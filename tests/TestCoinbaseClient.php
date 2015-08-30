<?php namespace Yani\Coinbase\Tests;

use GuzzleHttp\Client as Guzzle;
use Yani\Coinbase\CoinbaseClient;
use Yani\Coinbase\Exceptions\CoinbaseOrderException;

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
	protected $payload = array();

	/**
	 * Order Data
	 *
	 * @var array
	 */
	protected $orderData = array();

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
		$this->metadata    = array();

		$this->payload = json_encode(array(
			'amount'      => $this->amount,
			'currency'    => $this->currency,
			'name'        => $this->name,
			'description' => $this->description,
			'metadata'    => $this->metadata,
		));

		$this->orderData = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'order.json');
		$this->orderData = json_decode($this->orderData);
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
	public function testCreateOrder()
	{
		$timestamp = time();
		$accessSign = hash_hmac(
			'sha256',
			($timestamp . 'POST' . '/v2/orders' . $this->payload),
			$this->apiSecret
		);

		$headers = array(
			'CB-ACCESS-KEY'       => $this->apiKey,
			'CB-ACCESS-SIGN'      => $accessSign,
			'CB-ACCESS-TIMESTAMP' => $timestamp,
		);
		$successfulOrderResponseMock = \Mockery::mock('successfulOrderResponseMock')
			->shouldReceive('getStatusCode')->andReturn(201)
			->shouldReceive('getBody')->andReturn($this->orderData)->mock();

		$this->guzzleMock
			->shouldReceive('post')
			->with($this->endpoint . '/v2/orders', array(
				'body'    => $this->payload,
				'headers' => $headers,
			))->andReturn($successfulOrderResponseMock);

		$clientMock = \Mockery::mock(
			'Yani\Coinbase\CoinbaseClient[getHeaders]',
			array($this->guzzleMock, $this->apiKey, $this->apiSecret, $this->endpoint)
		)->shouldReceive('getHeaders')->andReturn($headers)->mock();

		$order = $clientMock->createOrder($this->amount, $this->currency, $this->name);

		$this->assertEquals($order, $this->orderData->data);
	}

	/**
	 * Test for failed orders
	 */
	public function testCreateOrderFailed()
	{
		$timestamp = time();
		$accessSign = hash_hmac(
			'sha256',
			($timestamp . 'POST' . '/v2/orders' . $this->payload),
			$this->apiSecret
		);

		$headers = array(
			'CB-ACCESS-KEY'       => $this->apiKey,
			'CB-ACCESS-SIGN'      => $accessSign,
			'CB-ACCESS-TIMESTAMP' => $timestamp,
		);

		$failedOrderResponseMock = \Mockery::mock('successfulOrderResponseMock')
			->shouldReceive('getStatusCode')->andReturn(500)
			->shouldReceive('getBody')->andReturn('some error from coinbase')->mock();

		$this->guzzleMock
			->shouldReceive('post')
			->with($this->endpoint . '/v2/orders', array(
				'body'    => $this->payload,
				'headers' => $headers
			))->andReturn($failedOrderResponseMock);

		$clientMock = \Mockery::mock(
			'Yani\Coinbase\CoinbaseClient[getHeaders]',
			array($this->guzzleMock, $this->apiKey, $this->apiSecret, $this->endpoint)
		)->shouldReceive('getHeaders')->andReturn($headers)->mock();

		$this->setExpectedException('Yani\Coinbase\Exceptions\CoinbaseOrderException');

		$order = $clientMock->createOrder($this->amount, $this->currency, $this->name);
	}

	/**
	 * Test getHeaders method
	 */
	public function testGetHeaders()
	{
		$timestamp   = time();
		$method      = 'POST';
		$requestPath = '/v2/orders';
		$body        = $this->payload;

		$headers = $this->client->getHeaders($timestamp, $method, $requestPath, $body);

		$accessSign = hash_hmac('sha256', ($timestamp . $method . $requestPath . $body), $this->apiSecret);

		$expectedHeaders = array(
			'CB-ACCESS-KEY'       => $this->apiKey,
			'CB-ACCESS-SIGN'      => $accessSign,
			'CB-ACCESS-TIMESTAMP' => $timestamp,
		);

		$this->assertEquals($headers, $expectedHeaders);
	}

}
