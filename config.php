<?php

/**
 * Common Services configuration bootstrap.
 *
 * 2026-07-19:
 * Migrated the environment file from:
 *
 *     /var/www/html/site/CommonServices/.env
 *
 * to:
 *
 *     /etc/commonservices/.env
 *
 * This keeps credentials and other secrets outside the web directory.
 */

/*
 * Original autoloader path, replaced on 2026-07-19 with an absolute path
 * based on this file's location:
 *
 * require 'vendor/autoload.php';
 */

require_once __DIR__ . '/vendor/autoload.php';

/*
 * Original dotenv loading logic, replaced on 2026-07-19:
 *
 * $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
 * $dotenv->load();
 */

$envDirectory = '/etc/commonservices';
$envFile = $envDirectory . '/.env';

if (!is_readable($envFile)) {
    error_log(
        'Configuration error: environment file is missing or unreadable: '
        . $envFile
    );

    http_response_code(500);
    exit('Internal server error');
}

try {
    $dotenv = Dotenv\Dotenv::createImmutable($envDirectory);
    $dotenv->load();
} catch (Throwable $exception) {
    error_log(
        'Configuration error while loading environment variables: '
        . $exception->getMessage()
    );

    http_response_code(500);
    exit('Internal server error');
}

/**
 * Read an environment variable.
 *
 * 2026-07-19:
 * Retained the existing helper behavior while adding a return type and
 * supporting values loaded into either $_ENV or $_SERVER.
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function env(string $key, $default = null)
{
    return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
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