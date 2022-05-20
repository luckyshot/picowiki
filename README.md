# <img src="static/picowiki-favicon.png" alt=""> PicoWiki

**_PicoWiki is a ~~super~~ tiny and simple file-based Wiki system._**

<p style="text-align: center"><img src="static/screenshot.jpg" alt="Screenshot of the main page of PicoWiki"></p>


# Features

- ~~**Markdown** Formatting, links, etc.~~
- **Install in 2 seconds** Just place a folder in your server
- **File-based** Easily editable
- ~~**Tiny** Main code has less than 100 lines~~
- **Extensible** via Plugins
- ~~**Fast** Uses very low bandwidth~~
- ~~**Powerful** You can use PHP code anywhere~~


## Setup

See [Setup](files/setup.md) for instructions.


## Plugins

Plugins are used to implement event hooks and media handlers.

Event Hooks are used to attach new features and alter functionality
on the run, a new plugin must have a `load()` method that will be
executed whenever you specify. Check out `/backend/plugins/` to
find available plugins.

To disable a plugin, simply move it away from the `plugins` folder
(i.e. in a subfolder such as `plugins/deactivated`).

### Hooks

- `plugins_loaded`: Plugins loaded
- `run_init`: Initialized `run()` method
- `url_loaded`: URL parsed
- `list_loaded`: File list loaded
- `template_header`: Add HTML code before the closing `</header>` HTML tag
- `view_after`: The file view has been loaded, just before echoing it
- `template_footer`: Add HTML code before the closing `</body>` HTML tag

### Deprecated hooks

These hooks are deprecated because I don't think they can be hooked by
plugins at all.

- `init`: Initialized the PicoWiki Class, just before loading `$config`
- `config_loaded`: Configuration loaded

### Additional hooks

- `error404`: File not found
- `view_before`: The file view before being processed by the renderer
- `meta_read_after`: After file meta data and YAML front matter has been read
- `write_access_error`: handles when the user wants to write to a write-protected URL
- `read_access_error`: handles when the user wants to access to a read-protected URL
- `check_readable`: check if user has read access
- `check_writeable`: check if user has read access
- `payload_pre`: pre-process payload before saving
- `meta_write_before`: modify meta data before payload generation
- `payload_post`: post-process payload before saving


## Requirements

- PHP 7.4.0 or above
- svgbob : line-art
- graphviz : code diag

### PHP Extensions

- fileinfo
- pecl-yaml
- dom
- json

## Plugins

### PluginMarkDown

- Uses [MirrorMark](https://github.com/musicbed/mirrormark) for editing.
- Markdown Extensions:
  - checkboxes in lists [x] and [ ] markup
  - table span. [See markup](https://github.com/KENNYSOFT/parsedown-tablespan)
  - `~~` ~~strike-through~~ (del)
  - `++` ++insert++ (ins)
  - `^^` ^^superscript^^ (sup)
  - `,,` ,,subscript,, (sub)
  - `==` ==keyboard== (kbd)
  - "\\" at the end of the line to generate a line break
  - headown
    - header html tags in the content start at H2 (since H1 is used
      by the wiki's document title.
    - `#++` and `#--` is used to increment headown level.  (Use this in
      combination with file includes.
  - diagrams in fenced code blocks.
    - Adding to a fenced code block a tag such as:
      - graphviz-dot
      - graphviz-neato
      - graphviz-fdp
      - graphviz-sfdp
      - graphviz-twopi
      - graphviz-circo
      - lineart : parsed using [svgbob](https://github.com/ivanceras/svgbob)
    - This will render the given code as a SVG.
  - Markdown libraries:
    - [Parsedown](https://github.com/erusev/parsedown)
    - [PardownExtra](https://github.com/erusev/parsedown-extra)
    - `[toc]` tag implemented using [TOC](https://github.com/KEINOS/parsedown-extension_table-of-contents/)
  - syntax highlighting with tags in fenced code blocks using
    [hihglight.js](https://highlightjs.org/).

### PluginHTML

This plugin is used to handle HTML files.  Implements a media handler
interface.

### PluginIncludes

This plugin can be used to include files into a document before
rendering.

In a new line use: `$include: file $` to include a file.  Note that
all files are relative to `config[file_path]`.

### PluginVars

This plugin is used to create text substituions.  There are two
sets of substitutions.  Substitutions done **before**
and **after** rendering.

- Before rendering:
  - `$ urls$`: Current url
  - `$config.key$`: values in the `config` table.  You can define
     additional variables by adding them to `config.yaml`.
  - `$meta.key$` : meta values from the current document.
- After rendering:
  - `$ plugins$` an unordered HTML list containing loaded plugins.
  - `$ attachments$` an unordered HTML list containg links to
    the current document's attachments.

### PluginWikiLinks

Simplified markup for internal links.  It supports:

- hypertext links
  - `[[` : opening
  - __url-path__ : relative to `config[file_path]`.
  - ==space== followed by html attribute tags (if any, can be omitted)
  - `|` followed by the link text if not specified, defaults to the
    __url-path__.
  - `]]` : closing
- img tags
  - `{{` : opening
  - __url-path__ : relative to `config[file_path]`.
  - ==space== followed by html attribute tags (if any, can be omitted)
  - `|` followed by the `alt` and `title` text.  Defaults to
    __url-path__.
  - `}}` : closing

### PluginEmoji

Simple plugin to add Emoji rendering.

## License & Contact

&copy; 2018-2019 [Xavi Esteve](https://xaviesteve.com/). Licensed under [MIT](https://opensource.org/licenses/MIT).

Parsedown by Emanuil Rusev also licensed under a MIT License.

Some plugins made by their respective authors.

## Contributing

~~PicoWiki is a single PHP class with 7 methods, all in less than 100 lines of code, ready to be extended. New features I can think of right (through plugins) now are:~~

PicoWiki is a single PHP class, ready to be extended. New features I
can think of right (through plugins) now are:

- Report for checking for broken links (links to pages that don't exist yet), Orphan pages, etc.
- Code snippets to load YouTube videos or Google Maps, etc.
- [x] Web-based file editor to edit files directly via browser
- Themes
- Auto-translate
- Sitemap generator

If you'd like to **contribute** please do, I am quite active on Github and usually merge Pull Requests in a few hours/days. Any code submitted will follow the same license as PicoWiki.

It's easy to contribute! When I say PicoWiki is tiny I mean it 😊 The whole app is just this code:

<img src="static/screenshot-code.jpg" alt="Screenshot of the code of PicoWiki where you can see that it is less than 100 lines of code">
