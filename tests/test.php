<?php

define('DS', DIRECTORY_SEPARATOR);

require_once "../vendor/autoload.php";

use PHPClio\Console as Console;
use I18nExtractor\Extractor as Extractor;

$ex = new Extractor();

/**
 * Allow directories passed in via arguments
 */
if(count($argv) > 1) {
    for($i = 1; $i < count($argv); $i++) {

        if(substr($argv[$i], 0, 1) != '/') {
            $argv[$i] = __DIR__ . DS . $argv[$i];
        }

        $ex->add($argv[$i]);

    }
}

while(true) {

    if(count($ex->files)) {
        Console::out("Files to be parsed:");
        print_r($ex->files);
    }

    $in = Console::in('What Directory or File would you like to extract PO data from? (leave blank to continue)');

    if(strlen($in) == 0) {
        break;
    }

    if(substr($in, 0, 1) != '/') {
        $in = __DIR__ . DS . $in;
    }

    try{
        $ex->add($in);
    } catch (\Exception $e) {
        Console::out($e->getMessage());
    }
    
}

$ex->tokenize();

file_put_contents('extract.pot', $ex->stringsAsPOT());