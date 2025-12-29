# ICSGenerator
The module can generate basic ICS calendar strings and files.

Add key-value arrays to the `events` property ([WireArray](https://processwire.com/api/ref/wire-array/)) for one or more events.

## Examples

### Basic example

```php
// get the module
$icsgen = wire()->modules->IcsGenerator;

// create a new event using date strings
$myEvent = [
    'summary' => 'Christmas 2033',
    'dtstart' => '2033-12-24 18:00',
    'dtend' => '2033-12-24 22:00',
    'location' => 'North pole',
    'url' => 'https://san.ta',
    'description' => 'Ho ho ho',
];

// add to events
$icsgen->events->add($myEvent);

// get the ICS string to work with it directly
$str = $icsgen->getString();
echo '<pre>'. $str .'</pre>';

// or get the path to a temporary .ics file
// (uses wire()->files->tempDir under the hood)
$path = $icsgen->getFile();
```

### Save ICS file to ProcessWire files field

Assuming you have a file fields named `ics_files` with output formatting *Multi-file array of items*:

```php
// ... continuing from the basic example
// You can optionally provide a filename in getFile:
$path = $icsgen->getFile('my-event.ics');
$page->of(false);
$page->ics_files->add($path);
$page->save('ics_files');

// output
echo '<a href="'. $page->ics_files->last()->url .'" download>Download ICS File</a>';
```

### Save ICS file to a server path

E.g. saving it to the page's assets/files folder:

```php
// ... continuing from the basic example
// prepare paths
$fileName = 'event.ics';
$filePath = $page->filesPath() . $fileName;
$fileUrl = $page->filesUrl() . $fileName;

// save file
wire()->files->filePutContents($filePath, $icsgen->getString());

// output
echo '<a href="'. $fileUrl .'" download>Download ICS File</a>';
```

### Send email with ICS file

```php
// ... continuing from the basic example
$mail = wireMail();
$mail->attachment($path, 'calendar.ics');
$mail->to($user->email);
$mail->subject('ICS Demo');
$mail->body('This is a ICS demo.');
$numSent = $mail->send();
```

### Multiple events

Since events is a [WireArray](https://processwire.com/api/ref/wire-array/), simply add multiple entries how it suits you best:

```php
// add multiple entries (arrays)
$icsgen->events->add($myEvent1);
$icsgen->events->add($myEvent2);

// ... or set all events at once
$icsgen->events->setArray([
    [
        'summary' => 'Christmas 2033',
        'dtstart' => '2033-12-24 18:00',
        'dtend' => '2033-12-24 22:00',
    ],
    [
        'summary' => 'Christmas 2034',
        'dtstart' => '2034-12-24 18:00',
        'dtend' => '2034-12-24 22:00',
    ],
]);
```

## Timestamps

* All timestamps are expected to be a PHP `DateTime` object or a string. 
* All timestamp strings will be passed to [DateTime constructor](https://www.php.net/manual/en/datetime.construct.php).
* All timestamp properties are: `dtstart`, `dtend`, `last-modified`, `created`, `recurrence-id`
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

It depends on the calendar client what properties will be used.

```php
// all supported properties
$icsgen->events->add([
    'uid' => 'custom-entry-id',
    'summary' => 'Main Event Title',
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
    // X-*: will be unsanitized! In ICS.php you can find functions to escape dates and strings.
    'X-anything': 'custom-value',
]);
```

## Timezones

By default, the server's php timezone will be used.

```php
// Set a `timezone` property that will be applied to all date strings:
$icsgen->events->add([
    'timezone' => new \DateTimeZone('Europe/Berlin'),
    'dtstart'  => 'now',
    'dtend'    => 'now + 60 minutes',
]);

// Alternatively, construct DateTime with DateTimeZone:
$icsgen->events->add([
    'dtstart'  => new \DateTime('2033-12-24 12:00', new \DateTimeZone('Asia/Dubai')),
    'dtend'    => new \DateTime('2033-12-24 12:00', new \DateTimeZone('Europe/Paris')),
]);

// `timezone` property will be ignored when string is a unix timestamp or contains a timezone:
$icsgen->events->add([
    'dtstart'  => '@946684800',
    'dtend'    => '2010-01-28T15:00:00+02:00',
]);
```

## Credits

Code is based on https://gist.github.com/jakebellacera/635416 with some improvements (see comments in ICS.php).
