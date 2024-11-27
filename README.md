# ICSGenerator
The module can generate basic ICS calendar strings and files for ProcessWire.

Add data to the `events` property ([WireArray](https://processwire.com/api/ref/wire-array/)) for one or more events.

## Links
* [Module Directory](https://processwire.com/modules/ics-generator/)
* [Support Forum](https://processwire.com/talk/topic/26817-ics-generator/)

## Basic example

```php
// get the module
$icsgen = wire()->modules->IcsGenerator;

// create a new event using date strings
// using WireData for easier manipulation later
$myEvent = new WireData([
    'summary' => 'Christmas 2033',
    'dtstart' => '2033-12-24 18:00',
    'dtend' => '2033-12-24 22:00',
    'location' => 'North pole',
    'url' => 'https://san.ta',
    'description' => 'Ho ho ho',
]);

// add to events
$icsgen->events->add($myEvent);

// get ICS string
$str = $icsgen->getString();

// get path to a temporary .ics file
// using wire()->files->tempDir
$path = $icsgen->getFile();

```

## Dates

* All dates are expected to be a PHP `DateTime` object or a string. 
* Date strings will be passed to [DateTime constructor](https://www.php.net/manual/en/datetime.construct.php).
* Final output will be converted to UTC (Z timestamp)

```php
// using DateTime
$icsgen->events->add([
    'dtstart' => new \DateTime('2033-12-24 12:00'),
    'dtend'   => new \DateTime('2033-12-24 14:00'),
    'summary' => 'Event title',
]);
```

## All properties

```php
// all supported properties
$icsgen->events->add([
    'uid' => 'custom-entry-id',
    'summary' => 'Event Title 2',
    'description' => 'This is a weekly meeting to discuss current projects and priorities. We will also review any new developments or updates. Please come prepared with any updates or questions you may have.',
    'dtstart' => '2033-12-24 14:00',
    'dtend' => '2033-12-24 16:00',
    // duration: string (ISO.8601.2004), alternative to dtend
    // 'duration' => 'PT2H', 
    'location' => 'Mount Everest',
    'url' => 'https://test.com',
    'rrule' => 'FREQ=DAILY;COUNT=10',
    'last-modified' => 'now',
    'created' => 'yesterday',
    'recurrence-id' => 'yesterday',
    'priority' => 1,
    'sequence' => 0,
    'class' => 'PUBLIC',
    'resources' => 'Projector,VCR',
    'organizer' => 'sam@example.com',
    // geo: LAT;LONG
    'geo' => '37.386013;-122.082932',
    // status: // "TENTATIVE", "CONFIRMED", "CANCELLED"
    'status' => 'CONFIRMED', 
    // transp: // "TRANSPARENT", "OPAQUE"
    'transp' => 'OPAQUE', 
    // X-*: will be unsanitized! There are helper functions to escape dates and strings.
    'X-anything': 'custom-value',
]);
```

## Mail example

```php
// send email with ics file
$mail = wireMail();
$mail->attachment($path, 'calendar.ics');
$mail->to($user->email);
$mail->subject('ICS Demo');
$mail->body('This is a ICS demo.');
$numSent = $mail->send();
```

## Timezones

By default, the server's php timezone will be used.

```php
// Set a `timezone` property, that will be applied to all date strings. 
$icsgen->events->add([
    'timezone' => new \DateTimeZone('Europe/Berlin'),
    'dtstart'  => 'now',
    'dtend'    => 'now + 60 minutes',
]);

// Alternatively, construct DateTime with DateTimeZone
$icsgen->events->add([
    'dtstart'  => new \DateTime('2033-12-24 12:00', new \DateTimeZone('Asia/Dubai')),
    'dtend'    => new \DateTime('2033-12-24 12:00', new \DateTimeZone('Europe/Paris')),
]);

// `timezone` property will be ignored when string is a unix timestamp or contains a timezone
$icsgen->events->add([
    'dtstart'  => '@946684800',
    'dtend'    => '2010-01-28T15:00:00+02:00',
]);
```

## Helper functions

Code is based on https://gist.github.com/jakebellacera/635416 with some improvements.

PRs are open.
