<?php
/*
PicoWiki Autolink
This plugin auto-detects file keywords and generates links

TODO:
- detect URLs and autolink them
- detect YouTube links and replace with embed code

*/
Class PluginAutoLink {
	static $version = '1.0.0';

	static function run( $PicoWiki ){
		$PicoWiki->event('view_after', NULL, function($PicoWiki) {
			$filenames = [];

			foreach ($PicoWiki->file_list as $file) {
				if ( !$file ){ continue; }
				$filenames[ $file ] = self::cleanName( $file );
			}

			foreach ($filenames as $path => $name) {
				$PicoWiki->html = preg_replace('#([ \n])('.$name.')([ .,])#i', '$1<a href="'.$PicoWiki->config['app_url'].''.$path.'">$2</a>$3', $PicoWiki->html);
				// Markdown version, although it is unreliable when in subdirectories
				// $PicoWiki->html = preg_replace('#([ \n_*])('.$name.')([ .,_*])#i', '$1['.$name.']('.$path.')$3', $PicoWiki->html);
			}
	        return $PicoWiki;
		});
	}

	static function cleanName($string){
		return str_replace('-', ' ', pathinfo($string)['filename']);
	}
}
