<?php

$config = [
    'app_name'			=> 'PicoWiki',
    'app_url'			=> null, // (auto-detected, although you can manually specify it if you need to)
    'file_path' 		=> __DIR__ . '/files', // no trailing slash
    'file_extension' 	=> 'md',
    'version'			=> '1.0.0',
];

class PicoWiki
{
    private $Parsedown = null; // Markdown library object
    private $config = null; // configuration variables
    public $file_list = []; // array of available files
    public $url = null;

    public function __construct($config)
    {
        $this->config = $config;
        if (!$this->config['app_url']) {
            $this->config['app_url'] = '//'.$_SERVER['HTTP_HOST'].str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
        }
    }

    public function run($url = null)
    {
        $this->url = preg_replace('/[^a-z0-9-\/]/', '', strtolower($url));

        $this->file_list = $this->listFiles();
        $file_path = $this->getFilePath($this->url);
        $this->view($file_path);
    }

    protected function listFiles()
    {
        $this->file_list = $this->readDirectory($this->config['file_path'].'/*.'.$this->config['file_extension']);

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

    protected function readDirectory($pattern)
    {
        $first_files = glob($pattern);
        foreach (glob(dirname($pattern).'/*') as $dir) {
            $first_files = array_merge($first_files, $this->readDirectory($dir.'/'.basename($pattern)));
        }
        return $first_files;
    }

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
    // NOTE: use file_get_contents($file_path) instead of require to disable PHP code in .md files
    protected function view($file_path)
    {
        require __DIR__.'/backend/templates/_header.php';
        ob_start();
        require $file_path;
        $html = ob_get_clean();
        echo $this->parseMarkdown($html);
        require __DIR__.'/backend/templates/_footer.php';
    }

    protected function parseMarkdown($string)
    {
        if (!$this->Parsedown) {
            require_once __DIR__ . '/backend/libs/Parsedown.php';
            $this->Parsedown = new Parsedown();
        }
        return $this->Parsedown->text($string);
    }
}

$PicoWiki = new PicoWiki($config);
$PicoWiki->run(@$_GET['url']);
