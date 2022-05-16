<?php
class ParsedownExtension extends ParsedownToC {
  const VERSION = '0.0.0';

  function __construct() {
    $this->InlineTypes['^'][] = 'Superscript';
    $this->inlineMarkerList .= '^';
    $this->InlineTypes[','][] = 'Subscript';
    $this->inlineMarkerList .= ',';
    $this->InlineTypes['+'][] = 'InsertedText';
    $this->inlineMarkerList .= '+';

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
  # List
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


