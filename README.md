# PHP i18n Extraction Utility

Extract translation tokens from .php, .html, and .js files, and export in .pot file format.

## Why did I make this?

I wanted to roll my own translation system while still keeping things similar to PHP's gettext implementation.

This utility will also extract from .js files, which is an added bonus.

## Usage

Install via Composer:
```
composer require erik-nystrom/php-i18n-extr
```

Alias/Import into your script, create an instance of the Extractor, add some files, and tokenize:
```
use I18nExtractor\Extractor as Extractor;

$ex = new Extractor();

$ex->add("/path/to/your/website");
$ex->add("/path/to/another/script.php");
$ex->add("/path/to/another/script.js");

$ex->tokenize();

file_put_contents('default.pot', $ex->stringsAsPOT());
```

By default the Extractor class will look for `_()`, but you can change this if you'd like.

The resulting file should load fine in something like [poedit](https://poedit.net/).

## License
[MIT](https://choosealicense.com/licenses/mit/)