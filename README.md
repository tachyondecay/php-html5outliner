# HTML5Outliner

PHP implementation of the [HTML5 outline algorithm](http://www.w3.org/html/wg/drafts/html/master/sections.html#outlines). Credit to [HTMLOutliner.js](https://github.com/hoyois/html5outliner) for providing a good example of how the algorithm can be implemented.

I wanted to automatically generate a table of contents based on the output of Markdown-formatted documents. I couldn't find an existing version in PHP, so I rolled my own. You can check out a demo at: http://tachyondecay.net/php/bin/html5outliner/

The `Outline` class contains the logic for generating the actual outline. It uses `Section` and `Heading` objects to represent sections (in the content sense, not the HTML `section` element) and headings for those sections.

## Examples

HTML5Outliner works with PHP's `DOMDocument` class to construct an outline. You can pass either an element of a `DOMDocument` or a string that HTML5Outliner can then turn into a `DOMDocument`:

```php
// Pass an HTML string
$outline = HTML5Outliner\Outline::loadHTML($html);

// Pass a DOMDocument
$outline = HTML5Outliner\Outline::build($document->documentElement);
```

For a complete, working example, refer to `index.php`.

## Caveats

I have tested this against the examples given in the spec and fixed most of the glaring problems. However, `DOMDocument` does some interesting things when fed HTML that lacks `<html>` or `<body>` elements; in those cases, HTML5Outliner might produce unexpected output.

I'm open to pull requests if you make any improvements.