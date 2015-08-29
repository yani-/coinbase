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
```
