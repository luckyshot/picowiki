<?php
/*
PicoWiki include
This plugin is used to include files
*/
class PluginIncludes {
  static $version = '0.0.0';

  static function mdx_include($html,$PicoWiki) {
    $files_dir = $PicoWiki->config['file_path'];

    $offset = 0;
    $newhtml = '';

    while (preg_match('/\n\s*\$include:\s*([^\$]+)\$\s*\n/', '\n'.$html.'\n',$mv,PREG_OFFSET_CAPTURE,$offset)) {
      $newhtml .= substr($html,$offset,$mv[0][1]-1-$offset);

      $incfile = trim($mv[1][0]);
      //~ echo '<pre>';
      //~ print_r($mv);
      //~ echo "FILE: (".$incfile.")\n";

      if (file_exists($files_dir.'/'.$incfile)) {
	$newhtml .= PHP_EOL;

	$inchtml = $PicoWiki->fileGetContents($files_dir.'/'.$incfile);
	$PicoWiki->fileGetMeta($files_dir.'/'.$incfile);
	$inchtml = substr($inchtml, $PicoWiki->fileGetOffset($files_dir.'/'.$incfile));

	$inchtml = self::mdx_include($inchtml, $PicoWiki);
	$newhtml .= $inchtml;

	$newhtml .= PHP_EOL;
      } else {
	$newhtml .= PHP_EOL;
	$newhtml .= '> ERROR: '.$incfile.' does not exists!'.PHP_EOL;
	$newhtml .= PHP_EOL;
      }

      $offset = $mv[0][1]+strlen($mv[0][0])-3;
      //~ echo 'NEXT: ,'.substr($html,$offset,25).','.PHP_EOL;
      //~ echo '</pre>';

    }

    $newhtml .= substr($html,$offset);
    return $newhtml;
  }


  static function load($PicoWiki) {
    $PicoWiki->event('view_before', NULL, function($html) use ($PicoWiki) {
      return self::mdx_include($html, $PicoWiki);
    });
  }
}
