# Coinbase

A simple Laravel package for processing payments via [Coinbase](https://coinbase.com)

## Requirements

* PHP 5.4 or greater
* Laravel 4.1 or greater

## Installation

You can install the package using the [Composer](https://getcomposer.org/) package manager. Assuming you have Composer installed globally:

```sh
composer require yani-/coinbase:1.0.*
```

### Service provider and alias

Next, add the `Yani\Coinbase\CoinbaseServiceProvider` service provider to the `providers` array in your `app/config.php` file.

```php
'providers' => array(
  ...
  'Yani\Coinbase\CoinbaseServiceProvider',
),
```

and then add the facade to your `aliases` array in your `app/config.php` file.

```php
'aliases' => array(
  ...
  'Coinbase' => 'Yani\Coinbase\Facades\Coinbase',
),
```

### Configuration

Publish the configuration with

```php
php artisan config:publish yani/coinbase
```

This will add the boilerplate configuration to `app/config/packages/yani/coinbase/config.php`.

## Usage

```php
$amount   = 100;
$currency = 'USD';
$name     = 'Order #1';
try
{
	$order = Coinbase::createOrder($amount, $currency, $name);
	echo $order->id;                       // 0fdfb26e-bd26-5e1c-b055-7b935e57fa33
	echo $order->status;                   // active
	echo $order->bitcoin_address;          // mymZkiXhQNd6VWWG7VGSVdDX9bKmviti3U
	echo $order->bitcoin_amount->amount;   // 1.00000000
	echo $order->bitcoin_amount->currency; // BTC
	echo $order->expires_at;               // expires_at
	// You can find a full list of the response here: https://developers.coinbase.com/api/v2#create-an-order
}
catch (CoinbaseOrderException $e)
{
	echo "The order failed because: " . $e->getMessage();
}
```
