<?php
/*
PicoWiki Password
Password protected access. Enable it by turning $config['enabled'] to true

*/
Class PluginPassword
{
	static $config = [
		'enabled' => true,
		'access_token' => 'vdlQVsW5XZr5kLVlARAJDZUnTQFDbfBAcoEW77n9ajIAiRIOl1NqHPhJiZBlRc0j',
		'duration' => 3600*24*30,
		'cookie_name' => 'picowiki_access_token',
		'cookie_path' => '/',
	];
	static $version = '1.1.1';

	static function run( $PicoWiki ){
		if ( !self::$config['enabled'] ){
			return true;
		}

		$PicoWiki->event('plugins_loaded', self::$config, function($PicoWiki) {
			$authenticated = false;

        	if ( isset($_POST[ self::$config['cookie_name'] ]) AND $_POST[ self::$config['cookie_name'] ] === self::$config['access_token'] ){
        		setcookie(self::$config['cookie_name'], $_POST[ self::$config['cookie_name'] ], time()+self::$config['duration'], self::$config['cookie_path']);
        		$_COOKIE[ self::$config['cookie_name'] ] = $_POST[ self::$config['cookie_name'] ];
        		$authenticated = true;
        		header("Location: ".$PicoWiki->config['app_url']);
        	}

			if ( isset($_COOKIE[ self::$config['cookie_name'] ]) AND $_COOKIE[ self::$config['cookie_name'] ] === self::$config['access_token'] ){
				$authenticated = true;
			}

        	if ( !$authenticated ){
        		echo '<!DOCTYPE html>
        		<html lang="en">
        		<head>
        			<meta charset="UTF-8">
        			<title>'.$PicoWiki->config['app_name'].'</title>
        			<link rel="stylesheet" href="'.$PicoWiki->config['app_url'].'static/style.css">
        			<style>
						input {
							font-size: 1em;
							padding: 1em;
							width: 100%;
							max-width: 100%;
							font-family: monospace;
						}
						button {
							font-size: 1em;
							padding: 1em;
							display: block;
							width: 100%;
							background: inherit;
						}
        			</style>
        		</head>
        		<body>
					<h1>'.$PicoWiki->config['app_name'].'</h1>
					<p>Please login:</p>
					<form action="?" method="post">
						<p><input type="password" name="'.self::$config['cookie_name'].'"></p>
						<p><button type="submit">Login</button></p>
					</form>
        		</body>
        		</html>';
        		die();
        	}
		});

		$PicoWiki->event('list_loaded', self::$config, function($PicoWiki) {
			$PicoWiki->file_list[] = 'logout';
		});

		$PicoWiki->event('url_loaded', self::$config, function($PicoWiki) {
			if ( $PicoWiki->url === 'logout' ){
        		setcookie(self::$config['cookie_name'], null, time() - 3600, self::$config['cookie_path']);
        		unset( $_COOKIE[ self::$config['cookie_name'] ] );
        		header("Location: ".$PicoWiki->config['app_url']);
			}
		});
	}
}
