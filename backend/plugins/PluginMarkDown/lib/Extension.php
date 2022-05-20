<?php
class ParsedownExtension extends ParsedownToC {
  const VERSION = '0.0.0';

  static $graphviz_modes = [ 'dot', 'neato', 'fdp', 'sfdp', 'twopi', 'circo' ];
  public $headown = 1;
  public $gfx_fmt = 'svg'; # Use: 'svg' or 'png'

  function __construct() {
    $this->InlineTypes['^'][] = 'Superscript';
    $this->inlineMarkerList .= '^';
    $this->InlineTypes[','][] = 'Subscript';
    $this->inlineMarkerList .= ',';
    $this->InlineTypes['+'][] = 'InsertedText';
    $this->inlineMarkerList .= '+';
    $this->InlineTypes['='][] = 'KbdText';
    $this->inlineMarkerList .= '=';
    $this->InlineTypes['\\'][] = 'ForcedBr';
    $this->inlineMarkerList .= '\\';

    $this->BlockTypes['!'][] = 'Headown';
  }
  protected function inlineForcedBr($excerpt) {
    //~ echo '<pre>'.htmlspecialchars(print_r($excerpt,true)).'</pre>';
    //~ return;
    if (preg_match('/\\\s*\n/', $excerpt['text'], $matches)) {
      return [
	  'extent' => strlen($matches[0]),
	  'element' => [
	      'name' => 'br',
	  ],
      ];
    }
  }


  protected function inlineSuperscript($excerpt) {
    if (preg_match('/(?:\^\^(?!\^)([^\^ ]*)\^\^(?!\^))/', $excerpt['text'], $matches)) {
      return [
	  'extent' => strlen($matches[0]),
	  'element' => [
	      'name' => 'sup',
	      'text' => $matches[1],
	      'function' => 'lineElements',
	  ],
      ];
    }
  }
  protected function inlineSubscript($excerpt) {
    if (preg_match('/(?:,,(?!,)([^, ]*),,(?!,))/', $excerpt['text'], $matches)) {
      return [
	  'extent' => strlen($matches[0]),
	  'element' => [
	      'name' => 'sub',
	      'text' => $matches[1],
	      'function' => 'lineElements',
	  ],
      ];
    }
  }
  protected function inlineInsertedText($excerpt) {
    if (preg_match('/(?:\+\+(?!\+)([^\+ ]*)\+\+(?!\+))/', $excerpt['text'], $matches)) {
      return [
	  'extent' => strlen($matches[0]),
	  'element' => [
	      'name' => 'ins',
	      'text' => $matches[1],
	      'function' => 'lineElements',
	  ],
      ];
    }
  }
  protected function inlineKbdText($excerpt) {
    if (preg_match('/(?:==(?!=)([^, ]*)==(?!=))/', $excerpt['text'], $matches)) {
      return [
	  'extent' => strlen($matches[0]),
	  'element' => [
	      'name' => 'kbd',
	      'text' => $matches[1],
	      'function' => 'lineElements',
	  ],
      ];
    }
  }

  /*
   * Table span
   */
  protected function blockTableComplete(array $Block) {
    if ( ! isset($Block)) return null;

    //~ $HeaderElements =& $Block['element']['elements'][0]['elements'][0]['elements'];
    $HeaderElements =& $Block['element']['text'][0]['text'][0]['text'];
    for ($index = count($HeaderElements) - 1; $index >= 0; --$index) {
      $colspan = 1;
      $HeaderElement =& $HeaderElements[$index];

      //~ while ($index && $HeaderElements[$index - 1]['handler']['argument'] === '>')
      while ($index && $HeaderElements[$index - 1]['text'] === '>') {
	$colspan++;
	$PreviousHeaderElement =& $HeaderElements[--$index];
	$PreviousHeaderElement['merged'] = true;
	if (isset($PreviousHeaderElement['attributes'])) {
	  $HeaderElement['attributes'] = $PreviousHeaderElement['attributes'];
	}
      }

      if ($colspan > 1) {
	if ( ! isset($HeaderElement['attributes'])) {
	  $HeaderElement['attributes'] = array();
	}
	$HeaderElement['attributes']['colspan'] = $colspan;
      }
    }

    for ($index = count($HeaderElements) - 1; $index >= 0; --$index) {
      if (isset($HeaderElements[$index]['merged'])) {
	array_splice($HeaderElements, $index, 1);
      }
    }

    //~ $Rows =& $Block['element']['elements'][1]['elements'];
    $Rows =& $Block['element']['text'][1]['text'];

    foreach ($Rows as $RowNo => &$Row) {
      //~ $Elements =& $Row['elements'];
      $Elements =& $Row['text'];

      for ($index = count($Elements) - 1; $index >= 0; --$index) {
	$colspan = 1;
	$Element =& $Elements[$index];

	//~ while ($index && $Elements[$index - 1]['handler']['argument'] === '>')
	while ($index && $Elements[$index - 1]['text'] === '>') {
	  $colspan++;
	  $PreviousElement =& $Elements[--$index];
	  $PreviousElement['merged'] = true;
	  if (isset($PreviousElement['attributes'])) {
	    $Element['attributes'] = $PreviousElement['attributes'];
	  }
	}

	if ($colspan > 1) {
	  if ( ! isset($Element['attributes'])) {
	    $Element['attributes'] = array();
	  }
	  $Element['attributes']['colspan'] = $colspan;
	}
      }
    }

    foreach ($Rows as $RowNo => &$Row) {
      //~ $Elements =& $Row['elements'];
      $Elements =& $Row['text'];

      foreach ($Elements as $index => &$Element) {
	$rowspan = 1;

	if (isset($Element['merged'])) continue;

	//~ while ($RowNo + $rowspan < count($Rows) && $index < count($Rows[$RowNo + $rowspan]['elements']) && $Rows[$RowNo + $rowspan]['elements'][$index]['handler']['argument'] === '^' && (@$Element['attributes']['colspan'] ?: null) === (@$Rows[$RowNo + $rowspan]['elements'][$index]['attributes']['colspan'] ?: null))
	while ($RowNo + $rowspan < count($Rows)
		&& $index < count($Rows[$RowNo + $rowspan]['text'])
		&& $Rows[$RowNo + $rowspan]['text'][$index]['text'] === '^'
		&& (@$Element['attributes']['colspan'] ?: null) === (@$Rows[$RowNo + $rowspan]['text'][$index]['attributes']['colspan'] ?: null)) {
	  //~ $Rows[$RowNo + $rowspan]['elements'][$index]['merged'] = true;
	  $Rows[$RowNo + $rowspan]['text'][$index]['merged'] = true;
	  $rowspan++;
	}

	if ($rowspan > 1) {
	  if ( ! isset($Element['attributes'])) {
	    $Element['attributes'] = array();
	  }
	  $Element['attributes']['rowspan'] = $rowspan;
	}
      }
    }

    foreach ($Rows as $RowNo => &$Row) {
      //~ $Elements =& $Row['elements'];
      $Elements =& $Row['text'];

      for ($index = count($Elements) - 1; $index >= 0; --$index) {
	if (isset($Elements[$index]['merged'])) {
	  array_splice($Elements, $index, 1);
	}
      }
    }

    return $Block;
  }


  /*
  * Overrides
  */
  #
  # Fenced code blocks
  #
  protected function blockFencedCodeComplete($Block) {
    $Block = parent::blockFencedCodeComplete($Block);
    if (!isset($Block['element']['text']['text']) ||
	!isset($Block['element']['text']['attributes']['class'])) return $Block;

    $text = $Block['element']['text']['text'];
    $hclass = $Block['element']['text']['attributes']['class'];

    if (substr($hclass,0,strlen('language-graphviz-')) == 'language-graphviz-') {
      // Handle graphviz graphics
      $gmode = substr($hclass,strlen('language-graphviz-'));
      if (!in_array($gmode, self::$graphviz_modes)) return $Block;

      $proc = proc_open([ $gmode , '-T'.$this->gfx_fmt ],
			[
			  0 => ['pipe', 'r'],
			  1 => ['pipe', 'wb'],
			  2 => ['pipe', 'w'],
			],
			$pipes);
      if (is_resource($proc)) {
	fwrite($pipes[0], $text);
	fclose($pipes[0]);

	$output = stream_get_contents($pipes[1]);
	fclose($pipes[1]);

	$stderr = stream_get_contents($pipes[2]);
	fclose($pipes[2]);

	$ret = proc_close($proc);

	//~ echo "gmode: $gmode<br>";
	//~ echo "ret: $ret<br>";
	//~ echo "stderr: $stderr<br>";

	if ($output) {
	  switch ($this->gfx_fmt) {
	  case 'svg':
	    $Block['element'] = [
		'name' => 'div',
		'rawHtml' => $output,
	      ];
	    break;
	  case 'png':
	    $Block['element'] = [
		'name' => 'img',
		'attributes' => [
		  'src' => 'data:image/png;base64,'.base64_encode($output),
		]
	      ];
	    break;
	  }
	}
      }
    } elseif ($hclass == 'language-lineart') {
      $proc = proc_open([ 'svgbob' ],
			[
			  0 => ['pipe', 'r'],
			  1 => ['pipe', 'wb'],
			  2 => ['pipe', 'w'],
			],
			$pipes);
      if (is_resource($proc)) {
	fwrite($pipes[0], $text);
	fclose($pipes[0]);

	$output = stream_get_contents($pipes[1]);
	fclose($pipes[1]);

	$stderr = stream_get_contents($pipes[2]);
	fclose($pipes[2]);

	$ret = proc_close($proc);

	//~ echo "gmode: $gmode<br>";
	//~ echo "ret: $ret<br>";
	//~ echo "stderr: $stderr<br>";

	if ($output) {
	  $Block['element'] = [
	      'name' => 'div',
	      'rawHtml' => $output,
	    ];
	}
      }
    }


    return $Block;
  }


  #
  # Header


  protected function blockHeader($Line) {

    if (isset($Line['text'][1])) {
      if (trim($Line['text']) == '#++') {
	$this->headown++;
	return ['hidden' => true];
      } elseif (trim($Line['text']) == '#--') {
	$this->headown--;
	return ['hidden' => true];
      }

      $level = $this->headown;
      while (isset($Line['text'][$level]) and $Line['text'][$level] === '#') 	{
	$level ++;
      }
      if ($level > 6) return;
      $text = trim($Line['text'], '# ');
      $Block = array(
	  'element' => array(
	      'name' => 'h' . min(6, $level),
	      'text' => $text,
	      'handler' => 'line',
	  ),
      );
      return $Block;
    }
  }

  #
  # List
  #
  protected function blockList($Line)
  {
      list($name, $pattern) = $Line['text'][0] <= '-' ? array('ul', '[*+-]') : array('ol', '[0-9]{1,9}[.\)]');
      if (preg_match('/^('.$pattern.'([ ]+|$))(.*)/', $Line['text'], $matches))
      {
	  $contentIndent = strlen($matches[2]);
	  if ($contentIndent >= 5)
	  {
	      $contentIndent -= 1;
	      $matches[1] = substr($matches[1], 0, -$contentIndent);
	      $matches[3] = str_repeat(' ', $contentIndent) . $matches[3];
	  }
	  elseif ($contentIndent === 0)
	  {
	      $matches[1] .= ' ';
	  }
	  $Block = array(
	      'indent' => $Line['indent'],
	      'pattern' => $pattern,
	      'data' => array(
		  'type' => $name,
		  'marker' => $matches[1],
		  'markerType' => ($name === 'ul' ? strstr($matches[1], ' ', true) : substr(strstr($matches[1], ' ', true), -1)),
	      ),
	      'element' => array(
		  'name' => $name,
		  'handler' => 'elements',
	      ),
	  );

	  if($name === 'ol')
	  {
	      $listStart = ltrim(strstr($matches[1], $Block['data']['markerType'], true), '0') ?: '0';

	      if($listStart !== '1')
	      {
		  $Block['element']['attributes'] = array('start' => $listStart);
	      }
	  }

	  $Block['li'] = array(
	      'name' => 'li',
	      'handler' => 'li',
	      'text' => !empty($matches[3]) ? array($matches[3]) : array(),
	  );

	  $Block['element']['text'] []= & $Block['li'];

	  return $Block;
      }
  }

  protected function blockListContinue($Line, array $Block)
  {
      if (isset($Block['interrupted']) and empty($Block['li']['text']))
      {
	  return null;
      }
      $requiredIndent = ($Block['indent'] + strlen($Block['data']['marker']));
      if ($Line['indent'] < $requiredIndent
	  and (
	      (
		  $Block['data']['type'] === 'ol'
		  and preg_match('/^[0-9]+'.preg_quote($Block['data']['markerType']).'(?:[ ]+(.*)|$)/', $Line['text'], $matches)
	      ) or (
		  $Block['data']['type'] === 'ul'
		  and preg_match('/^'.preg_quote($Block['data']['markerType']).'(?:[ ]+(.*)|$)/', $Line['text'], $matches)
	      )
	  )
      ) {
	  if (isset($Block['interrupted']))
	  {
	      $Block['li']['text'] []= '';

	      $Block['loose'] = true;

	      unset($Block['interrupted']);
	  }

	  unset($Block['li']);

	  $text = isset($matches[1]) ? $matches[1] : '';

	  $Block['indent'] = $Line['indent'];
	  $Block['li'] = array(
	      'name' => 'li',
	      'handler' => 'li',
	      'text' => array(
		  $text,
	      ),
	  );

	  $Block['element']['text'] []= & $Block['li'];

	  return $Block;
      }

      elseif ($Line['indent'] < $requiredIndent and $this->blockList($Line))
      {
	  return null;
      }
      if ($Line['text'][0] === '[' and $this->blockReference($Line))
      {
	  return $Block;
      }

      if ($Line['indent'] >= $requiredIndent)
      {

	  if (isset($Block['interrupted']))
	  {
	      $Block['li']['text'] []= '';
	      unset($Block['interrupted']);
	  }
	  $text = substr($Line['body'], $requiredIndent);
	  $Block['li']['text'] []= $text;

	  return $Block;
      }

      if ( ! isset($Block['interrupted']))
      {
	  $text = preg_replace('/^[ ]{0,'.$requiredIndent.'}/', '', $Line['body']);
	  $Block['li']['text'] []= $text;

	  return $Block;
      }
  }

  protected function blockListComplete(array $Block) {
    $Block = parent::blockListComplete($Block);
    if ($Block['element']['name'] != 'ul') return $Block;
    foreach ($Block['element']['text'] as &$li) {
      if ($li['name'] != 'li') continue;
      if (preg_match('/^\[([ xX])\]\s/',$li['text'][0],$mv)) {
	$li['text'][0] = '<input type="checkbox" disabled '.
	    ($mv[1] == ' ' ? '' : 'checked'). '> '.
	    substr($li['text'][0],strlen($mv[0]));
      }
    }
    return $Block;
  }


}


