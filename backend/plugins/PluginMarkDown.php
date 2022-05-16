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
            //~ require_once __DIR__ . '/PluginMarkDown/Parsedown.php';
	    //~ $Parsedown = new Parsedown();

            //~ require_once __DIR__ . '/PluginMarkDown/Parsedown-1.7.4.php';
	    //~ require_once __DIR__ . '/PluginMarkDown/ParsedownExtra-0.8.1.php';
	    //~ $Parsedown = new ParsedownExtra();

            require_once __DIR__ . '/PluginMarkDown/Parsedown-1.7.4.php';
	    require_once __DIR__ . '/PluginMarkDown/ParsedownExtra-0.8.1.php';
	    require_once __DIR__ . '/PluginMarkDown/TOC-1.1.2.php';
	    require_once __DIR__ . '/PluginMarkDown/Extension.php';
	    $Parsedown = new ParsedownExtension();

	    return $Parsedown->text($PicoWiki->html);
	  });
	}
}

# ParsedownExtra
# GOOD
# - TOC
# - tasks
# - emojis
# BAD
# - sup/sub
# - tablespan
#
