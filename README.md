# Restarting php7.1-fpm on Cloudways servers from a [Deployer](https://deployer,org) file.

A quick and dirty deployer task to restart php7.1 fpm hosted on cloudways

Should be simple to modify to restart other services. See https://platform.cloudways.com/api

## Requirements

You need a [Cloudways](https://www.cloudways.com) account and server, an api key, and the id of the server you want to restart services on.

## Installation

It's just a couple of utility functions and a deployer task. Either download and include [restart-php7.1-fpm.php](restart-php7.1-fpm.php) or install via composer:

`composer require --dev samyapp/cloudways-restart-php-deployer-task:dev-master`

## Usage

In your `deploy.php` script, include [restart-php7.1-fpm.php](restart-php7.1-fpm.php) after you include your recipes.

Then set the following variables:

```php
// The email attached to your cloudways account.
set('cloudways_email', 'you@cloudways-account-email.com'); 

// the id of the cloudways server to restart
set('cloudways_server_id', 42); 

// your cloudways api key - you could hard code this in your deploy script of
// just set it in your environment instead.
set('cloudways_api_key', $_ENV['CLOUDWAYS_API_KEY']);
```
Finally, call the restart task from somewhere appropriate, eg:

```php
after('deploy:symlink', 'deploy:restart-php7.1-fpm');
```
