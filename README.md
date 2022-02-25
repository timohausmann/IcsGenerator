# ICSGenerator
The module can generate basic ICS calendar strings and files.

The module simply extends WireData. It has these properties and default values:

```php
$this->set('date', 'now');
$this->set('dateEnd', 'now + 60 minutes');
$this->set('summary', 'ICS Calendar File');
$this->set('description', '');
$this->set('location', '');
$this->set('url', '');
$this->set('timezone', new \DateTimeZone(date_default_timezone_get()));
```

* `date` / `dateEnd` should be a PHP `DateTime` object or a string. 
* Date strings will be passed to [DateTime constructor](https://www.php.net/manual/en/datetime.construct.php).
* Date strings will use the set timezone. 
* Final output will be converted to UTC (Z timestamp)

```php
// get the module
$icsgen = wire()->modules->IcsGenerator;

// set single properties (date strings)
$icsgen->set('date',    '2033-12-24 12:00');
$icsgen->set('dateEnd', '2033-12-24 14:00');

// set single properties (DateTime)
$icsgen->set('date',    new \DateTime('2033-12-24 12:00'));
$icsgen->set('dateEnd', new \DateTime('2033-12-24 14:00'));

// the set timezone will only impact time strings!
$icsgen->set('timezone', new \DateTimeZone('Europe/Berlin'));
$icsgen->set('date',    'now');
$icsgen->set('dateEnd', 'now + 60 minutes');

// set timezone will be ignored when string is a unix timestamp or contains a timezone
$icsgen->set('date',    '@946684800');
$icsgen->set('date',    '2010-01-28T15:00:00+02:00');

// set timezone will not impact DateTime!
// construct your DateTime with DateTimeZone instead
$icsgen->set('date',    new \DateTime('2033-12-24 12:00', new \DateTimeZone('Asia/Dubai')));
$icsgen->set('dateEnd', new \DateTime('2033-12-24 12:00', new \DateTimeZone('Europe/Paris')));

// set multiple properties at once
$icsgen->setArray(array(
    'date' => '2033-12-24 12:00',
    'dateEnd' => '2033-12-24 14:00',
    'summary' => 'Event Title',
    'description' => 'Event Description',
));

// get ICS string
$str = $icsgen->getString();

// get ICS object
$ics = $icsgen->getICS();

// get path to a temporary .ics file
// using wire()->files->tempDir
$path = $icsgen->getFile();

// get path to a temporary .ics file by ID
// this is useful if you need the same ICS multiple times in one request
// (same file id will only be created once)
$path = $icsgen->getFileByID('christmas-2033');

```

Code is heavily based on https://gist.github.com/jakebellacera/635416 with some improvements.

PRs are open.