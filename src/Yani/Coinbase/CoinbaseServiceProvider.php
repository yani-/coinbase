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
		$config_path = $this->app['path.config'] . DIRECTORY_SEPARATOR . 'coinbase.php';
		$this->publishes([__DIR__ . '/../../config/config.php' => $config_path]);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->mergeConfigFrom(__DIR__ . '/../../config/config.php', 'coinbase');

		$this->app->singleton('coinbase', function ($app) {
			return new CoinbaseClient(
				new Guzzle,
				$app['config']->get('coinbase.apiKey'),
				$app['config']->get('coinbase.apiSecret'),
				$app['config']->get('coinbase.endpoint')
			);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['coinbase'];
	}
}
