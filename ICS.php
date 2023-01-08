<?php

namespace ICSGen;

// @see https://gist.github.com/jakebellacera/635416
// This version includes improvements:
// - added support for multiple events (ICSEvent class)
// - added support for DateTime objects
// - added line break conversion for description (literal \n)
// - added line length limit (75 characters)
// - respects DateTimeZone of DateTime
// - ability to set timezone for string dates (defaults to system timezone)
// - all dates will be converted to UTC (Z timestamp)
// Output can be validated here: https://icalendar.org/validator.html

/**
 * This is free and unencumbered software released into the public domain.
 * 
 * Anyone is free to copy, modify, publish, use, compile, sell, or
 * distribute this software, either in source code form or as a compiled
 * binary, for any purpose, commercial or non-commercial, and by any
 * means.
 * 
 * In jurisdictions that recognize copyright laws, the author or authors
 * of this software dedicate any and all copyright interest in the
 * software to the public domain. We make this dedication for the benefit
 * of the public at large and to the detriment of our heirs and
 * successors. We intend this dedication to be an overt act of
 * relinquishment in perpetuity of all present and future rights to this
 * software under copyright law.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
 * OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 * 
 * For more information, please refer to <http://unlicense.org>
 * 
 * ICS.php
 * =============================================================================
 * Use this class to create an .ics file.
 * 
 *
 * Usage
 * -----------------------------------------------------------------------------
 * Basic usage - generate ics file contents (see below for available properties):
 *   $ics = new ICS($props);
 *   $ics_file_contents = $ics->to_string();
 *
 * Setting properties after instantiation
 *   $ics = new ICS();
 *   $ics->set('summary', 'My awesome event');
 *
 * You can also set multiple properties at the same time by using an array:
 *   $ics->set(array(
 *     'dtstart' => 'now + 30 minutes',
 *     'dtend' => 'now + 1 hour'
 *   ));
 *
 * Available properties
 * -----------------------------------------------------------------------------
 * description
 *   String description of the event.
 * dtend
 *   A date/time stamp designating the end of the event. You can use either a
 *   DateTime object or a PHP datetime format string (e.g. "now + 1 hour").
 * dtstart
 *   A date/time stamp designating the start of the event. You can use either a
 *   DateTime object or a PHP datetime format string (e.g. "now + 1 hour").
 * location
 *   String address or description of the location of the event.
 * summary
 *   String short summary of the event - usually used as the title.
 * url
 *   A url to attach to the the event. Make sure to add the protocol (http://
 *   or https://).
 */

class ICS {
  public $events;

  public function __construct($events) {
    $this->events = array();

    foreach ($events as $event) {
      $this->events[] = new ICSEvent($event);
    }
  }

  public function to_string() {
    $rows = $this->build_ics();
    return implode("\r\n", $rows);
  }

  private function build_ics() {
    // ICS header
    $ics_out = array(
      'BEGIN:VCALENDAR',
      'VERSION:2.0',
      'PRODID:-//hacksw/handcal//NONSGML v1.0//EN',
      'CALSCALE:GREGORIAN',
    );

    // ICS events
    foreach ($this->events as $event) {
      $event_out = $event->build_ics();
      $ics_out = array_merge($ics_out, $event_out);
    }

    // ICS footer
    $ics_out[] = 'END:VCALENDAR';

    return $ics_out;
  }
}


class ICSEvent {

  /**
   * Mapping known properties to sanitizers
   * Simplified version of the rfc5545 Value Data Types
   * @see https://datatracker.ietf.org/doc/html/rfc5545#section-3.6.1
   * @see https://datatracker.ietf.org/doc/html/rfc5545#section-8.3.4
   */
  const PROPERTIES = [
    'DESCRIPTION' => 'longtext',
    'DTSTAMP' => 'timestamp',
    'DTSTART' => 'timestamp',
    'DTEND' => 'timestamp',
    'DURATION' => 'text',
    'SUMMARY' => 'text',
    'LOCATION' => 'text',
    'UID' => 'text',
    'URL' => 'uri',

    'LAST-MODIFIED' => 'timestamp',
    'CREATED' => 'timestamp',
    'RECURRENCE-ID' => 'timestamp',
    'PRIORITY' => 'integer',
    'SEQUENCE' => 'integer',
    'CLASS' => 'text',
    'STATUS' => 'text',
    'TRANSP' => 'text',
    'RESOURCES' => 'rawtext',
    'RRULE' => 'rawtext',
    'GEO' => 'rawtext',
    'ORGANIZER' => 'email',
  ];

  const DT_FORMAT = 'Ymd\THis\Z';

  protected $timezone;
  protected $properties = array();

  public function __construct($event) {

    $this->timezone = isset($event['timezone']) ? $event['timezone'] : null;
    $this->set($event);
  }

  /**
   * @param array|string $key
   * @param DateTime|string $val
   */
  protected function set($key, $val = '') {

    if (is_array($key)) {
      foreach ($key as $k => $v) {
        $this->set($k, $v);
      }
      return;
    }

    $key = strtoupper($key);
    if ($val != '' && in_array($key, array_keys(ICSEvent::PROPERTIES))) {
      $this->properties[$key] = sanitize_val($val, $key);
    }
  }

  public function build_ics() {

    $ics_out = array(
      'BEGIN:VEVENT'
    );

    // defaults for required properties
    $props = array(
      'DTSTAMP' => format_timestamp('now', $this->timezone),
      'UID' => uniqid(),
    );

    foreach ($this->properties as $k => $v) {
      $propkey = format_property($k);
      $props[$propkey] = $v;
    }

    // Append properties
    foreach ($props as $k => $v) {
      $ics_out[] = "$k:$v";
    }

    $ics_out[] = 'END:VEVENT';

    return $ics_out;
  }
}


/**
 * @param DateTime|string $val
 * @param string $key
 */
function sanitize_val($val, string $key) {

  $type = ICSEvent::PROPERTIES[$key];

  switch ($type) {
    case 'timestamp':
      return format_timestamp($val);
    case 'longtext':
      // convert description line breaks to "\\n"
      // (the file actually has to contain the literal string \n)
      $val = preg_replace("/\r\n?|\n/", "\\n", $val);
      // limit line length to 75 chars
      return ical_split($key, $val);
    case 'email':
      return 'mailto:' . $val;
    case 'rawtext':
      return $val;
    default:
      return escape_string($val);
  }
}

/**
 * Some properties may have comma-seperated parameters
 */
function format_property($key) {

  $property = array($key);
  $type = ICSEvent::PROPERTIES[$key];

  switch ($type) {
    case 'url':
      $property[] = 'VALUE=URI';
      break;
  }

  return implode(';', $property);
}

/**
 * @param DateTime|string $dt
 * @param string|null $timezone
 */
function format_timestamp($dt, $timezone = null) {

  $dt = ($dt instanceof \DateTime) ? $dt : new \DateTime($dt, $timezone);
  $dt->setTimeZone(new \DateTimeZone('UTC'));
  return $dt->format(ICSEvent::DT_FORMAT);
}

function escape_string(string $str) {
  return preg_replace('/([\,;])/', '\\\$1', $str);
}


/**
 * @see https://gist.github.com/hugowetterberg/81747
 */
function ical_split(string $preamble, string $value) {
  $value = trim($value);
  $value = strip_tags($value);
  $value = preg_replace('/\n+/', ' ', $value);
  $value = preg_replace('/\s{2,}/', ' ', $value);

  $preamble_len = strlen($preamble);

  $lines = array();
  while (strlen($value) > (75 - $preamble_len)) {
    $space = (75 - $preamble_len);
    $mbcc = $space;
    while ($mbcc) {
      $line = mb_strcut($value, 0, $mbcc);
      $oct = strlen($line);
      if ($oct > $space) {
        $mbcc -= $oct - $space;
      } else {
        $lines[] = $line;
        $preamble_len = 1; // Still take the tab into account
        $value = mb_strcut($value, $mbcc);
        break;
      }
    }
  }
  if (!empty($value)) {
    $lines[] = $value;
  }

  return implode("\r\n\t", $lines);
}
