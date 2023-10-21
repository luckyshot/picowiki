<?php
/**
 * Autolink Plugin
 * This plugin auto-detects file keywords and generates links
 * Authors: Xavi Esteve (@luckyshot), Igor Gaffling (@gaffling)
 */
class PluginAutoLink {

	static $version = '1.1.0';

	static function run( $wiki ) {
		$wiki->event('view_after', NULL, function($wiki) {
			$use_target_blank = FALSE; // CHANGE IF YOU LIKE
			$use_nofollow_tag = TRUE; // CHANGE IF YOU LIKE

			$filenames = [];
			foreach ($wiki->file_list as $file) {
				if ( !$file ) continue;
				$filenames[ $file ] = self::cleanName( $file );
			}
			foreach ($filenames as $path => $name) {
				$wiki->html = preg_replace(
					'#([ \n])('.$name.')([ .,])#i',
					'$1<a href="'.$wiki->config['app_url'].''.$path.'">$2</a>$3',
					$wiki->html
				);
			}

			// Detect URLs and autolink them
			$attribute = '';
			if ( $use_target_blank == TRUE ) $attribute .= ' target="_blank"';
			if ( $use_nofollow_tag == TRUE ) $attribute .= ' rel="nofollow"';

			$linkregex = '/^((?:tel|https?|ftps?|mailto):.*?)$/im';

			if ( preg_match_all($linkregex, $wiki->html, $match) ) {
				foreach ($match[0] as $url) {
				$linktext = str_replace('mailto:', '', $url);
				$wiki->html=str_replace($url, '<a href="'.$url.'"'.$attribute.'>'.$linktext.'</a>', $wiki->html);
				}
			}

			return $wiki;
		});
	}

	static function cleanName($string){
		return str_replace('-', ' ', pathinfo($string)['filename']);
	}
}
