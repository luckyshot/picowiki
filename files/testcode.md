# Test code

[toc]

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

