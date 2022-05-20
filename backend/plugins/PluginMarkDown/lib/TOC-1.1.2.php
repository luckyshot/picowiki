<?php
/**
 * ToC Extension/Plugin for Parsedown.
 * ============================================================================
 * It creates a list of contents table from the headings in Markdown text.
 *
 * @author      KEINOS (https://github.com/KEINOS/)
 *              Contributors (https://github.com/KEINOS/parsedown-extension_table-of-contents/graphs/contributors)
 * @package     Parsedown ^1.7 (https://github.com/erusev/parsedown)
 * @php         ^5.6.40
 * @see         HowTo: https://github.com/KEINOS/parsedown-extension_table-of-contents/
 * @license     MIT: https://github.com/KEINOS/parsedown-extension_table-of-contents/LICENSE
*/

// Make it compatible with ParsedownExtra
//   - Feature Implementation from Issue #13 by @qwertygc
//     https://github.com/KEINOS/parsedown-extension_table-of-contents/issues/13
if (class_exists('ParsedownExtra')) {
    class DynamicParent extends \ParsedownExtra
    {
        public function __construct()
        {
            parent::__construct();
        }
    }
} else {
    class DynamicParent extends \Parsedown
    {
        public function __construct()
        {
            //
        }
    }
}

// Old version compatibility (Deprecated since v1.1.0 and will be removed in v1.2.0)
/*
class Extension extends ParsedownToC
{
}
*/

class ParsedownToC extends DynamicParent
{
    /**
     * ------------------------------------------------------------------------
     *  Constants.
     * ------------------------------------------------------------------------
     */
    const version = '1.1.2'; // Version is available since v1.1.0
    const VERSION_PARSEDOWN_REQUIRED = '1.7';
    const TAG_TOC_DEFAULT = '[toc]';
    const ID_ATTRIBUTE_DEFAULT = 'toc';

    /**
     * Version requirement check.
     */
    public function __construct()
    {
        if (version_compare(\Parsedown::version, self::VERSION_PARSEDOWN_REQUIRED) < 0) {
            $msg_error  = 'Version Error.' . PHP_EOL;
            $msg_error .= '  Parsedown ToC Extension requires a later version of Parsedown.' . PHP_EOL;
            $msg_error .= '  - Current version : ' . \Parsedown::version . PHP_EOL;
            $msg_error .= '  - Required version: ' . self::VERSION_PARSEDOWN_REQUIRED . PHP_EOL;
            throw new Exception($msg_error);
        }

        parent::__construct();
    }

    /**
     * ------------------------------------------------------------------------
     * Methods (in ABC order)
     * ------------------------------------------------------------------------
     */

    /**
     * Heading process.
     * Creates heading block element and stores to the ToC list. It overrides
     * the parent method: \Parsedown::blockHeader() and returns $Block array if
     * the $Line is a heading element.
     *
     * @param  array $Line  Array that Parsedown detected as a block type element.
     * @return void|array   Array of Heading Block.
     */
    protected function blockHeader($Line)
    {
        // Use parent blockHeader method to process the $Line to $Block
        $Block = DynamicParent::blockHeader($Line);

        if (! empty($Block)) {
            // Get the text of the heading
            if (isset($Block['element']['handler']['argument'])) {
                // Compatibility with old Parsedown Version
                $text = $Block['element']['handler']['argument'];
            }
            if (isset($Block['element']['text'])) {
                // Current Parsedown
                $text = $Block['element']['text'];
            }

            // Get the heading level. Levels are h1, h2, ..., h6
            $level = $Block['element']['name'];

            // Get the anchor of the heading to link from the ToC list
            $id = isset($Block['element']['attributes']['id']) ?
                $Block['element']['attributes']['id'] : $this->createAnchorID($text);

            // Set attributes to head tags
            $Block['element']['attributes'] = array(
                'id'   => $id,
                'name' => $id,
            );

            // Add/stores the heading element info to the ToC list
            $this->setContentsList(array(
                'text'  => $text,
                'id'    => $id,
                'level' => $level
            ));

            return $Block;
        }
    }

    /**
     * Parses the given markdown string to an HTML string but it leaves the ToC
     * tag as is. It's an alias of the parent method "\DynamicParent::text()".
     *
     * @param  string $text  Markdown string to be parsed.
     * @return string        Parsed HTML string.
     */
    public function body($text)
    {
        $text = $this->encodeTagToHash($text);   // Escapes ToC tag temporary
        $html = DynamicParent::text($text);      // Parses the markdown text
        $html = $this->decodeTagFromHash($html); // Unescape the ToC tag

        return $html;
    }

    /**
     * Returns the parsed ToC.
     * If the arg is "string" then it returns the ToC in HTML string.
     *
     * @param  string $type_return  Type of the return format. "string" or "json".
     * @return string               HTML/JSON string of ToC.
     */
    public function contentsList($type_return = 'string')
    {
        if ('string' === strtolower($type_return)) {
            $result = '';
            if (! empty($this->contentsListString)) {
                // Parses the ToC list in markdown to HTML
                $result = $this->body($this->contentsListString);
            }
            return $result;
        }

        if ('json' === strtolower($type_return)) {
            return json_encode($this->contentsListArray);
        }

        // Forces to return ToC as "string"
        error_log(
            'Unknown return type given while parsing ToC.'
            . ' At: ' . __FUNCTION__ . '() '
            . ' in Line:' . __LINE__ . ' (Using default type)'
        );
        return $this->contentsList('string');
    }

    /**
     * Generates an anchor text that are link-able even the heading is not in
     * ASCII.
     *
     * @param  string $text
     * @return string
     */
    protected function createAnchorID($text)
    {
        return  urlencode($this->fetchText($text));
    }

    /**
     * Decodes the hashed ToC tag to an original tag and replaces.
     *
     * This is used to avoid parsing user defined ToC tag which includes "_" in
     * their tag such as "[[_toc_]]". Unless it will be parsed as:
     *   "<p>[[<em>TOC</em>]]</p>"
     *
     * @param  string $text
     * @return string
     */
    protected function decodeTagFromHash($text)
    {
        $salt = $this->getSalt();
        $tag_origin = $this->getTagToC();
        $tag_hashed = hash('sha256', $salt . $tag_origin);

        if (strpos($text, $tag_hashed) === false) {
            return $text;
        }

        return str_replace($tag_hashed, $tag_origin, $text);
    }

    /**
     * Encodes the ToC tag to a hashed tag and replace.
     *
     * This is used to avoid parsing user defined ToC tag which includes "_" in
     * their tag such as "[[_toc_]]". Unless it will be parsed as:
     *   "<p>[[<em>TOC</em>]]</p>"
     *
     * @param  string $text
     * @return string
     */
    protected function encodeTagToHash($text)
    {
        $salt = $this->getSalt();
        $tag_origin = $this->getTagToC();

        if (strpos($text, $tag_origin) === false) {
            return $text;
        }

        $tag_hashed = hash('sha256', $salt . $tag_origin);

        return str_replace($tag_origin, $tag_hashed, $text);
    }

    /**
     * Get only the text from a markdown string.
     * It parses to HTML once then trims the tags to get the text.
     *
     * @param  string $text  Markdown text.
     * @return string
     */
    protected function fetchText($text)
    {
        return trim(strip_tags($this->line($text)));
    }

    /**
     * Gets the ID attribute of the ToC for HTML tags.
     *
     * @return string
     */
    protected function getIdAttributeToC()
    {
        if (isset($this->id_toc) && ! empty($this->id_toc)) {
            return $this->id_toc;
        }

        return self::ID_ATTRIBUTE_DEFAULT;
    }

    /**
     * Unique string to use as a salt value.
     *
     * @return string
     */
    protected function getSalt()
    {
        static $salt;
        if (isset($salt)) {
            return $salt;
        }

        $salt = hash('md5', time());
        return $salt;
    }

    /**
     * Gets the markdown tag for ToC.
     *
     * @return string
     */
    protected function getTagToC()
    {
        if (isset($this->tag_toc) && ! empty($this->tag_toc)) {
            return $this->tag_toc;
        }

        return self::TAG_TOC_DEFAULT;
    }

    /**
     * Set/stores the heading block to ToC list in a string and array format.
     *
     * @param  array $Content   Heading info such as "level","id" and "text".
     * @return void
     */
    protected function setContentsList(array $Content)
    {
        // Stores as an array
        $this->setContentsListAsArray($Content);
        // Stores as string in markdown list format.
        $this->setContentsListAsString($Content);
    }

    /**
     * Sets/stores the heading block info as an array.
     *
     * @param  array $Content
     * @return void
     */
    protected function setContentsListAsArray(array $Content)
    {
        $this->contentsListArray[] = $Content;
    }
    protected $contentsListArray = array();

    /**
     * Sets/stores the heading block info as a list in markdown format.
     *
     * @param  array $Content  Heading info such as "level","id" and "text".
     * @return void
     */
    protected function setContentsListAsString(array $Content)
    {
        $text  = $this->fetchText($Content['text']);
        $id    = $Content['id'];
        $level = (integer) trim($Content['level'], 'h');
        $link  = "[${text}](#${id})";

        if ($this->firstHeadLevel === 0) {
            $this->firstHeadLevel = $level;
        }
        $cutIndent = $this->firstHeadLevel - 1;
        if ($cutIndent > $level) {
            $level = 1;
        } else {
            $level = $level - $cutIndent;
        }

        $indent = str_repeat('  ', $level);

        // Stores in markdown list format as below:
        // - [Header1](#Header1)
        //   - [Header2-1](#Header2-1)
        //     - [Header3](#Header3)
        //   - [Header2-2](#Header2-2)
        // ...
        $this->contentsListString .= "${indent}- ${link}" . PHP_EOL;
    }
    protected $contentsListString = '';
    protected $firstHeadLevel = 0;

    /**
     * Sets the user defined ToC markdown tag.
     *
     * @param  string $tag
     * @return void
     */
    public function setTagToc($tag)
    {
        $tag = trim($tag);
        if (self::escape($tag) === $tag) {
            // Set ToC tag if it's safe
            $this->tag_toc = $tag;
        } else {
            // Do nothing but log
            error_log(
                'Malformed ToC user tag given.'
                . ' At: ' . __FUNCTION__ . '() '
                . ' in Line:' . __LINE__ . ' (Using default ToC tag)'
            );
        }
    }
    protected $tag_toc='';

    /**
     * Parses markdown string to HTML and also the "[toc]" tag as well.
     * It overrides the parent method: \Parsedown::text().
     *
     * @param  string $text
     * @return void
     */
    public function text($text)
    {
        // Parses the markdown text except the ToC tag. This also searches
        // the list of contents and available to get from "contentsList()"
        // method.
        $html = $this->body($text);

        $tag_origin  = $this->getTagToC();

        if (strpos($text, $tag_origin) === false) {
            return $html;
        }

        $toc_data = $this->contentsList();
        $toc_id   = $this->getIdAttributeToC();
        $needle  = '<p>' . $tag_origin . '</p>';
        $replace = "<div id=\"${toc_id}\">${toc_data}</div>";

        return str_replace($needle, $replace, $html);
    }
}
