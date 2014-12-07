FluentDOM Loader ContentLines
=============================

This package adds additional loaders to FluentDOM (>= 5.2). 

iCalendar
---------

```php
<?php
$dom = FluentDOM::load(__DIR__.'/example.ical', 'text/calendar');
$dom->registerNamespace('xcal', 'urn:ietf:params:xml:ns:icalendar-2.0');
echo $dom('string(//xcal:vevent//xcal:summary)');
```

VCard
-----
