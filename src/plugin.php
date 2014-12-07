<?php

namespace FluentDOM\ContentLines {

  if (class_exists('\\FluentDOM')) {
    \FluentDOM::registerLoader(
      new \FluentDOM\Loader\Lazy(
        [
          'text/calendar' => function () {
            return new Loader\ICalendar;
          },
          'text/vcard' => function () {
            return new Loader\VCard;
          }
        ]
      )
    );
  }
}