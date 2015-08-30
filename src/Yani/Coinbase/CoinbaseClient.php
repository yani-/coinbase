<?php namespace Yani\Coinbase;

use Yani\Coinbase\Exceptions\CoinbaseOrderException;
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
	public function createOrder($amount, $currency, $name, $description = '', $metadata = array())
	{
		$payload = json_encode(get_defined_vars());
		$path    = '/v2/orders';
		$headers = $this->getHeaders(time(), 'POST', $path, $payload);

		$response = $this->client->post($this->endpoint . $path, array(
			'body'    => $payload,
			'headers' => $headers
		));
		if ((int) $response->getStatusCode() === 201)
		{
			return $response->getBody()->data;
		}
		else
		{
			throw new CoinbaseOrderException($response->getBody());
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

		return array(
			'CB-ACCESS-KEY'       => $this->apiKey,
			'CB-ACCESS-SIGN'      => $accessSign,
			'CB-ACCESS-TIMESTAMP' => $timestamp,
		);
	}
}
