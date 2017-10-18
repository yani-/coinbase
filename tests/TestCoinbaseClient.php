<?php namespace Yani\Coinbase\Tests;

use GuzzleHttp\Client as Guzzle;
use Yani\Coinbase\CoinbaseClient;
use Yani\Coinbase\Exceptions\CoinbaseCheckoutException;

class TestCoinbaseClient extends \PHPUnit_Framework_TestCase {
	/**
	 * Coinbase client
	 *
	 * @var mixed
	 */
	protected $client = null;

	/**
	 * Coinbase endpoint
	 *
	 * @var string
	 */
	protected $endpoint = 'https://api.sandbox.coinbase.com';

	/**
	 * Coinbase apiKey
	 *
	 * @var string
	 */
	protected $apiKey = 'COINBASE-API-KEY';

	/**
	 * Coinbase apiSecret
	 *
	 * @var string
	 */
	protected $apiSecret = 'COINBASE-API-SECRET';

	/**
	 * GuzzleMock object
	 *
	 * @var mixed
	 */
	protected $guzzleMock = null;

	/**
	 * Payload for creating a new order
	 *
	 * @var array
	 */
	protected $payload = [];

	/**
	 * Order Data
	 *
	 * @var array
	 */
	protected $orderData = [];

	/**
	 * Order amount
	 *
	 * @var float
	 */
	protected $amount = 0;

	/**
	 * Order currency
	 *
	 * @var string
	 */
	protected $currency = '';

	/**
	 * Order name
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Order description
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * Order metadata
	 *
	 * @var array
	 */
	protected $metadata = '';

	/**
	 * Set up the test group by creating a guzzle mock and CoinbaseClient
	 */
	public function setUp()
	{
		$this->guzzleMock = \Mockery::mock('GuzzleHttp\Client');
		$this->client     = new CoinbaseClient($this->guzzleMock, $this->apiKey, $this->apiSecret, $this->endpoint);

		$this->amount      = 10;
		$this->currency    = 'USD';
		$this->name        = 'ORDER #1';
		$this->description = '';
		$this->metadata    = [];

		$this->payload = [
			'amount'      => $this->amount,
			'currency'    => $this->currency,
			'name'        => $this->name,
		];

		$this->orderData = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'order.json');
	}

	/**
	 * Test that the class can be initialized
	 */
	public function testCanBeInitialized()
	{
		$this->assertInstanceOf('Yani\Coinbase\CoinbaseClient', $this->client);
	}

	/**
	 * Test for successful orders
	 */
	public function testCreateCheckout()
	{
		$purchaseUUID = 'f399cd87-3265-46df-affa-0bd0cd431f29';
		$successUrl = 'https://servmask.com/purchase/' . $purchaseUUID;
		$data = [
			'collect_email' => true,
			'style'         => 'buy_now_large',
			'success_url'   => $successUrl,
			'metadata'      => [
				'purchase_uuid' => $purchaseUUID,
			],
		];
		$this->payload += $data;
		$timestamp = time();
		$accessSign = hash_hmac(
			'sha256',
			($timestamp . 'POST' . '/v2/checkouts' . json_encode($this->payload)),
			$this->apiSecret
		);

		$headers = [
			'CB-ACCESS-KEY'       => $this->apiKey,
			'CB-ACCESS-SIGN'      => $accessSign,
			'CB-ACCESS-TIMESTAMP' => $timestamp,
			'CB-VERSION'          => '2015-04-08',
		];
		$successfulOrderResponseMock = \Mockery::mock('successfulOrderResponseMock')
			->shouldReceive('getStatusCode')->andReturn(201)
			->shouldReceive('getBody')->andReturn($this->orderData)->mock();

		$this->guzzleMock
			->shouldReceive('post')
			->with($this->endpoint . '/v2/checkouts', [
				'body'    => json_encode($this->payload),
				'headers' => $headers,
			])->andReturn($successfulOrderResponseMock);

		$clientMock = \Mockery::mock(
			'Yani\Coinbase\CoinbaseClient[getHeaders]',
			[$this->guzzleMock, $this->apiKey, $this->apiSecret, $this->endpoint]
		)->shouldReceive('getHeaders')->andReturn($headers)->mock();

		$order = $clientMock->createCheckout($this->amount, $this->currency, $this->name, $data);

		$this->assertEquals($order, json_decode($this->orderData)->data);
	}

	/**
	 * Test for failed orders
	 */
	public function testCreateOrderFailed()
	{
		$purchaseUUID = 'f399cd87-3265-46df-affa-0bd0cd431f29';
		$successUrl = 'https://servmask.com/purchase/' . $purchaseUUID;
		$data = [
			'collect_email' => true,
			'style'         => 'buy_now_large',
			'success_url'   => $successUrl,
			'metadata'      => [
				'purchase_uuid' => $purchaseUUID,
			],
		];
		$this->payload += $data;

		$timestamp = time();
		$accessSign = hash_hmac(
			'sha256',
			($timestamp . 'POST' . '/v2/checkouts' . json_encode($this->payload)),
			$this->apiSecret
		);

		$headers = [
			'CB-ACCESS-KEY'       => $this->apiKey,
			'CB-ACCESS-SIGN'      => $accessSign,
			'CB-ACCESS-TIMESTAMP' => $timestamp,
			'CB-VERSION'          => '2015-04-08',
		];

		$failedOrderResponseMock = \Mockery::mock('successfulOrderResponseMock')
			->shouldReceive('getStatusCode')->andReturn(500)
			->shouldReceive('getBody')->andReturn('some error from coinbase')->mock();

		$this->guzzleMock
			->shouldReceive('post')
			->with($this->endpoint . '/v2/checkouts', [
				'body'    => json_encode($this->payload),
				'headers' => $headers
			])->andReturn($failedOrderResponseMock);

		$clientMock = \Mockery::mock(
			'Yani\Coinbase\CoinbaseClient[getHeaders]',
			[$this->guzzleMock, $this->apiKey, $this->apiSecret, $this->endpoint]
		)->shouldReceive('getHeaders')->andReturn($headers)->mock();

		$this->setExpectedException('Yani\Coinbase\Exceptions\CoinbaseCheckoutException');

		$order = $clientMock->createCheckout($this->amount, $this->currency, $this->name, $data);
	}

	/**
	 * Test getHeaders method
	 */
	public function testGetHeaders()
	{
		$timestamp   = time();
		$method      = 'POST';
		$requestPath = '/v2/orders';
		$body        = json_encode($this->payload);

		$headers = $this->client->getHeaders($timestamp, $method, $requestPath, $body);

		$accessSign = hash_hmac('sha256', ($timestamp . $method . $requestPath . $body), $this->apiSecret);

		$expectedHeaders = [
			'CB-ACCESS-KEY'       => $this->apiKey,
			'CB-ACCESS-SIGN'      => $accessSign,
			'CB-ACCESS-TIMESTAMP' => $timestamp,
			'CB-VERSION'          => '2015-04-08',
		];

		$this->assertEquals($headers, $expectedHeaders);
	}

}
