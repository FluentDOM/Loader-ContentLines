<?php
namespace FluentDOM\ContentLines\Loader {

  use FluentDOM\Loader\Supports;
  use FluentDOM\TestCase;

  require_once(__DIR__.'/../../vendor/autoload.php');

  class ContentLinesTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers FluentDOM\ContentLines\Loader\ContentLines
     * @dataProvider provideContentLines
     */
    public function testLoad($expectedXml, $textInput) {
      $loader = new ContentLines_TestProxy();
      $this->assertXmlStringEqualsXmlString(
        $expectedXml,
        $loader->load($textInput, 'text/content-lines')->saveXML()
      );
    }

    public static function provideContentLines() {
      return [
        [
          '<?xml version="1.0"?>
            <data xmlns="urn:content-lines">
              <vcalendar/>
            </data>',
          "BEGIN:VCALENDAR\r\nEND\r\n"
        ]
      ];
    }
  }

  class ContentLines_TestProxy extends ContentLines {

    use Supports;

    public function getSupported() {
      return ['text/content-lines'];
    }
  }
}