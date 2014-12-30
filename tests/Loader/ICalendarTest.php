<?php
namespace FluentDOM\ContentLines\Loader {

  use FluentDOM\TestCase;

  require_once(__DIR__.'/../../vendor/autoload.php');

  class ICalendarTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers FluentDOM\ContentLines\Loader\ICalendar
     */
    public function testSupportsExpectingTrue() {
      $loader = new ICalendar();
      $this->assertTrue($loader->supports('text/calendar'));
    }

    /**
     * @covers FluentDOM\ContentLines\Loader\ICalendar
     */
    public function testSupportsExpectingFalse() {
      $loader = new ICalendar();
      $this->assertFalse($loader->supports('text/html'));
    }

    /**
     * @covers FluentDOM\ContentLines\Loader\ICalendar
     */
    public function testLoadRfc6321ExampleOne() {
      $loader = new ICalendar();
      $this->assertXmlStringEqualsXmlFile(
        __DIR__.'/TestData/ical-rfc6321-example-1.xml',
        $loader->load(
          __DIR__.'/TestData/ical-rfc6321-example-1.ics',
          'text/calendar'
        )->saveXML()
      );
    }

    /**
     * @covers FluentDOM\ContentLines\Loader\ICalendar
     */
    public function testLoadRfc6321ExampleTwo() {
      $loader = new ICalendar();
      $this->assertXmlStringEqualsXmlFile(
        __DIR__.'/TestData/ical-rfc6321-example-2.xml',
        $loader->load(
          __DIR__.'/TestData/ical-rfc6321-example-2.ics',
          'text/calendar'
        )->saveXML()
      );
    }

    /**
     * @covers FluentDOM\ContentLines\Loader\ICalendar
     */
    public function testLoadWithInvalidSourceExpectingNull() {
      $loader = new ICalendar();
      $this->assertNull(
        $loader->load(FALSE, 'text/calendar')
      );
    }
  }
}