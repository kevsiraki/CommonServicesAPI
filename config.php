<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

function env($key, $default = null) {
    return $_ENV[$key] ?? $default;
}

/*
Architecture:

- .env for all secrets
- File for each endpoint, 1 common interface/root index file
- Logging for all actions and errors

Endpoint Setup:

- Seperate file for each, and one index.php that joins it all together with includes.

- Email
- Discord
- Tweet
- Pings, Health Checks, Alerts

- Easter Egg Endpoint:

Returns the number of days after March 21 on which Easter falls for a given year. If no year is specified, the current year is assumed.

This function can be used instead of easter_date() to calculate Easter for years which fall outside the range of Unix timestamps (i.e. before 1970 or after 2037).

The date of Easter Day was defined by the Council of Nicaea in AD325 as the Sunday after the first full moon which falls on or after the Spring Equinox. 
The Equinox is assumed to always fall on 21st March, so the calculation reduces to determining the date of the full moon and the date of the following Sunday. 
The algorithm used here was introduced around the year 532 by Dionysius Exiguus. 
Under the Julian Calendar (for years before 1753) a simple 19-year cycle is used to track the phases of the Moon. 
Under the Gregorian Calendar (for years after 1753 - devised by Clavius and Lilius, and introduced by Pope Gregory XIII in October 1582, 
and into Britain and its then colonies in September 1752) two correction factors are added to make the cycle more accurate.
*/ 