<?php

namespace I18nExtractor;

use Peast\Traverser;
use Peast\Peast;

/**
 * Extract translation tokens from PHP, HTML, and JavaScript files
 */

class Extractor {

    public $files = [];
    private $strings = [];
    private $method = false;
    private $extensions = [];

    /**
     * Tokenize and extract translation tokens from a PHP or HTML File
     * 
     * @param $code PHP code to be parsed (such as file_get_contents('check.php'))
     * @param $method the method to search for
     * @return array an array of strings found
     */
    public static function tokenizePHP($code, $method) {

        $found = [];

        $tokens = token_get_all($code);

        foreach($tokens as $i => $token) {

            if($i < 2) {
                continue;
            }

            // check for JS inside any PHP/HTML
            if(is_array($token) && array_key_exists(1, $token) && strpos($token[1], '<script') > -1) {
                $doc = \DomDocument::loadHTML($token[1]);
                $xpath = new \DOMXpath($doc);
                $scripts = $xpath->query('//script[not(@src)]');
                foreach($scripts as $script){
                    $foundJS = Self::tokenizeJS($script->textContent, $method);
                    foreach($foundJS as $tokenJS => $definitionsJS) {
                        foreach($definitionsJS['locations'] as &$location) {
                            // append the parent node's line number to the JS's line number
                            $location += $token[2];
                        }
                        if(array_key_exists($tokenJS, $found)) {
                            $found[$tokenJS]['locations']= array_merge($found[$key]['locations'], $definitionsJS['locations']);
                        } else {
                            $found[$tokenJS]= [
                                'term' => $definitionsJS['term'],
                                'definition' => $definitionsJS['definition'],
                                'locations' => $definitionsJS['locations']
                            ];
                        }
                    }
                }
            }

            $secondprev = $tokens[$i - 2];
            if(is_array($secondprev)) {
                $secondprev = $secondprev[1];
            }

            $prev = $tokens[$i - 1];
            if(is_array($prev)) {
                $prev = $prev[1];
            }

            if($secondprev == $method && $prev == '(') {

                $string = $token[1];
                if((substr($string, 0, 1) == "'" || substr($string, 0, 1) == "\"") && (substr($string, strlen($string) - 1, 1) == "'" || substr($string, strlen($string) - 1, 1) == "\"")){
                    $string = substr($string, 1, -1);
                }

                if(array_key_exists($token[1], $found)) {
                    $found[$token[1]]['locations'][]= $token[2];
                } else {
                    $found[$token[1]]= [
                        'term' => $string,
                        'definition' => $string,
                        'locations' => [$token[2]]
                    ];
                }

            }

        }

        return $found;

    }

    /**
     * Tokenize and extract translation tokens from a JavaScript File
     * 
     * @param $code JavaScript code to be parsed (such as file_get_contents('check.js'))
     * @param $method the method to search for 
     * @return array an array of strings found
     */
    public static function tokenizeJS($code, $method) {

        $found = [];
        $stop = false;

        $ast = Peast::latest($code, null)->parse();
        $traverser = new Traverser;
        $traverser->addFunction(function($node) use ($method, &$stop, &$found) {
            if($node->getType() == 'Identifier' && $node->getName() == $method) {
                $stop = true;
                return true;
            }
            if($stop) {

                $stop = false;
                
                if(array_key_exists($node->getValue(), $found)) {
                    $found[$node->getValue()]['locations'][]= $node->getLocation()->getStart()->getLine();
                } else {
                    $found[$node->getValue()]= [
                        'term' => $node->getValue(),
                        'definition' => $node->getValue(),
                        'locations' => [$node->getLocation()->getStart()->getLine()]
                    ];
                }

            }
        });

        $traverser->traverse($ast);

        return $found;

    }

    /**
     * Constructor
     * 
     * @param $method The method to search for during tokenization (defaults to gettext's _)
     * @param $extensions A list of extensions to check, and how to parse them ['extension' => '(php|js)']
     */
    public function __construct($method = '_', $extensions = ['php' => 'php', 'html' => 'php', 'js' => 'js']) {
        $this->method = $method;
        $this->extensions = $extensions;
    }

    /**
     * Recursively scan a directory for .php, .html, and .js files
     * 
     * @param $path the path to search
     * @return array $files an array of files found
     */
    private function scan($path) {

        $files = [];
        $scan = scandir($path);

        foreach($scan as $item) {
            
            if($item == '.' || $item == '..'){
                continue;
            }

            $file = $path . '/' . $item;
            
            if(is_dir($file)) {
                $files = array_merge($files, $this->scan($path . '/' . $item));
            } elseif(is_file($file)) {
                $ext = explode('.', $file);
                $ext = array_pop($ext);
                if(in_array($ext, array_keys($this->extensions))){
                    $files[]= $file;
                }
            }

        }

        return $files;

    }

    /**
     * Add a directory to search through
     * 
     * @param $path either a directory to scan for files or a link to an individual file
     * @return true if the path/file exists, otherwise throws an exception
     */
    public function add($path) {

        if(is_dir($path)) {
            $this->files = array_merge($this->files, $this->scan($path));
            return true;
        } elseif (is_file($path)){
            $this->files = array_merge($this->files, [$path]);
            return true;
        } else {
            throw new \Exception("{$path} is not a valid file or directory");
        }

    }

    /**
     * Tokenize any .php, .html, or .js files that were found.
     */
    public function tokenize() {
        
        foreach($this->files as $file) {

            $ext = explode('.', $file);
            $ext = array_pop($ext);
            $ext = strtolower($ext);

            if(!array_key_exists($ext, $this->extensions)) {
                continue;
            } 

            switch($this->extensions[$ext]) {
                case 'php':

                    $strings = Extractor::tokenizePHP(file_get_contents(realpath($file)), $this->method);

                    foreach($strings as $string) {
                        foreach($string['locations'] as &$location) {
                            $location = sprintf('%s:%d', realpath($file), $location);
                        }
                        if(array_key_exists($string['definition'], $this->strings)) {
                            $this->strings[$string['definition']]['locations'] = array_merge($this->strings[$string['definition']]['locations'], $string['locations']);
                        } else {
                            $this->strings[$string['definition']] = $string;
                        }
                    }

                break;
                case 'js':

                    $strings = Extractor::tokenizeJS(file_get_contents(realpath($file)), $this->method);

                    foreach($strings as $string) {
                        foreach($string['locations'] as &$location) {
                            $location = sprintf('%s:%d', realpath($file), $location);
                        }
                        if(array_key_exists($string['definition'], $this->strings)) {
                            $this->strings[$string['definition']]['locations'] = array_merge($this->strings[$string['definition']]['locations'], $string['locations']);
                        } else {
                            $this->strings[$string['definition']] = $string;
                        }
                    }

                break;
            }

        }

    }

    /**
     * Return the extracted strings, with all 
     */
    public function getStrings() {
        return $this->strings;
    }

    /**
     * Format the strings found as a valid .pot file
     * 
     * @return string valid .pot file contents
     */
    public function getStringsAsPOT() {

        ob_start();

        $pot = '';

        foreach($this->strings as $string) {

            $entry = <<<EOD
#: %s 
msgid "%s"
msgstr "%s"


EOD;

            $pot .= sprintf($entry, implode(', ', $string['locations']), str_replace('"', '\"', $string['term']), str_replace('"', '\"', $string['definition']));

        }

        return $pot;

    }

}