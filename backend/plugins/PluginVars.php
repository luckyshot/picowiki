<?php
/*
PicoWiki vars
This plugin is used to render config and meta data on a page
*/
class PluginVars {
  static $version = '0.0.0';

  static function load($PicoWiki) {
    $PicoWiki->event('view_after', NULL, function($html) use ($PicoWiki) {
      $vars = [];
      # We do it like this because these expansions may be expensive...
      if (strpos($html,'$plugins$') !== false) {
	$vars['$plugins$'] = '<ul>';
	foreach ($PicoWiki->plugin_list as $pp) {
	  $vars['$plugins$'] .= '<li>'.pathinfo($pp)['filename'].'</li>';
	}
	$vars['$plugins$'] .= '</ul>';
      }
      if (strpos($html,'$attachments$') !== false) {
	$file_path = $PicoWiki->getFilePath($PicoWiki->url);
	$pi = pathinfo($file_path);
	if ($pi['basename'] == $PicoWiki->config['default_doc']) {
	  $file_path = $pi['dirname'];
	} else {
	  $file_path = $pi['dirname'].'/'.$pi['filename'];
	}
	$url_path = substr($file_path,strlen($PicoWiki->config['file_path']));

	$lst = [];
	$dp = @opendir($file_path);
	if ($dp !== false) {
	  while (false !== ($fn = readdir($dp))) {
	    if ($fn[0] == '.' || $fn == $PicoWiki->config['default_doc']) continue;
	    $lst[] = $fn;
	  }
	  closedir($dp);
	}
	if (count($lst)==0) {
	  $vars['$attachments$'] = '<p>No attachments</p>';
	} else {
	  natsort($lst);
	  $vars['$attachments$'] = '<ul>';
	  foreach ($lst as $fn) {
	    $vars['$attachments$'] .= '<li>'.
		  '<a href="'.$PicoWiki->mkUrl($url_path,$fn).'">'.
		  htmlspecialchars($fn).
		  '</a></li>';
	  }
	  $vars['$attachments$'] .= '</ul>';
	}
      }
      if (count($vars) == 0) return $html;
      return strtr($html,$vars);
    });
    $PicoWiki->event('view_before', NULL, function($html) use ($PicoWiki) {
      # variable substitutions #
      $vars = [
	'$url$' => $PicoWiki->url,
      ];
      foreach ([
	    'config'=>$PicoWiki->config,
	    'meta'=>$PicoWiki->meta,
	  ] as $nsp => &$reg) {
	foreach ($reg as $k=>$v) {
	  $vars['$'.$nsp.'.'.$k.'$'] = is_array($v) ? json_encode($v) : $v;
	}
      }
      $vars['$tags$'] = implode(', ',$PicoWiki->meta['all-tags']);
      return strtr($html,$vars);
    });
  }
}
