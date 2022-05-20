<?php
/*
PicoWiki HTML
This plugin processes HTML
*/

#
# Template:
# <html>
#  <head>
#    <title>xxx</title>
#    text is assume url encoded, so use
#	%25 to insert a %
#	and %22 to insert a quote.
#    <meta name="key" content="value">
#  </head>
#  <body>
#    Content
#  </body>
# </html>
#

class PluginHTML {
  static $version = '1.0.0';

  static function parseFile($source) {
    if (false === ($i = stripos($source,'<body>'))) return [[],0];
    $offset = $i + strlen('<body>');

    if (false === ($i = stripos($source,'<head>'))) return [[],$offset];
    $start = $i+strlen('<head>');
    if (false === ($i = stripos($source,'</head>',$start))) return [[],$offset];
    $end = $i;

    $meta = [];
    foreach (explode("\n", substr($source,$start,$end)) as $line) {
      if (preg_match('/<title>(.*)<\/title>/',$line,$mv)) {
	$meta['title'] = htmlspecialchars_decode($mv[1]);
	continue;
      }
      if (preg_match('/<meta\s+name="([^"]*)"\s+content="([^"]*)"\s*>/',$line,$mv)) {
	$meta[urldecode($mv[1])] = urldecode($mv[2]);
      }
    }
    return [$meta,$offset];
  }
  static function readMeta($PicoWiki, $file_path) {
    $source = $PicoWiki->fileGetContents($file_path);
    return self::parseFile($source);
  }
  static function render($PicoWiki, $html) {
    //~ echo "<pre>$PicoWiki->html</pre>";
    if (false !== ($i = strripos($html,'</body>'))) {
      $html = substr($html,0,$i);
    }
    //~ return '<pre>'.htmlspecialchars($html).'</pre>';
    return $html;
  }
  static function payload_before($PicoWiki,$file_path,$payload) {
    list($meta,$offset) = self::parseFile($payload);
    if ($offset) {
      if (false !== ($i = strripos($payload,'</body>',$offset))) {
	return [$meta, substr($payload,$offset,$i - $offset)];
      }
    }
    return [$meta,$payload];
  }
  static function payload_after($PicoWiki,$meta,$body) {
    $hdr = '';
    if (count($meta)) {
      $tr = [ '"' => '%22', '%' => '%25' ];
      $hdr = '  <head>'.PHP_EOL;
      $hdr .= '    <!-- texts in meta tags are assumed to be url encoded -->'.PHP_EOL;
      $hdr .= '    <!--    Use "%22" to insert a quote (") -->'.PHP_EOL;
      $hdr .= '    <!--    Use "%25" to insert a "%" -->'.PHP_EOL;

      foreach ($meta as $k=>$v) {
	if ($k == 'title') {
	  $hdr .= '    <title>'.htmlspecialchars($v,ENT_NOQUOTES|ENT_HTML401|ENT_SUBSTITUTE).'</title>'.PHP_EOL;
	} else {
	  $hdr .= '    <meta name="'.strtr($k,$tr).'" content="'.strtr($v,$tr).'">'.PHP_EOL;
	}
      }
      $hdr .= '  </head>'.PHP_EOL;
    }
    return '<html>'.PHP_EOL.$hdr.'  <body>'.PHP_EOL.trim($body).PHP_EOL.'  </body>'.PHP_EOL.'</html>';
  }
  static function error404($PicoWiki,$file_path) {
    $dir = __DIR__ . '/'.pathinfo(__FILE__)['filename'];
    $pi = pathinfo($file_path);
    $new_template = self::payload_after($PicoWiki,[
	  'title' => ucwords($pi['filename']),
	  'example' => 'Example meta data',
	],
	'<h1>'.$pi['filename'].'</h1>'.PHP_EOL.
	'<p>Sample content</p>'.PHP_EOL);

    require $dir.'/templates/404.html';
  }

  static function view($PicoWiki) {
    $dir = __DIR__ . '/'.pathinfo(__FILE__)['filename'];
    require $dir.'/templates/layout.html';
  }
  static function load( $PicoWiki ){
    foreach (['html'] as $ext) {
      $PicoWiki->handler($ext, __CLASS__);
    }
  }
}
