<?php namespace Yani\Coinbase;

use Yani\Coinbase\Exceptions\CoinbaseCheckoutException;
use GuzzleHttp\Client as Guzzle;

class CoinbaseClient {

	/**
	 * The coinbase API KEY
	 *
	 * @var string
	 */
	protected $apiKey;

	/**
	 * The coinbase API SECRET
	 *
	 * @var string
	 */
	protected $apiSecret;

	/**
	 * The Guzzle HTTP client
	 *
	 * @var \GuzzleHttp\Client
	 */
	protected $client = null;

	/**
	 * The Coinbase endpoint
	 *
	 * @var string
	 */
	protected $endpoint = '';

	/**
	 * Instantiate a new client
	 *
	 * @param \GuzzleHttp\Client $client
	 * @param string             $apiKey
	 * @param string             $apiSecret
	 * @param string             $endpoint
	 */
	public function __construct(Guzzle $client, $apiKey, $apiSecret, $endpoint)
	{
		$this->apiKey    = $apiKey;
		$this->apiSecret = $apiSecret;
		$this->client    = $client;
		$this->endpoint  = $endpoint;
	}

	/**
	 * Create order with Coinbase API
	 *
	 * @param float  $amount
	 * @param string $currency
	 * @param string $name
	 * @param string $description
	 * @param array  $metadata
	 *
	 * @return stdClass
	 */
	public function createCheckout($amount, $currency, $name, $data = [])
	{
		$payload = [
			'amount' => $amount,
			'currency' => $currency,
			'name'     => $name,
		];
		foreach ($data as $key => $value)
		{
			$payload[$key] = $value;
		}
		$payload = json_encode($payload);
		$path    = '/v2/checkouts';
		$headers = $this->getHeaders(time(), 'POST', $path, $payload);

		try
		{
			$response = $this->client->post($this->endpoint . $path, [
				'body'    => $payload,
				'headers' => $headers
			]);
		}
		catch (\Exception $e)
		{
			echo $e->getResponse();
			exit(0);
		}
		if ((int) $response->getStatusCode() === 201)
		{
			return json_decode($response->getBody())->data;
		}
		else
		{
			throw new CoinbaseCheckoutException($response->getBody());
		}

	}

	/**
	 * Return headers with coinbase signature
	 *
	 * @param int    $timestamp
	 * @param string $method
	 * @param string $requestPath
	 * @param array  $body
	 *
	 * @return array
	 */
	public function getHeaders($timestamp, $method, $requestPath, $body)
	{
		$accessSign = hash_hmac('sha256', ($timestamp . $method . $requestPath . $body), $this->apiSecret);

		return [
			'CB-ACCESS-KEY'       => $this->apiKey,
			'CB-ACCESS-SIGN'      => $accessSign,
			'CB-ACCESS-TIMESTAMP' => $timestamp,
			'CB-VERSION'          => '2015-04-08',
		];
	}
}
