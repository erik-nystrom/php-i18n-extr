# PHP i18n Extraction Utility

Extract translation tokens from .php, .html, and .js files.

## Usage

Install via Composer:
```
composer require erik-nystrom/php-i18n-extr
```

Alias/Import into your script, create an instance of Extractor, add some files, and extract:
```
use I18nExtractor\Extractor as Extractor;

$ex = new Extractor();

$ex->add("/path/to/your/website");
$ex->add("/path/to/another/script.php");
$ex->add("/path/to/another/script.js");

$ex->tokenize();

file_put_contents('default.pot', $ex->stringsAsPOT());
```

## License
[MIT](https://choosealicense.com/licenses/mit/)