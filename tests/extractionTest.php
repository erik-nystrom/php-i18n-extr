<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use I18nExtractor\Extractor;

final class ExtractionText extends TestCase
{

    public function testKeysExtracted() {

        $ex = new Extractor();
        $ex->add(__DIR__ . '/sample-files/sample.html');
        $ex->add(__DIR__ . '/sample-files/sample.php');
        $ex->add(__DIR__ . '/sample-files/sample.js');

        $ex->tokenize();

        $strings = $ex->getStrings();
        
        $this->assertArrayHasKey('This is a page title', $strings);
        $this->assertArrayHasKey('Lorem ipsum dolor sit amet consectetur adipisicing elit. Cupiditate veniam recusandae culpa id, a iure fuga fugiat praesentium pariatur iusto nulla aliquam debitis nesciunt cum atque quam! Nihil, ducimus enim?', $strings);
        $this->assertArrayHasKey('Lorem, ipsum dolor sit amet consectetur adipisicing elit. Eum tenetur, mollitia non debitis culpa perspiciatis repellat officia quam est voluptatibus vero ipsum veritatis incidunt quisquam accusamus saepe? Illo, incidunt excepturi.', $strings);
        $this->assertArrayHasKey('some image alt text', $strings);
        $this->assertArrayHasKey('this is some sample text', $strings);
        $this->assertArrayHasKey('this is some more sample text', $strings);
        $this->assertArrayHasKey('this is yet some more sample text', $strings);
        $this->assertArrayHasKey('This is some text.', $strings);
        $this->assertArrayHasKey('this is also some text', $strings);
        
        $this->assertArrayNotHasKey('Sample text that should be ignored', $strings);

    }

    public function testKeysExtractedDoubleUnderscore() {

        $ex = new Extractor('__');
        $ex->add(__DIR__ . '/sample-files/sample.html');
        $ex->add(__DIR__ . '/sample-files/sample.php');
        $ex->add(__DIR__ . '/sample-files/sample.js');

        $ex->tokenize();

        $strings = $ex->getStrings();
        
        $this->assertArrayNotHasKey('This is a page title', $strings);
        $this->assertArrayNotHasKey('Lorem ipsum dolor sit amet consectetur adipisicing elit. Cupiditate veniam recusandae culpa id, a iure fuga fugiat praesentium pariatur iusto nulla aliquam debitis nesciunt cum atque quam! Nihil, ducimus enim?', $strings);
        $this->assertArrayNotHasKey('Lorem, ipsum dolor sit amet consectetur adipisicing elit. Eum tenetur, mollitia non debitis culpa perspiciatis repellat officia quam est voluptatibus vero ipsum veritatis incidunt quisquam accusamus saepe? Illo, incidunt excepturi.', $strings);
        $this->assertArrayNotHasKey('some image alt text', $strings);
        $this->assertArrayNotHasKey('this is some sample text', $strings);
        $this->assertArrayNotHasKey('this is some more sample text', $strings);
        $this->assertArrayNotHasKey('this is yet some more sample text', $strings);
        $this->assertArrayNotHasKey('This is some text.', $strings);
        $this->assertArrayNotHasKey('this is also some text', $strings);
        
        $this->assertArrayHasKey('Sample text that should be ignored', $strings);

    }

}