<?php
/*
PicoWiki MarkDown
This plugin converts Markdown format to HTML

*/
Class PluginMarkDown
{
	static $version = '1.0.0';

	static function run( $PicoWiki ){
		$PicoWiki->event('view_after', NULL, function($PicoWiki) {
            require_once __DIR__ . '/PluginMarkDown/Parsedown.php';
            $Parsedown = new Parsedown();
	        return $Parsedown->text($PicoWiki->html);
		});
	}
}
