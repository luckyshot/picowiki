# <img src="static/picowiki-favicon.png" alt=""> PicoWiki

**_PicoWiki is a super tiny and simple file-based Wiki system._**

<p style="text-align: center"><img src="static/screenshot.jpg" alt="Screenshot of the main page of PicoWiki"></p>


## Features

- **Markdown** Formatting, links, etc.
- **Install in 2 seconds** Just place a folder in your server
- **File-based** Easily editable
- **Extensible** Less than 100 lines of code
- **Fast** Uses very low bandwidth
- **Powerful** You can use PHP code anywhere


## Setup

See [Setup](files/setup.md) for instructions.


## Plugins

Plugins use Event Hooks to attach new features and alter functionality on the run, a new plugin must have a `run()` method that will be executed whenever you specify. Check out `/backend/plugins/` to find available plugins.

To disable a plugin, simply move it away from the `plugins` folder (also in a subfolder such as `plugins/deactivated`).


### Hooks

- `init`: Initialized the PicoWiki Class, just before loading `$config`
- `config_loaded`: Config has been loaded
- `plugins_loaded`: Plugins have been loaded
- `run_init`: `$PicoWiki->run()` has been called
- `url_loaded`: URL has been detected
- `list_loaded`: File list has been loaded
- `template_header`: add stuff before the closing `header` HTML tag
- `view_after`: Just before outputting the page
- `template_footer`: add stuff before the closing `body` HTML tag

## License & Contact

&copy; <?=date('Y')?> [Xavi Esteve](https://xaviesteve.com/). Licensed under [MIT](https://opensource.org/licenses/MIT).

Parsedown by Emanuil Rusev also licensed under a MIT License.

## Contributing

PicoWiki is a single PHP class with 7 methods, all in less than 100 lines of code, ready to be extended. New features I can think of right (through plugins) now are:

- Report for checking for broken links (links to pages that don't exist yet), Orphan pages, etc.
- Code snippets to load YouTube videos or Google Maps, etc.
- Web-based file editor to edit files directly via browser
- Themes
- Auto-translate
- Sitemap generator

If you'd like to **contribute** please do, I am quite active on Github and usually merge Pull Requests in a few hours or days.

When I say it's tiny I mean this, the whole app is just this code:

<img src="static/screenshot-code.jpg" alt="Screenshot of the code of PicoWiki where you can see that it is less than 100 lines of code">
