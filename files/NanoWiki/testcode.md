---
title: Test Code
---
[toc]

# Other plugins

## wiki links

- [[NanoWiki]]
- [[NanoWiki/testcode.md target="_blank"|testcode in a new window]]
- [[]] shouldn't work
- {{NanoWiki/static/picowiki-favicon.png}}
- {{NanoWiki/static/picowiki-favicon.png width=32 height=32}}
- [[not-found.md]]
- https://www.php.net/manual/en/functions.arguments.php
- {{NanoWiki/static/picowiki-favicon.png|PicoWiki icon}}

## Emojis

- (y)
- (n)
- :rocket:

## Vars

- $meta.file-path$
- $config.app_url$
- $url$
- $tags$
- $meta.file-tags$

***

$attachments$


# Markdown tests

## Simple markups

- ~~Strike through~~
- [x] or [x]
- [ ] or [ ]
- [x] ~~strike-through~~ (del)
- [ ] ++insert++ (ins)
- [ ] ^^superscript^^ (sup)
- [ ] ,,subscript,, (sub)

## Tables

| table | is table |
|---|--|
| ABC | BCA |
| 1 | 2 |


| >     | >           |   Colspan    | >           | for thead |
| ----- | :---------: | -----------: | ----------- | --------- |
| Lorem | ipsum       |    dolor     | sit         | amet      |
| ^     | -           |      >       | right align | .         |
| ,     | >           | center align | >           | 2x2 cell  |
| >     | another 2x2 |      +       | >           | ^         |
| >     | ^           |              |             | !         |

| Item      | Value |
| --------- | -----:|
| Computer  | $1600 |
| Phone     |   $12 |
| Pipe      |    $1 |

| Function name | Description                    |
| ------------- | ------------------------------ |
| `help()`      | Display the help window.       |
| `destroy()`   | **Destroy your computer!**     |


## lists

* [ ] item 1 [ ] or [x]
* [x] item 2
* [ ] item 3
* no match
* test line break \
  like this
* but not like
  that

1. one
2. two
3. three

* item
  more items
* item
  - item
  - item
    - item
    - item
* item
* item
- item
  1. abc
  2. cjd

***

* [ ] item
  more items
* [ ] item
  - [x] item
  - [ ] item
    - [ ] item
    - [x] item
* item
* item
- item
  1. abc
  2. cjd
  10. dkf

## Block Quotes

> block quotes
> are here.

## Emojis

Gone camping! :tent: Be back soon.

That is so funny! :joy:

## Fenced code blocks


```php
    protected function blockListComplete(array $Block)
    {
        if (isset($Block['loose']))
        {
            foreach ($Block['element']['text'] as &$li)
            {
                if (end($li['text']) !== '')
                {
                    $li['text'] []= '';
                }
            }
        }

        return $Block;
    }

```

## Graphviz test


``` graphviz-dot
  graph NET {
    layout=neato

    edge [weight=2.0 fontsize=7]
    node [style=filled shape=box]

    node [fillcolor=white] kpnmodem
    node [fillcolor=lightblue] ngs1 ngs2 ngs3
    node [fillcolor=lightgreen] cctv_sw
    node [fillcolor=silver] cn4 iptv1 veraedge1 nd2 nd3 philtv
    node [fillcolor=yellow] wac1 wac2 wac3 owap1

    kpnmodem -- cn4 [label="v2" taillabel="p1" headlabel="p2"]
    kpnmodem -- iptv1 [label="v2 (p#7)" taillabel="p2"]
    kpnmodem -- veraedge1 [label="v2" taillabel="p4"]

    ngs1 -- cn4 [label="v1,3" taillabel="p10" headlabel="p0"]
    ngs1 -- ngs2 [label="v1,3 (p#4)" taillabel="p1" headlabel="p1"]
    ngs1 -- ngs3 [label="v1,3" taillabel="p8,9" headlabel="p1,8" penwidth=2.0]
    ngs1 -- cctv_sw [label="v3" taillabel="p7" headlabel="p5"]

    ngs3 -- nd2 [label="v1,3" taillabel="p7"]
    ngs3 -- nd3 [label="v1,3" taillabel="p8"]

    cctv_sw -- ipcam1 [label="v3" taillabel="p1"]
    cctv_sw -- ipcam2 [label="v3 (p#1)" taillabel="p2"]
    cctv_sw -- ipcam3 [label="v3 (p#5)" taillabel="p3"]
  }
```

## svgbob

``` lineart
o->  Quick logo scribbles
        .---.                      _
       /-o-/--       .--.         |-|               .--.
    .-/ / /->       /--. \     .--)-|    .--.-.    //.-.\
   ( *  \/         / O  )|     |  |-|    |->| |   (+(-*-))
    '-.  \        /\ |-//      .  * |    '--'-'    \\'-'/
       \ /        \ '+'/        \__/                '--'
        '          '--'
                                           _____
       .----.               _             /   __)\
       |    |           ,--(_)            |  /  \ \
     __|____|__       _/ .-. \         ___|  |__/ /
    |  ______--|     (_)(   ) )       / (_    _)_/
    `-/.::::.\-'       \ `-'_/       / /  |  |
     '--------'         `--(_)       \ \__/  |
                                      \(_____/
```

# headown

# h1

#++

# h2

#--

# h1

# includes

including index.md

$include: NanoWiki/index.md $

including non-existing file

$include: not-found.md $
