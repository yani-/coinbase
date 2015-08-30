<?php namespace Yani\Coinbase;

use Yani\Coinbase\Exceptions\CoinbaseOrderException;
use GuzzleHttp\Client as Guzzle;

class CoinbaseClient {

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
	 * @param string             $endpoint
	 */
	public function __construct(Guzzle $client, $endpoint)
	{
		$this->client   = $client;
		$this->endpoint = $endpoint;
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
		$payload = $this->preparePayload(get_defined_vars());
		$response = $this->client->post($this->endpoint, $payload);
		if ((int)$response->getStatusCode() === 201)
		{
			return $response->getBody()->data;
		}
		else
		{
			throw new CoinbaseOrderException($response->getBody());
		}

	}

	/**
	 * Prepare payload data by json encoding it
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	private function preparePayload($data)
	{
		return array(
			'body' => json_encode($data)
		);
	}
}
