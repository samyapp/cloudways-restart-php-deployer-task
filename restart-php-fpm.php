<?php

namespace samyapp;

use function Deployer\get;
use function Deployer\desc;
use function Deployer\task;

/**
 * Gets an oauth access token from Cloudways
 * Requires a valid cloudways account email and api key
 */
function cloudways_oauth($email, $api_key)
{
	$url = 'https://api.cloudways.com/api/v1/oauth/access_token';
	$data = [
		'email' => $email,
		'api_key' => $api_key,
	];

	// use key 'http' even if you send the request to https://...
	$options = array(
		'http' => array(
			'header' => "Content-type: application/x-www-form-urlencoded\r\n",
			'method' => 'POST',
			'content' => http_build_query($data)
		)
	);
	$context = stream_context_create($options);
	$result = file_get_contents($url, false, $context);
	if ($result !== FALSE) {
		$json = json_decode($result);
		$token = $json->access_token;
		return $token;
	}
	return false;
}

/**
 * Makes an api request to cloudways api to restart php-fpm on a specific server
 * @param string $server - The ID of the server to restart
 * @param string $token - oauth access token granted by cloudways_oauth()
 */
function cloudways_restartphp($server, $token) {
	$url = 'https://api.cloudways.com/api/v1/service/state';
	$data = [
		'server_id' => $server,
        'service' => 'php' . get('cloudways_php_version') . '-fpm',
		'state' => 'restart',
	];

	// use key 'http' even if you send the request to https://...
	$options = array(
		'http' => array(
			'header' => "Authorization: Bearer $token\r\nContent-type: application/x-www-form-urlencoded\r\n",
			'method' => 'POST',
			'content' => http_build_query($data)
		)
	);
	$context = stream_context_create($options);
	$result = file_get_contents($url, false, $context);
	if ($result !== FALSE) {
		$json = json_decode($result);
		return $json->service_status && $json->service_status->status == 'running';
	}
	return false;
}

desc('Restart php ' . get('cloudways_php_version') . ' fpm on cloudways server');
task('deploy:restart-php-fpm', function() {
	$token = cloudways_oauth(get('cloudways_email'), get('cloudways_api_key'));
	if($token) {
		if(!cloudways_restartphp(get('cloudways_server_id'), $token)) {
			throw new \Exception('unable to restart php');
		}
	}
	else {
		throw new \Exception('unable to restart php - oauth failure');
	}
});

/* Example Usage

// require this file in your deploy.php after requiring your recipe,
// set the needed variables and schedule the task.

set('cloudways_email', 'you@cloudways-account-email.com'); 
set('cloudways_server_id', 42); // the id of the cloudways server to restart
// your cloudways api key - you could hard code this in your deploy script of
// just set it in your environment instead.
set('cloudways_api_key', $_ENV['CLOUDWAYS_API_KEY']);
after('deploy:symlink', 'deploy:restart-php-fpm');

*/
