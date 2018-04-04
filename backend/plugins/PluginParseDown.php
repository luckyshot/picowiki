<?php
/*
PicoWiki MarkDown
This plugin converts Markdown format to HTML

*/
Class PluginParseDown
{
	static function run( $PicoWiki ){
		$PicoWiki->event('view_after', NULL, function($PicoWiki) {
            require_once __DIR__ . '/PluginParseDown/Parsedown.php';
            $Parsedown = new Parsedown();
	        return $Parsedown->text($PicoWiki->html);
		});
	}
}