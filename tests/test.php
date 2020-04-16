<?php

define('DS', DIRECTORY_SEPARATOR);

require_once "../vendor/autoload.php";

use I18nExtractor\Extractor as Extractor;

$ex = new Extractor();

$ex->add('sample-files/sample.html');
$ex->add('sample-files/sample.php');
$ex->add('sample-files/sample.js');

$ex->tokenize();

file_put_contents('extract.pot', $ex->stringsAsPOT());