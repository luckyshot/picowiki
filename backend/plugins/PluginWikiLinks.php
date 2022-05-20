<?php
/*
This plugin is used to create link
*/
class PluginWikiLinks {
  static $version = '0.0.0';

  static function load($PicoWiki) {
    $PicoWiki->event('view_after', NULL, function($html) use ($PicoWiki) {
      $vars = [];
      foreach ([
	  '/\[\[[^\]\n]+\]\]/' => '<a href="%1$s"%3$s>%2$s</a>',
	  '/\{\{[^\}\n]+\}\}/' => '<img src="%1$s" alt="%2$s" title="%2$s"%3$s>',
	] as $re=>$fmt) {
	if (preg_match_all($re,$html,$mv)) {
	  foreach ($mv[0] as $k) {
	    $v = substr($k,2,-2);
	    if (false !== ($i = strpos($v,'|'))) {
	      $t = substr($v,$i+1);
	      $v = substr($v,0,$i);
	    } else {
	      $t = null;
	    }

	    if (empty($v)) continue;
	    $v = preg_split('/\s+/',$v,2);
	    if (count($v) == 0) continue;
	    $x = isset($v[1]) ? ' '.$v[1] : '';
	    $v = $v[0];
	    if (empty($v)) continue;
	    if (empty($t)) $t = htmlspecialchars(pathinfo($v)['filename']);

	    //~ echo '<pre>';
	    //~ var_dump($k);
	    //~ var_dump($v);
	    //~ var_dump($x);
	    //~ var_dump($t);
	    //~ echo '</pre>';

	    $vars[$k] = sprintf($fmt, $PicoWiki->mkUrl($v), $t,$x);
	  }
	}
      }
      if (count($vars) == 0) return $html;
      return strtr($html,$vars);
    });
  }
}
