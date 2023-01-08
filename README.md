# ICSGenerator
The module can generate basic ICS calendar strings and files.

**This development branch is WIP!**

```php
// get the module
$icsgen = wire()->modules->IcsGenerator;

// add an event
$icsgen->events->add([
    'summary' => 'Event Title 1',
    'description' => 'Event body 1',
    'dtstart' =>'2033-12-24 12:00',
    'duration' => 'PT2H', // ISO.8601.2004
]);

// add another event
$icsgen->events->add([
    'uid' => 'custom-entry-id',
    'summary' => 'Event Title 2',
    'description' => 'This is a weekly meeting to discuss current projects and priorities. We will also review any new developments or updates. Please come prepared with any updates or questions you may have.',
    'dtstart' =>'2033-12-24 14:00',
    'dtend' => '2033-12-24 16:00',
    'location' => 'Mount Everest',
    'url' => 'https://google.com',

    'rrule' => 'FREQ=DAILY;COUNT=10',
    'last-modified' => $icsgen->format_timestamp('now'),
    'created' => $icsgen->format_timestamp('now'),
    'priority' => 1,
    'sequence' => 0,
    'class' => 'PUBLIC',
    'status' => 'CONFIRMED', // "TENTATIVE", "CONFIRMED", "CANCELLED"
    'transp' => 'OPAQUE', // "TRANSPARENT", "OPAQUE"
    'resources' => 'Projector,VCR',
    'geo' => '37.386013;-122.082932', // LATLONG
    'organizer' => 'sam@example.com',
    'recurrence-id' => $icsgen->format_timestamp('yesterday'),
]);

// get ICS string
$str = $icsgen->getString();

// get ICS object
$ics = $icsgen->getICS();

// get path to a temporary .ics file
// using wire()->files->tempDir
$path = $icsgen->getFile();

// get path to a temporary .ics file by ID
// this is useful if you need the same ICS multiple times in one request
// (the id will be part of the filename and only created if it does not exist yet)
$path = $icsgen->getFileByID('christmas-2033');

// example: send email with ics file
$mail = wireMail();
$mail->attachment($path, 'calendar.ics');
$mail->to($user->email);
$mail->subject('ICS Demo');
$mail->body('This is a ICS demo.');
$numSent = $mail->send();

```

Code is based on https://gist.github.com/jakebellacera/635416 with some improvements.

PRs are open.
