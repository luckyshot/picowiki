# <img src="static/picowiki-favicon.png" alt=""> PicoWiki

**_PicoWiki is a super tiny and simple file-based Wiki system._**

<p style="text-align: center"><img src="static/screenshot.jpg" alt="Screenshot of the main page of PicoWiki"></p>


## Features

- **Markdown** Formatting, links, etc.
- **Install in 2 seconds** Just place a folder in your server
- **File-based** Easily editable
- **Tiny** Main code has less than 100 lines
- **Extensible** via Plugins
- **Fast** Uses very low bandwidth
- **Powerful** You can use PHP code anywhere


## Setup

See [Setup](files/setup.md) for instructions.


## Plugins

Plugins use Event Hooks to attach new features and alter functionality on the run, a new plugin must have a `run()` method that will be executed whenever you specify. Check out `/backend/plugins/` to find available plugins.

To disable a plugin, simply move it away from the `plugins` folder (i.e. in a subfolder such as `plugins/deactivated`).


### Hooks

- `init`: Initialized the PicoWiki Class, just before loading `$config`
- `config_loaded`: Configuration loaded
- `plugins_loaded`: Plugins loaded
- `run_init`: Initialized `run()` method
- `url_loaded`: URL parsed
- `list_loaded`: File list loaded
- `template_header`: Add HTML code before the closing `</header>` HTML tag
- `view_after`: The file view has been loaded, just before echoing it
- `template_footer`: Add HTML code before the closing `</body>` HTML tag


## Requirements

- PHP 5.4 or above


## License & Contact

&copy; 2018-2019 [Xavi Esteve](https://xaviesteve.com/). Licensed under [MIT](https://opensource.org/licenses/MIT).

Parsedown by Emanuil Rusev also licensed under a MIT License.

Some plugins made by their respective authors.


## Contributing

PicoWiki is a single PHP class with 7 methods, all in less than 100 lines of code, ready to be extended. New features I can think of right (through plugins) now are:

- Report for checking for broken links (links to pages that don't exist yet), Orphan pages, etc.
- Code snippets to load YouTube videos or Google Maps, etc.
- Web-based file editor to edit files directly via browser
- Themes
- Auto-translate
- Sitemap generator

If you'd like to **contribute** please do, I am quite active on Github and usually merge Pull Requests in a few hours/days. Any code submitted will follow the same license as PicoWiki.

It's easy to contribute! When I say PicoWiki is tiny I mean it 😊 The whole app is just this code:

<img src="static/screenshot-code.jpg" alt="Screenshot of the code of PicoWiki where you can see that it is less than 100 lines of code">
