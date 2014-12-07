<?php
/**
 * Load a iCalendar (*.ics) file
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright Copyright (c) 2009-2014 Bastian Feder, Thomas Weinert
 */

namespace FluentDOM\ContentLines\Loader {

  use FluentDOM\Loader\Supports;

  /**
   * Load a iCalendar (*.ics) file
   */
  class ICalendar extends ContentLines {

    use Supports;

    protected $_namespace = 'urn:ietf:params:xml:ns:icalendar-2.0';

    protected $_nodeNames = [
      'root' => 'icalendar',
      'default-component' => 'vcalendar',
      'properties' => 'properties',
      'components' => 'components',
      'parameters' => 'parameters',
      'default-type' => 'text'
    ];

    protected $_properties = [
      'CLASS' => 'PUBLIC',
      'CALSCALE' => NULL,
      'PRODID' => NULL,
      'REV' => NULL,
      'UID' => NULL,
      'VERSION' => NULL
    ];

    protected $_addPropertiesAsAttributes = FALSE;

    protected $_parameters = [
      'ALTREP' => 'text',
      'CN' => 'text',
      'CUTYPE' => 'text',
      'DELEGATED-FROM' => 'cal-address',
      'DELEGATED-TO' => 'cal-address',
      'DIR' => 'uri',
      'ENCODING' => 'text',
      'FMTTYPE' => 'text',
      'FBTYPE' => 'text',
      'LANGUAGE' => 'language-tag',
      'MEMBER' => 'cal-address',
      'PARTSTAT' => 'text',
      'RANGE' => 'text',
      'RELATED' => 'text',
      'RELTYPE' => 'text',
      'ROLE' => 'text',
      'RSVP' => 'boolean',
      'SENT-BY' => 'cal-address',
      'TZID' => 'text'
    ];

    protected $_components = [
      // General Properties
      'CALSCALE' => 'text',
      'METHOD' => 'text',
      'PRODID' => 'text',
      'VERSION' => 'text',
      'REQUEST-STATUS' => ['code', 'description', 'data'],
      // Descriptive Component Properties
      'ATTACH' => 'uri',
      'CATEGORIES' => 'text',
      'CLASS' => 'text',
      'COMMENT' => 'text',
      'DESCRIPTION' => 'text',
      'GEO' => ['latitude', 'longitude'],
      'LOCATION' => 'text',
      'PERCENT-COMPLETE' => 'integer',
      'PRIORITY' => 'integer',
      'RESOURCES' => 'text',
      'STATUS' => 'text',
      'SUMMARY' => 'text',
      'COMPLETED' => 'date-time',
      'DTEND' => 'date-time',
      'DUE' => 'date-time',
      'DTSTART' => 'date-time',
      'DURATION' => 'duration',
      'FREEBUSY' => 'period',
      'TRANSP' => 'text',
      'TZID' => 'text',
      'TZNAME' => 'text',
      'TZOFFSETFROM' => 'utc-offset',
      'TZOFFSETTO' => 'utc-offset',
      'TZURL' => 'uri',
      // Relationship Component Properties
      'ATTENDEE' => 'cal-address',
      'CONTACT' => 'text',
      'ORGANIZER' => 'cal-address',
      'RECURRENCE-ID' => 'date-time',
      'RELATED-TO' => 'text',
      'URL' => 'uri',
      'UID' => 'text',
      // Recurrence Component Properties
      'EXDATE' => 'date-time',
      'RDATE' => 'date-time',
      'RRULE' => ':assoc',
      // Alarm Component Properties
      'ACTION' => 'text',
      'REPEAT' => 'integer',
      'TRIGGER' => 'duration',
      // Change Management Component Properties
      'CREATED' => 'date-time',
      'DTSTAMP' => 'date-time',
      'LAST-MODIFIED' => 'date-time',
      'SEQUENCE' => 'integer'
    ];

    /**
     * @return string[]
     */
    public function getSupported() {
      return array('text/calendar');
    }
  }
}