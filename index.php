<?php

$config = [
    'app_name'			=> 'PicoWiki',
    'app_url'			=> null, // (auto-detected, although you can manually specify it if you need to)
    'file_path' 		=> __DIR__ . '/files', // no trailing slash
    'file_extension' 	=> 'md',
    'version'			=> '1.1.0',
];

class PicoWiki
{
    public $config = null; // configuration variables
    public $file_list = []; // array of available files
    public $plugin_list = [];
    public $url = null;
    public $html = null;
    public $events = [];

    public function __construct($config)
    {
        $this->event('init', $this);
        $this->config = $config;
        if (!$this->config['app_url']) {
            $this->config['app_url'] = '//'.$_SERVER['HTTP_HOST'].str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
        }
        $this->event('config_loaded', $this);
        $this->loadPlugins();
        $this->event('plugins_loaded', $this);
    }

    /**
     * Finds a file with that URL and outputs it nicely
     *
     * @param string $url a URL slug that should presumably match a file
     */
    public function run($url = null)
    {
        $this->event('run_init', $this);
        $this->url = preg_replace('/[^a-z0-9-\/]/', '', strtolower($url));
        $this->event('url_loaded', $this);
        $this->file_list = $this->listFiles($this->config['file_path'].'/*.'.$this->config['file_extension']);
        $file_path = $this->getFilePath($this->url);
        $this->event('list_loaded', $this);
        $this->view($file_path);
    }

    /**
     * Reads all files in the path
     *
     * @param string $path a glob path pattern
     */
    protected function listFiles($path)
    {
        $this->file_list = $this->readDirectory($path);

        $this->file_list = array_map(function ($f) {
            $f = str_replace($this->config['file_path'], '', $f);
            $f = str_replace('index.'.$this->config['file_extension'], '', $f);
            $f = str_replace('.'.$this->config['file_extension'], '', $f);
            $f = trim($f, '/');
            return $f;
        }, $this->file_list);

        sort($this->file_list);
        return $this->file_list;
    }

    /**
     * Returns a list of all files that match a pattern, recursive
     *
     * @param string $pattern a glob path pattern
     */
    protected function readDirectory($pattern)
    {
        $first_files = glob($pattern);
        foreach (glob(dirname($pattern).'/*') as $dir) {
            $first_files = array_merge($first_files, $this->readDirectory($dir.'/'.basename($pattern)));
        }
        return $first_files;
    }

    /**
     * Returns the full path to a file in /files/ folder based on its filename
     *
     * @param string $file_name file name to get the full path from
     */
    protected function getFilePath($file_name)
    {
        if ($file_name === 404) {
            http_response_code(404);
            $file_path = __DIR__.'/backend/templates/404.md';
        } else {
            $file_path = $this->config['file_path'].'/'.$file_name.'.'.$this->config['file_extension'];
            if (!file_exists($file_path)) {
                $file_path = $this->config['file_path'].'/'.$file_name.'/index.'.$this->config['file_extension'];
            }
            if (!file_exists($file_path)) {
                $file_path = __DIR__.'/backend/templates/404.md';
            }
        }
        return $file_path;
    }

    /**
     * Outputs the templates and files
     * You can use file_get_contents($file_path) instead of require to disable running PHP code in .md files
     *
     * @param string $file_path full path to the Markdown file
     */
    protected function view($file_path)
    {
        require __DIR__.'/backend/templates/_header.php';
        ob_start();
        require $file_path;
        $this->html = ob_get_clean();
        $this->html = $this->event('view_after', $this);
        echo $this->html;
        require __DIR__.'/backend/templates/_footer.php';
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
            call_user_func_array([ $class_name, 'run'], [$this] );
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
    public function event($event, $value = NULL, $callback = NULL)
    {
        // Adding or removing a callback?
        if ($callback !== NULL) {
            if ($callback) {
                $this->events[$event][] = $callback;
            } else {
                unset($this->events[$event]);
            }
        } elseif (isset($this->events[$event])) { // Fire a callback
            foreach($this->events[$event] as $function) {
                $value = call_user_func($function, $value);
            }
            return $value;
        }
    }
}

$PicoWiki = new PicoWiki($config);
$PicoWiki->run(@$_GET['url']);
