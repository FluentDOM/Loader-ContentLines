<?php
/**
 * Load a iCalendar (*.ics) file
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @copyright Copyright (c) 2009-2014 Bastian Feder, Thomas Weinert
 */

namespace FluentDOM\ContentLines\Loader {

  use FluentDOM\Element;
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
      'COMPLETED' => 'date-time-or-date',
      'DTEND' => 'date-time-or-date',
      'DUE' => 'date-time-or-date',
      'DTSTART' => 'date-time-or-date',
      'DURATION' => 'duration',
      'FREEBUSY' => 'period',
      'TRANSP' => 'text',
      'TZID' => ':value',
      'TZNAME' => 'text',
      'TZOFFSETFROM' => 'utc-offset',
      'TZOFFSETTO' => 'utc-offset',
      'TZURL' => 'uri',
      // Relationship Component Properties
      'ATTENDEE' => 'cal-address',
      'CONTACT' => 'text',
      'ORGANIZER' => 'cal-address',
      'RECURRENCE-ID' => 'date-time-or-date',
      'RELATED-TO' => 'text',
      'URL' => 'uri',
      'UID' => 'text',
      // Recurrence Component Properties
      'EXDATE' => 'date-time-or-date',
      'RDATE' => 'date-time-or-date',
      'RRULE' => ':recur',
      // Alarm Component Properties
      'ACTION' => 'text',
      'REPEAT' => 'integer',
      'TRIGGER' => 'duration',
      // Change Management Component Properties
      'CREATED' => 'date-time-or-date',
      'DTSTAMP' => 'date-time-or-date',
      'LAST-MODIFIED' => 'date-time-or-date',
      'SEQUENCE' => 'integer'
    ];

    /**
     * @return string[]
     */
    public function getSupported() {
      return array('text/calendar');
    }

    protected function appendValueNode(Element $parent, $type, $values) {
      switch ($type) {
      case ':recur' :
        $tokenValues = $this->getValuesAsList($values);
        $parent = $parent->appendElement('recur');
        foreach ($tokenValues as $tokenName => $tokenValue) {
          $this->appendValueNode($parent, $tokenName, $tokenValue);
        }
        return;
      case 'period' :
        list($start, $duration) = explode('/', (string)$values);
        $parent = $parent->appendElement($type);
        if (preg_match(self::PATTERN_DATETIME, $start, $match)) {
          $parent->appendElement(
            'start',
            sprintf(
              '%s-%s-%sT%s:%s:%s%s',
              $match['year'],
              $match['month'],
              $match['day'],
              $match['hour'],
              $match['minute'],
              $match['second'],
              isset($match['offset']) ? $match['offset'] : ''
            )
          );
        }
        $parent->appendElement(
          'duration', $duration
        );
        return;
      }
      parent::appendValueNode($parent, $type, $values);
    }
  }
}