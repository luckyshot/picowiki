# PicoWiki

**_PicoWiki is a super tiny and simple file-based Wiki system._**


## Features

- **Markdown** Formatting, links, etc.
- **Install in 2 seconds** Just place a folder in your server
- **File-based** Easily editable
- **Extensible** Less than 100 lines of code
- **Fast** Uses very low bandwidth
- **Powerful** You can use PHP code anywhere


## Available Plugins

<?php foreach( $this->plugin_list as $plugin ){ ?>
- <?=pathinfo($plugin)['filename']?>

<?php } ?>

## Setup

See [Setup](setup) for instructions and check the [full documentation](https://github.com/luckyshot/picowiki#readme) to learn about plugins and more.

<p style="text-align: center"><img src="<?=$this->config['app_url']?>static/picowiki-icon.png" style="height:125px;width:125px"></p>
