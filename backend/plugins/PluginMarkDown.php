<?php
/*
PicoWiki MarkDown
This plugin converts Markdown format to HTML
*/
class PluginMarkDown {
  static $version = '1.0.0';

  static function parseFile($source) {
    if (!preg_match('/^\s*---\s*\n/',$source,$mv)) return [[],0];

    $start = $offset = strlen($mv[0]);

    if (!preg_match('/\n\s*---\s*\n/',$source,$mv,PREG_OFFSET_CAPTURE,$offset)) return [[],0];
    $end = $mv[0][1];
    $offset = $mv[0][1] + strlen($mv[0][0]);

    //~ print_r(['start'=>$start,'end'=>$end,'offset'=>$offset]);
    //~ var_dump(substr($source,$start,$end-$start));
    //~ var_dump(substr($source,$offset));

    $meta = yaml_parse(substr($source,$start,$end-$start));
    if ($meta === false) return [[],0];

    return [$meta,$offset];
  }

  static function readMeta($PicoWiki, $file_path) {
    $source = $PicoWiki->fileGetContents($file_path);
    return self::parseFile($source);
  }
  static function render($PicoWiki, $html) {
    //~ echo "<pre>$PicoWiki->html</pre>";
    require_once __DIR__ . '/PluginMarkDown/lib/Parsedown-1.7.4.php';
    require_once __DIR__ . '/PluginMarkDown/lib/ParsedownExtra-0.8.1.php';
    require_once __DIR__ . '/PluginMarkDown/lib/TOC-1.1.2.php';
    require_once __DIR__ . '/PluginMarkDown/lib/Extension.php';
    $Parsedown = new ParsedownExtension();
    $Parsedown->headown = 2;
    return $Parsedown->text($html);
  }
  static function payload_before($PicoWiki,$file_path,$payload) {
    list($meta,$offset) = self::parseFile($payload);
    return [$meta,substr($payload,$offset)];
  }
  static function payload_after($PicoWiki,$meta,$body) {
    if (count($meta)) {
      $yaml = substr(yaml_emit($meta),0,-4).'---'.PHP_EOL;
    } else {
      $yaml = '';
    }
    return $yaml.$body;
  }
  static function error404($PicoWiki,$file_path) {
    $dir = __DIR__ . '/'.pathinfo(__FILE__)['filename'];
    $pi = pathinfo($file_path);
    $new_template =
	'---' .PHP_EOL.
	'title: '.ucwords($pi['filename']).PHP_EOL.
	'---'.PHP_EOL.
	PHP_EOL.
	'# '.$pi['filename'].PHP_EOL;
	PHP_EOL;

    require $dir.'/templates/404.html';
  }

  static function view($PicoWiki) {
    $dir = __DIR__ . '/'.pathinfo(__FILE__)['filename'];
    require $dir.'/templates/layout.html';
  }
  static function load( $PicoWiki ){
    foreach (['md', 'markdown'] as $ext) {
      $PicoWiki->handler($ext, __CLASS__);
    }
  }
}
