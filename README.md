# ICSGenerator
The module can generate basic ICS calendar strings and files.

**This development branch is WIP!**

```php
// get the module
$icsgen = wire()->modules->IcsGenerator;

// add an event
$icsgen->events->add([
    'description' => 'Event Title 1',
    'summary' => 'Event body 1',
    'dtstart' =>'2033-12-24 12:00',
    'dtend' => '2033-12-24 14:00',
]);

// add another event
$icsgen->events->add([
    'description' => 'Event Title 2',
    'summary' => 'Event body 2',
    'dtstart' =>'2033-12-26 12:00',
    'dtend' => '2033-12-26 14:00',
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

Code is heavily based on https://gist.github.com/jakebellacera/635416 with some improvements.

PRs are open.
