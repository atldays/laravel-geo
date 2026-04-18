<?php

use Atldays\Geo\Drivers\MaxMind;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | This driver is resolved first when the package needs to work with geo
    | data inside the host Laravel application.
    |
    */
    'driver' => MaxMind::class,

    /*
    |--------------------------------------------------------------------------
    | Fallback Drivers
    |--------------------------------------------------------------------------
    |
    | These drivers are resolved after the default driver when fallback
    | behavior is needed inside the host Laravel application.
    |
    */
    'fallbacks' => [],

    /*
    |--------------------------------------------------------------------------
    | Request Configuration
    |--------------------------------------------------------------------------
    |
    | These options configure how the package interacts with the current
    | request inside the host Laravel application.
    |
    */
    'request' => [
        /*
        |--------------------------------------------------------------------------
        | Fake IP Input Key
        |--------------------------------------------------------------------------
        |
        | This key is used by the fake client IP request macro when debug mode
        | is enabled. Change it if your application uses a different input name
        | for IP spoofing during local development or testing.
        |
        */
        'fake_ip_key' => env('GEO_FAKE_IP_KEY', 'ip'),
    ],

    /*
    |--------------------------------------------------------------------------
    | MaxMind Configuration
    |--------------------------------------------------------------------------
    |
    | These options define how the package integrates with MaxMind inside
    | the host Laravel application.
    |
    */
    'maxmind' => [
        /*
        |--------------------------------------------------------------------------
        | Account ID
        |--------------------------------------------------------------------------
        |
        | The MaxMind account identifier used when authenticating download
        | requests issued by the Laravel application.
        |
        */
        'account_id' => env('MAXMIND_ACCOUNT_ID'),

        /*
        |--------------------------------------------------------------------------
        | License Key
        |--------------------------------------------------------------------------
        |
        | The MaxMind license key used alongside the account identifier for
        | authenticated requests made by the Laravel application.
        |
        */
        'license_key' => env('MAXMIND_LICENSE_KEY'),

        /*
        |--------------------------------------------------------------------------
        | Edition ID
        |--------------------------------------------------------------------------
        |
        | The MaxMind edition that should be downloaded by default, such as
        | GeoLite2-City or GeoLite2-Country.
        |
        */
        'edition_id' => env('MAXMIND_EDITION_ID', 'GeoLite2-City'),

        /*
        |--------------------------------------------------------------------------
        | Download URL
        |--------------------------------------------------------------------------
        |
        | Optional explicit permalink from the MaxMind account portal.
        | When omitted, the package builds the current direct-download URL
        | from the configured edition ID.
        |
        */
        'download_url' => env('MAXMIND_DOWNLOAD_URL'),

        /*
        |--------------------------------------------------------------------------
        | Database Path
        |--------------------------------------------------------------------------
        |
        | Directory where the extracted .mmdb file and its metadata should live.
        |
        */
        'database_path' => env('MAXMIND_DATABASE_PATH', storage_path('app/geo/maxmind')),

        /*
        |--------------------------------------------------------------------------
        | Database Filename
        |--------------------------------------------------------------------------
        |
        | Optional override for the final .mmdb filename. By default, the name
        | from the MaxMind archive is preserved.
        |
        */
        'database_filename' => env('MAXMIND_DATABASE_FILENAME'),

        /*
        |--------------------------------------------------------------------------
        | Metadata Filename
        |--------------------------------------------------------------------------
        |
        | The filename used to store metadata about the latest downloaded
        | MaxMind database inside the Laravel application.
        |
        */
        'metadata_filename' => env('MAXMIND_METADATA_FILENAME', 'metadata.json'),

    ],
];
