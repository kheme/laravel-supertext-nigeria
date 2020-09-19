<h2 align="center">
    Laravel Package for SuperText Nigeria SMS Gateway
</h2>

<p align="center">
    <a href="https://packagist.org/packages/kheme/laravel-supertext-nigeria"><img src="https://poser.pugx.org/kheme/laravel-supertext-nigeria/v/stable?format=flat-square" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/kheme/laravel-supertext-nigeria"><img src="https://poser.pugx.org/kheme/laravel-supertext-nigeria/v/unstable?format=flat-square" alt="Latest Unstable Version"></a>    
    <a href="https://packagist.org/packages/kheme/laravel-supertext-nigeria"><img src="https://poser.pugx.org/kheme/laravel-supertext-nigeria/license?format=flat-square" alt="License"></a>
    <a href="https://packagist.org/packages/kheme/laravel-supertext-nigeria"><img src="https://poser.pugx.org/kheme/laravel-supertext-nigeria/downloads" alt="Total Downloads"></a>
</p>

## Introduction

This is a simple Laravel wrapper for SuperText Nigeria's SMS API gateway.

## Installation

Using Composer:

```bash
composer require kheme/laravel-supertext-nigeria
```

#### Installing on Laravel 5.5 or above

If you are using Laravel 5.5 or above, the package will automatically register the `supertext` provider and facade.

#### Installing on Laravel 5.4 and below

For Laravel 5.4 or below, add `Kheme\SuperTextNg\SuperTextNgServiceProvider` to the list of `providers` in your `config/app.php`:

```php
'providers' => [
    // Other service providers...

    Kheme\SuperTextNg\SuperTextNgServiceProvider::class,
],
```

If you want to use the facade interface, you can `use` the facade class when needed:

```php
use Kheme\SuperTextNg\Facade\SMS;
```

Or add to the list of aliases in your `config/app.php` as follows:

```php
'aliases' => [
    ...
    'SuperTextNg' => Kheme\SuperTextNg\Facade\SMS::class,
],
```

### Installing on Lumen

Laravel SuperTextNg works with Lumen too! You will need to do a little work by hand to get it up and running.
First, install the package using composer:

```bash
composer require kheme/laravel-supertext-nigeria
```

Next, we have to tell Lumen that our library exists. Update `bootstrap/app.php` and register `SuperTextNgServiceProvider` as follows:

```php
$app->register(Kheme\SuperTextNg\SuperTextNgServiceProvider::class);
```

At this point, set `SUPERTEXTNG_USERNAME`, `SUPERTEXTNG_PASSWORD`, `SUPERTEXTNG_SENDER` and `SUPERTEXTNG_IGNORE_DND` in your `.env` file
and it should work for you.

### Configuration

Run `artisan vendor:publish` to copy the distribution configuration file to your app's config directory.

```bash
php artisan vendor:publish --provider="Kheme\SuperTextNG\SuperTextNgServiceProvider"
```

#### Configuration on Lumen

Unfortunately, Lumen doesn't support publishing files, so you will have to create the config file yourself
by creating a config directory (if it doesn't exist) and copying the config file out of the package into your project.
From your project's root folder, run the following command in the terminal:

```bash
mkdir config
cp vendor/kheme/laravel-supertext-nigeria/config/supertextng.php config/supertextng.php
```

Update `config/supertextng.php` with your SuperText Nigeria credentials and settings.
Alternatively, you can update your `.env` file with the respective values to the following:

```dotenv
SUPERTEXTNG_USERNAME=(your supertextng.com username)
SUPERTEXTNG_PASSWORD=(your supertextng.com password)
SUPERTEXTNG_SENDER=(you SMS sender ID)
SUPERTEXTNG_IGNORE_DND=('yes' or 'no' indicating whether to send to do-not-disturb numbers)
```

Finally, add `$app->configure('supertextng');` to your `bootstrap/app.php` somewhere before registering the service provider above.

### Usage

Don't forget to import the fascade before use:

```php
use Kheme\SuperTextNg\Facades\SMS;
```

Sending to a single recipient
-----------------------------

```php
SMS::from('Kheme')
    ->to('2348153332428')
    ->message('Using the facade to send a message.')
    ->send();
```

On success, this should return `true`, otherwise, an exception will be thrown.

Sending to multiple recipients
------------------------------

You can send an SMS to multiple recipients by including multiple `to()` in your call:

```php
SMS::from('Kheme')
    ->to('2348153332428')
    ->to('2348056511193')
    ->message('Using the facade to send a message.')
    ->send();
]);
```

Or, by supplying an array of phone numbers to a single `to()`:

```php
SMS::from('Kheme')
    ->to(
        [
            '2348153332428',
            '2348056512393',
        ]
    ),
    ->message('Using the facade to send a message.')
    ->send();
```

Send to DND enabled numbers
------------------------

To send SMS to numbers that have Do Not Disturb (DND) enabled, include `ignoreDND()` to your call:

```php
SMS::from('Kheme')
    ->to('2348153332428')
    ->message('Using the facade to send a message.')
    ->ignoreDND()
    ->send();
]);
```

Return unit balance after sending
------------------------

If you would like to return your account balance after sending, include `returnBalance()` to your call:

```php
SMS::from('Kheme')
    ->to('2348153332428')
    ->message('Using the facade to send a message.')
    ->returnBalance()
    ->send();
]);
```

Return amount of units used for sending
------------------------

If you would like to return the total amount of units used after sending, include `returnUnitsUsed()` to your call:

```php
SMS::from('Kheme')
    ->to('2348153332428')
    ->message('Using the facade to send a message.')
    ->returnUnitsUsed()
    ->send();
]);
```

Combining options
------------------------

The above method options, exluding the `balance()` below, can be combined like in the following example:

```php
SMS::from('Kheme')
    ->to('2348153332428')
    ->message('Using the facade to send a message.')
    ->returnBalance()
    ->returnUnitsUsed()
    ->ignoreDND()
    ->send();
]);
```

Checking account balance
------------------------

To check your SuperText Nigeria credit balance, simply call `balance()`:

```php
SMS::balance();
```

### Errors

In the case of an error, a call will return an error as follows:

*The numbers on the left are the corresponding error code from SuperText Nigeria, but will not be included in the error response*

- **100**: *One or more required url parameter is missing or misspelt*
- **101**: *Username is blank*
- **102**: *Password is blank*
- **103**: *Destination is blank*
- **104**: *Message is blank*
- **105**: *Sender is blank*
- **200**: *Wrong username or password*
- **201**: *Account has not been activated*
- **202**: *Inactive account*
- **300**: *Insufficient credit*
- **400**: *Failed delivery (no credit deducted)*
