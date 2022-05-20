<?php
$config = [
  'app_name'		=> 'NanoWiki',
  'title'		=> 'NanoWiki',
  'app_url'		=> null, // (auto-detected, although you can manually specify it if you need to)
  'file_path' 		=> __DIR__ . '/files', // no trailing slash
  'default_doc'		=> 'index.md',
  'copyright'		=> 'nobody@nowhere',
  'read_only'		=> false,
];
if (file_exists(__DIR__.'/config.yaml')) {
  $config = array_merge($config, yaml_parse_file(__DIR__.'/config.yaml'));
}

class PicoWiki
{
    public $config = null; // configuration variables
    public $file_list = []; // array of available files
    public $plugin_list = [];
    public $handlers = []; // media handlers (based on file extension)
    public $url = null;
    public $html = null;
    public $source = null;
    public $events = [];
    public $meta = [];	// current doc meta data

    /**
     * Converts a long string of bytes into a readable format e.g KB, MB, GB, TB, YB
     *
     * @param {Int} num The number of bytes.
     */
    static public function readableBytes($bytes) {
      $i = floor(log($bytes) / log(1024));
      $sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
      return sprintf('%.02F', $bytes / pow(1024, $i)) * 1 . ' ' . $sizes[$i];
    }

    # These are simple optimizations
    private $caches = [ 'meta' => [], 'contents' => [], 'offsets' => [] ];
    public function fileGetContents($file_path) {
      if (!isset($this->caches['contents'][$file_path]))
	$this->caches['contents'][$file_path] = file_get_contents($file_path);
      return $this->caches['contents'][$file_path];
    }
    public function fileGetMeta($file_path) {
      if (!isset($this->caches['meta'][$file_path])) {
	$pi = pathinfo($file_path);

	if (isset($this->handlers[$pi['extension'] ?? ''])) {
	  $obj = $this->handlers[$pi['extension']];
	  list($meta,$offset) = call_user_func_array([$obj,'readMeta'],[$this,$file_path]);
	} else {
	  $meta = [];
	  $offset = 0;
	}
	if (empty($meta['title'])) {
	  if (basename($file_path) == $this->config['default_doc']) {
	    $meta['title'] = $this->url;
	  } else {
	    $meta['title'] = $pi['filename'];
	  }
	}

	$file_date = filemtime($file_path);
	$file_size = filesize($file_path);

	$meta = array_merge($meta, [
	    'file-path' => $file_path,
	    'file-ext' => $pi['extension'] ?? '',
	    'file-name' => $pi['filename'],
	    'file-datetime' => gmdate('Y-m-d H:i:s',$file_date),
	    'file-epoch' => $file_date,
	    'file-year' => gmdate('Y'),
	    'file-date' => gmdate('Y-m-d'),
	    'file-size' => self::readableBytes($file_size),
	    'file-bytes' => $file_size,
	    'file-tags' => [ gmdate('Y',$file_date) ],
	  ]);
	if (!empty($pi['extension'])) {
	  $meta['file-tags'][] = $pi['extension'];
	}
	//~ echo '<pre>';
	//~ print_r([$meta,$offset]);
	//~ echo '</pre>';
	$all_tags = [];

        $meta = $this->event('meta_read_after', $meta);

	// Fill in standard tags as needed...
	$meta['date'] = $meta['date'] ?? $meta['file-date'];
	$meta['year'] = $meta['year'] ?? $meta['file-year'];

	foreach ($meta as $k => $v) {
	  if ($k != 'tags' && substr($k,-5) != '-tags') continue;
	  if (!is_array($v)) $v = preg_split('/\s*,\s*/',$v);
	  foreach ($v as $i) {
	    if (is_array($i)) continue;
	    $i = strtolower($i);
	    $all_tags[$i] = $i;
	  }
	}
	natsort($all_tags);
	$meta['all-tags'] = $all_tags;

	$this->caches['meta'][$file_path] = $meta;
	$this->caches['offsets'][$file_path] = $offset;
      }
      return $this->caches['meta'][$file_path];
    }
    public function fileGetOffset($file_path) {
      return $this->caches['offsets'][$file_path] ?? 0;
    }

    static public function VERSION() {
      return trim(file_get_contents(__DIR__.'/VERSION'));
    }
    static public function sanitize($url) {
      $url = preg_replace('/\s+/','_', $url);
      $url = preg_replace('/[^A-Za-z0-9-\/\._]/', '', $url);
      $url = preg_replace('/\/\.\.?\//', '/', '/'.$url.'/');
      $url = preg_replace('/\/+/', '/', $url);
      $url = preg_replace('/^\/+/', '', $url);
      $url = preg_replace('/\/+$/', '', $url);

      $d = dirname($url);
      $b = basename($url);

      if (false !== ($i = strrpos($b,'.')) && $i > 0) {
	$b = substr($b,0,$i).strtolower(substr($b,$i));
	if ($d == '.') {
	  $url = $b;
	} else {
	  $url = $d.'/'.$b;
	}
      }
      if ($d && $d != '.') {
	$d = preg_replace('/\./','', $d);
	$url = $d . '/' . $b;
      }
      return $url;
    }

    public function __construct($config) {
      $this->event('init', $this);
      $this->config = $config;
      if (!$this->config['app_url']) {
	$this->config['app_url'] = '//'.$_SERVER['HTTP_HOST'].str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
      }
      if (empty($this->config['app_dir'])) $this->config['app_dir'] = __DIR__;
      $this->event('config_loaded', $this);
      $this->loadPlugins();
      $this->event('plugins_loaded', $this);
    }


    /* These two functions are stubs for the moment... */
    public function isWritable($filepath) {
      if (empty($this->config['read_only'])) {
	$wr = true;
      } else {
	if (is_bool($this->config['read_only'])) {
	  $wr = !$this->config['read_only'];
	} else if (is_string($this->config['read_only'])) {
	  $wr = strtolower($this->config['read_only']);
	  switch($wr) {
	  case 'not-auth':
	    # TODO: check how auth user is received
	    # $_SERVER['PHP_AUTH_USER'] or REMOTE_USER
	    $wr = true;
	    break;
	  case 'no':
	  case 'false':
	  case 'n':
	    $wr = true;
	    break;
	  default:
	    $wr = !(bool)$wr;
	  }
	} else {
	  $wr = !(bool)$this->config['read_only'];
	}
      }
      return $this->event('check_writeable', $wr);
    }
    public function isReadable($filepath) {
      return $this->event('check_readable', true);
    }

    public function mkUrl(...$uri) {
      $uri = preg_replace('/\/+/','/',trim(implode('/',$uri),'/'));
      return rtrim($this->config['app_url'],'/').'/'.$uri;
    }
    /**
     * Finds a file with that URL and outputs it nicely
     *
     * @param string $url a URL slug that should presumably match a file
     */
    public function run($url = null) {
      $this->event('run_init', $this);
      $this->url = self::sanitize($url);
      $this->event('url_loaded', $this);

      $this->file_list = $this->listFiles($this->config['file_path']);
      $this->event('list_loaded', $this);

      $file_path = $this->getFilePath($this->url);
      if (count($_POST)) {
	if (!$this->isWritable($file_path)) {
	  $this->event('write_access_error',$this);
	  die("Write access: $file_path"); #TODO:
	}
	$this->post($file_path);
      } else {
	if (!file_exists($file_path)) {
	  $this->error404($file_path);
	  return;
	}
	$this->meta = $this->fileGetMeta($file_path);

	if (!$this->isReadable($file_path)) {
	  $this->event('read_access_error',$this);
	  die("Read access: $file_path");
	}
	$this->view($file_path);
      }
    }

    /**
     * Handles 404 errors
     *
     * @param string $file_path to missing file
     */
    public function error404($file_path) {
      http_response_code(404);
      $this->meta['year'] = $this->meta['year'] ?? gmdate('Y');
      $this->meta['title'] = $this->meta['title'] ??
	'404: '.htmlspecialchars($this->url);

      $this->event('error404', $this);

      $ext = pathinfo($file_path)['extension'] ?? '';
      if (isset($this->handlers[$ext])) {
	$obj = $this->handlers[$ext];
	call_user_func_array([$obj,'error404'],[$this,$file_path]);
	return;
      }
      // Default 404 hanlder
      $PicoWiki = $this;
      require(__DIR__ . '/backend/templates/404.html');
    }


    /**
     * Reads all files in the path
     *
     * @param string $path a glob path pattern
     */
    protected function listFiles($path)
    {
        $this->file_list = $this->readDirectory($path);
        natsort($this->file_list);
        return $this->file_list;
    }

    /**
     * Returns a list of all files recursive
     *
     * @param string $path a directory path
     */
    protected function readDirectory($path) {
      $dq = [ '' ];
      $files = [];
      while (count($dq)) {

	$cd = array_shift($dq);
	$dp = opendir($path . $cd);
	if ($dp === false) continue;

	$cd = $cd .'/';
	while (false !== ($fn = readdir($dp))) {
	  if ($fn[0] == '.' || $fn == $this->config['default_doc']) continue;
	  $files[] = $cd . $fn;
	  if (is_dir($path. $cd.$fn)) $dq[] = $cd.$fn;
	}
	closedir($dp);
      }
      return $files;
    }

    /**
     * Returns the full path to a file in /files/ folder based on its filename
     *
     * @param string $file_name file name to get the full path from
     */
    public function getFilePath($file_name) {
      $file_path = $this->config['file_path'].'/'.$file_name;
      if (file_exists($file_path.'/'.$this->config['default_doc']))
	$file_path = $this->config['file_path'].'/'.$file_name.'/'.$this->config['default_doc'];

      return $file_path;
    }

    protected function viewDir($file_path) {
      $PicoWiki = $this;

      $lst = [];
      $dp = @opendir($file_path);
      if ($dp !== false) {
	while (false !== ($fn = readdir($dp))) {
	  if ($fn[0] == '.' || $fn == $PicoWiki->config['default_doc']) continue;
	  $lst[] = $fn;
	}
	closedir($dp);
      }
      natsort($lst);

      require(__DIR__ . '/backend/templates/folder.html');
    }
    /**
     * Outputs the templates and files
     * You can use file_get_contents($file_path) instead of require to disable running PHP code in .md files
     *
     * @param string $file_path full path to the Markdown file
     */
    protected function view($file_path) {
      header('Accept-Ranges: bytes');

      if (basename($file_path) == $this->config['default_doc']
	  && isset($_GET['tools'])
	  && $_GET['tools']) {
	$this->viewDir(dirname($file_path));
	return;
      }

      $ext = pathinfo($file_path)['extension'] ?? '';
      if (isset($this->handlers[$ext])) {
	$obj = $this->handlers[$ext];

	$this->source = $this->fileGetContents($file_path);
	//~ echo "<pre>";print_r($PicoWiki);echo "</pre>";
	$this->html = substr($this->source,$this->fileGetOffset($file_path));
	$this->html = $this->event('view_before', $this->html);

	$this->html = call_user_func_array([$obj,'render'],[$this,$this->html]);

	$this->html = $this->event('view_after', $this->html);

	call_user_func_array([$obj,'view'],[$this]);
	return;
      }
      if (is_dir($file_path)) {
	$this->viewDir($file_path);
	return;
      }

      ### Remove headers that might unnecessarily clutter up the output
      header_remove('Cache-Control');
      header_remove('Pragma');

      $mime = mime_content_type($file_path);
      if ($mime === false) $mime = 'application/octet-stream';
      header('Content-Type: '.$mime);
      header('Content-Disposition: filename="'
		. basename($file_path) . '"');

      ### Default to send entire file
      $byteOffset = 0;
      $byteLength = $fileSize = filesize($file_path);

      ### Parse Content-Range header for byte offsets, looks like "bytes=11525-" OR "bytes=11525-12451"
      if( isset($_SERVER['HTTP_RANGE']) && preg_match('%bytes=(\d+)-(\d+)?%i', $_SERVER['HTTP_RANGE'], $match) ) {
	### Offset signifies where we should begin to read the file
	$byteOffset = (int)$match[1];

	### Length is for how long we should read the file according to the browser, and can never go beyond the file size
	if( isset($match[2]) ){
	  $finishBytes = (int)$match[2];
	  $byteLength = $finishBytes + 1;
	} else {
	  $finishBytes = $fileSize - 1;
	}
	$cr_header = sprintf('Content-Range: bytes %d-%d/%d', $byteOffset, $finishBytes, $fileSize);

	header('HTTP/1.1 206 Partial content');
	header($cr_header);  ### Decrease by 1 on byte-length since this definition is zero-based index of bytes being sent
      }

      if ($byteOffset >= $byteLength) {
	http_response_code(416);
	die('Range outside resource size: '.$_SERVER['HTTP_RANGE']);
      }

      $byteRange = $byteLength - $byteOffset;

      header('Content-Length: ' . $byteRange);
      header('Expires: '. date('D, d M Y H:i:s', time() + 60*60*24*90) . ' GMT');

      $buffer = ''; 			### Variable containing the buffer
      $bufferSize = 1024 * 32;		### Just a reasonable buffer size
      $bytePool = $byteRange;		### Contains how much is left to read of the byteRange

      if(!($handle = fopen($file_path, 'r'))) die("Error reading: $file_path");
      if(fseek($handle, $byteOffset, SEEK_SET) == -1 ) die("Error seeking file");

      while( $bytePool > 0 ) {
	$chunkSizeRequested = min($bufferSize, $bytePool); ### How many bytes we request on this iteration

	### Try readin $chunkSizeRequested bytes from $handle and put data in $buffer
	$buffer = fread($handle, $chunkSizeRequested);

	### Store how many bytes were actually read
	$chunkSizeActual = strlen($buffer);

	### If we didn't get any bytes that means something unexpected has happened since $bytePool should be zero already
	if( $chunkSizeActual == 0 ) die('Chunksize became 0');

	### Decrease byte pool with amount of bytes that were read during this iteration
	$bytePool -= $chunkSizeActual;

	### Write the buffer to output
	print $buffer;

	### Try to output the data to the client immediately
	flush();
      }
    }
    /**
     * Handle post actions
     *
     * @param string $file_path full path to the Markdown file
     */
    protected function post($file_path)
    {
      if (empty($_POST['action'])) die("No action in POST");

      switch (strtolower($_POST['action'])) {
	case 'save':
	  $this->save($file_path);
	  break;
	case 'attach':
	  $this->attach($file_path);
	  break;
	default:
	  die('Unknown action: '.$_POST['action']);
      }
    }
    /**
     * Handle attach action
     */
    protected function attach($file_path) {
      if (!isset($_FILES['fileToUpload'])) die("Invalid FORM response");
      $fd = $_FILES['fileToUpload'];
      if (isset($fd['size']) && $fd['size'] == 0) die("Zero file submitted");
      if (isset($fd['error']) && $fd['error'] != 0) die('Error: '.$fd['error']);
      if (empty($fd['name']) || empty($fd['tmp_name'])) die("No file uploaded");

      $fname = self::sanitize(basename($fd['name']));

      if (basename($file_path) == $this->config['default_doc'])
	$file_path = dirname($file_path);

      if (file_exists($file_path.'/'.$fname)) die("$fname: File already exists");

      echo '<pre>';
      echo "url: $this->url\n";
      echo "file_path: $file_path\n";
      echo "fname: $fname\n";

      print_r($_POST);
      print_r($_FILES);

      if (!is_dir($file_path)) {
	if (mkdir(dirname($file_path),0777,true) === false)
	  die("Unable to create: $file_path");
      }
      if (!move_uploaded_file($fd['tmp_name'],$file_path.'/'.$fname))
	die("Error saving uploaded file");

      header('Location: '.$_SERVER['REQUEST_URI']);
    }
    /**
     * Handle save action
     */
    protected function save($file_path) {
      echo '<pre>';

      if (empty($_POST['payload'])) die("No payload!");
      $payload = $_POST['payload'];
      $payload = $this->event('payload_pre', $payload);

      $ext = pathinfo($file_path)['extension'] ?? '';
      if (isset($this->handlers[$ext])) {
	$obj = $this->handlers[$ext];

	list($meta,$body) = call_user_func_array([$obj,'payload_before'],[$this,$file_path,$payload]);

	$fattr = [ 'file-path' => $file_path ];
	if (!file_exists($file_path)) $fattr['created'] = true;
        list($meta,) = $this->event('meta_write_before', [$meta,$fattr]);

	$payload = call_user_func_array([$obj,'payload_after'],[$this,$meta,$body]);
      }

      $payload = $this->event('payload_post', $payload);

      if (!is_dir(dirname($file_path))) {
	if (mkdir(dirname($file_path),0777,true) === false)
	  die("Unable to create: $file_path");
      }
      echo "FILE_PATH: $file_path\n";
      echo htmlspecialchars($payload);

      if (file_put_contents($file_path,$payload) === false)
	die("Error saving to: $file_path");

      header('Location: '.$_SERVER['REQUEST_URI']);

      echo '<a href="'.$_SERVER['REQUEST_URI'].'">OK!</a>';

      echo '<pre>';
      echo "file_path: $file_path\n";
      print_r($_POST);
      echo '</pre>';
    }

    /**
     * Finds .php files inside the /plugins/ folder, stores the list and initializes them
     */
    protected function loadPlugins()
    {
        $this->plugin_list = glob( __DIR__ . '/backend/plugins/*.php');
        foreach ($this->plugin_list as $plugin_file) {
            $class_name = pathinfo($plugin_file)['filename'];
            require_once $plugin_file;
            call_user_func_array([ $class_name, 'load'], [$this] );
        }
    }

    /**
     * Attach (or remove) multiple callbacks to an event and trigger those callbacks when that event is called.
     * https://github.com/Xeoncross/micromvc/blob/master/Common.php#L15
     *
     * @param string $event name
     * @param mixed $value the optional value to pass to each callback
     * @param mixed $callback the method or function to call - FALSE to remove all callbacks for event
     */
    public function event($event, $value = NULL, $callback = NULL) {
      // Adding or removing a callback?
      if ($callback !== NULL) {
	  if ($callback) {
	      $this->events[$event][] = $callback;
	  } else {
	      unset($this->events[$event]);
	  }
      } else {
	if (isset($this->events[$event])) { // Fire a callback
	  foreach($this->events[$event] as $function) {
	      $value = call_user_func($function, $value);
	  }
	}
	return $value;
      }
    }

    /**
     * Register a media handler for the given file extension
     *
     * @param string $ext file extension
     * @param mixed $callback object or classname used for media handling.
     */
    public function handler($ext, $callback) {
      $this->handlers[$ext] = $callback;
    }
}

$PicoWiki = new PicoWiki($config);
$PicoWiki->run(@$_GET['url']);
