# PicoWiki

**_PicoWiki is a super tiny and simple file-based Wiki system._**


## Features

- **Markdown** Formatting, links, etc.
- **Install in 2 seconds** Just place a folder in your server
- **File-based** Easily editable
- **Extensible** Less than 100 lines of code
- **Fast** Uses very low bandwidth
- **Powerful** You can use PHP code anywhere


## Setup

See [Setup](setup) for instructions.


## Plugins

Plugins use an Event Hooks to attach new features and alter functionality on the run, a new plugin must have a `run()` method that will be executed whenever you specify. Check out `/backend/plugins/` to find examples.


### Hooks

- `init`: Initialized the PicoWiki Class, just before loading `$config`
- `config_loaded`: Config has been loaded
- `plugins_loaded`: Plugins have been loaded
- `run_init`: `$PicoWiki->run()` has been called
- `list_loaded`: File list has been loaded
- `view_after`: Just before outputting the page


## License & Contact

&copy; <?=date('Y')?> [Xavi Esteve](https://xaviesteve.com/). Licensed under [MIT](https://opensource.org/licenses/MIT).

Parsedown by Emanuil Rusev also licensed under a MIT License.

<p style="text-align: center"><img src="<?=$this->config['app_url']?>static/picowiki-icon.png" style="height:125px;width:125px"></p>
