---
title: Backlog
tags: development, php
---

[toc]


## Navigation

- nav
  - home
  - file-list | all-files list
  - tag-cloud [all files|current context]
  - search-text [allfiles|current context]
  - recent
- actions
  - view source
  - attach file
    - [select file]
    - [go]
    - goto attached
  - new file
    - [input name]
    - [select extension]
    - [go]
    - go to the "non-existing" location andget 404 and create file.
  - tool menu
    - delete
    - rename
    - move
- COOKIE contains the search|select settings
- GET queries are used to modify the cookie
- tags: GET to add or remove tags from the selection cookie
- normal view: only files in the current directory
  - if current file <path>/article.md
  - nav list view:
    - <home> | bread-crumbs-path
    - list of folders under <path>
    - list of files under <path>
    - if <path>/article is a directory, show an attachments link

- Can the default handler make a plugin?

## Markdown text diagrams

- blockdiag
  - http://blockdiag.com/en/
- [x] aafigure (or equivalent)
  - https://git.alpinelinux.org/aports/tree/testing/svgbob/APKBUILD
  - https://pkgs.alpinelinux.org/package/edge/testing/x86_64/svgbob
  - https://dl-cdn.alpinelinux.org/alpine/edge/testing/x86_64/svgbob-0.6.6-r0.apk
  - https://github.com/ivanceras/svgbob
- [x] graphviz : as an input filter
  - apk add graphviz font-bitstream-type1 ghostscript-fonts
  - [ ] fix png generation fonts
- update container config


## new media types

- source code
  - only view source in codemirror
  - read meta data from comments (start-of-header, line-comment, end-of-header)
  - 404 handler: create new file
- directory handler
  - delete, rename, move files

## others

- user authentication
  - https://www.devdungeon.com/content/http-basic-authentication-php
- http daemon authentication
  - https://httpd.apache.org/docs/2.4/howto/auth.html
- add front-matter-yaml support
  - md : when saving, check yaml
  - getRemoteUser
      - http user?
      - remote IP
  - if file does not exist
  - created: <date> <remote-user>
  - updated-by: <remote-user>
  - if (log in meta/yaml) {
    make log empty
    change-log: <date> <remote-user> <log-msg>
  - [x] auto-meta-data: date
- tagging
  - [ ] auto-tagging: based on words and tagcloud
  - tag from git
- Report for checking for broken links (links to pages that don't exist yet), Orphan pages, etc.
- Code snippets to load YouTube videos or Google Maps, etc.
- Themes
- Sitemap generator
