# PHP i18n Extraction Utility

Extract translation tokens from .php, .html, and .js files.

## Why did I make this?

I wanted to roll my own translation system while still keeping things similar to PHP's gettext implementation. Instead of `_()` I opted to use `__()` for my translation method, but you can use whatever you'd like. 

This utility will also extract from .js files, which is an added bonus.

## Usage

Install via Composer:
```
composer require erik-nystrom/php-i18n-extr
```

Alias/Import into your script, create an instance of the Extractor, add some files, and tokenize:
```
use I18nExtractor\Extractor as Extractor;

$ex = new Extractor('__');

$ex->add("/path/to/your/website");
$ex->add("/path/to/another/script.php");
$ex->add("/path/to/another/script.js");

$ex->tokenize();

file_put_contents('default.pot', $ex->stringsAsPOT());
```

By default the Extractor class will look for `_()`, but you can change this if you'd like.

## License
[MIT](https://choosealicense.com/licenses/mit/)