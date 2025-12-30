# ICSGenerator
A ProcessWire module to generate ICS calendar strings and files. Events are managed via a standard [WireArray](https://processwire.com/api/ref/wire-array/).

## Links
* [Module Directory](https://processwire.com/modules/ics-generator/)
* [Support Forum](https://processwire.com/talk/topic/26817-ics-generator/)
* [ICS Validator](https://icalendar.org/validator.html)

---

## Quick Start
```php
$icsgen = wire()->modules->IcsGenerator;

// 1. Add Event
$icsgen->events->add([
    'summary'     => 'Christmas 2033',
    'dtstart'     => '2033-12-24 18:00',
    'dtend'       => '2033-12-24 22:00',
    'location'    => 'The North Pole',
    'description' => 'Ho ho ho!',
    'url'         => 'https://example.com'
]);

// 2. Output
echo $icsgen->getString();             // Raw ICS string
$path = $icsgen->getFile('event.ics'); // Path to temp file
```

## Common Tasks

### Multiple events
```php
// Add individually
$icsgen->events->add($event1);
$icsgen->events->add($event2);

// Or overwrite all at once
$icsgen->events->setArray([$event1, $event2]); 
```

### Save to a ProcessWire file field
```php
$page->of(false);
$page->files_field->add($icsgen->getFile('invite.ics'));
$page->save('files_field');
```

### Send via email
```php
$mail = wireMail()->to($user->email)->subject('Event Invite');
$mail->attachment($icsgen->getFile(), 'invite.ics');
$mail->send();
```

## Event property reference

Some calendar clients may not support all properties.

| Property | Sanitizer | Description |
| :--- | :--- | :--- |
| `SUMMARY` | text | Event title / headline |
| `DTSTART` | timestamp | Start time (Required) |
| `DTEND` | timestamp | End time |
| `DURATION` | text | Alternative to DTEND (e.g., `PT2H`) |
| `DESCRIPTION`| longtext | Detailed event notes |
| `LOCATION` | text | Physical location or address |
| `URL` | uri | Event or registration website |
| `RRULE` | rawtext | Recurrence rule (e.g., `FREQ=DAILY;COUNT=10`) |
| `STATUS` | text | `TENTATIVE`, `CONFIRMED`, `CANCELLED` |
| `PRIORITY` | integer | 1 (High) to 9 (Low) |
| `ORGANIZER` | email | Organizer email address (e.g. `jsmith@host1.com` or `CN=John Smith:MAILTO:jsmith@host1.com`) |
| `GEO` | rawtext | `LAT;LONG` (e.g., `37.38;-122.08`) |
| `UID` | text | Unique identifier for the event |
| `CLASS` | text | `PUBLIC`, `PRIVATE`, `CONFIDENTIAL` |
| `TRANSP` | text | `TRANSPARENT` (Free) or `OPAQUE` (Busy) |
| `RESOURCES` | rawtext | Equipment or rooms (e.g., `Projector`) |
| `RECURRENCE-ID`| timestamp | Identifies a specific instance of a recurring event |
| `DTSTAMP` | timestamp | Time the event was created/modified in the system |
| `CREATED` | timestamp | Original creation timestamp |
| `LAST-MODIFIED`| timestamp | Last update timestamp |
| `SEQUENCE` | integer | Revision sequence number |
| `X-*` | rawtext | Custom non-standard properties (completely unsanitized) |

## Timestamps
* All timestamp inputs accept PHP `DateTime` objects or strings (parsed via `new DateTime()`).
* All timestamp output is automatically converted to UTC (Z timestamp).

## Timezones

Defaults to server timezone.

### `timezone` event property 

Will be applied to all date *strings* in this event:

```php
$icsgen->events->add([
    'timezone' => new \DateTimeZone('Europe/Berlin'),
    'dtstart'  => 'now',
    'dtend'    => 'now + 60 minutes',
]);
```

### Timezone per timestamp

When using DateTime, the timezone property above is ignored. Instead, you can define the timezone in the DateTime constructor:

```php
$icsgen->events->add([
    'dtstart'  => new \DateTime('2033-12-24 12:00', new \DateTimeZone('Asia/Dubai')),
    'dtend'    => new \DateTime('2033-12-24 12:00', new \DateTimeZone('Europe/Paris')),
]);
```

### Timezone via date strings

`timezone` property will also be ignored when string is a unix timestamp or already contains a timezone:

```php
$icsgen->events->add([
    'dtstart'  => '@946684800',
    'dtend'    => '2010-01-28T15:00:00+02:00',
]);
```

---

**Credits:** Based on [jakebellacera's Gist](https://gist.github.com/jakebellacera/635416).