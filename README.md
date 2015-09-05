# Coinbase
[![Build Status](https://travis-ci.org/yani-/coinbase.png?branch=develop)](https://travis-ci.org/yani-/coinbase)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yani-/coinbase/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yani-/coinbase/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/yani/coinbase/v/stable.png)](https://packagist.org/packages/yani/coinbase)
[![Total Downloads](https://poser.pugx.org/yani/coinbase/downloads.png)](https://packagist.org/packages/yani/coinbase)


A simple Laravel package for processing payments via [Coinbase](https://coinbase.com)

## Requirements

* PHP 5.4 or greater
* Laravel 4.1 or greater

## Installation

You can install the package using the [Composer](https://getcomposer.org/) package manager. Assuming you have Composer installed globally:

```sh
composer require yani/coinbase:0.*
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
	$checkout = Coinbase::createCheckout($amount, $currency, $name);
	echo $checkout->id;         // ffc93ba1-874d-5c55-853c-53c9c4814b1e
	echo $checkout->embed_code; // af0b52802ad7b36806e307b2d294e3b4
	// You can find a full list of the response here: https://developers.coinbase.com/api/v2#create-checkout
}
catch (CoinbaseCheckoutException $e)
{
	echo "The order failed because: " . $e->getMessage();
}
```

### License
MIT
