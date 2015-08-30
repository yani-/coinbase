<?php namespace Yani\Coinbase;

use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client as Guzzle;

class CoinbaseServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('yani/coinbase');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['coinbase'] = $this->app->share(function ($app) {
			return new CoinbaseClient(new Guzzle, $app['config']->get('coinbase::endpoint'));
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('coinbase');
	}
}
