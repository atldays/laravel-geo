# Laravel Geo

[![Latest Version on Packagist](https://img.shields.io/packagist/v/atldays/laravel-geo.svg?logo=packagist&style=for-the-badge)](https://packagist.org/packages/atldays/laravel-geo)
[![Total Downloads](https://img.shields.io/packagist/dt/atldays/laravel-geo.svg?style=for-the-badge&color=blue)](https://packagist.org/packages/atldays/laravel-geo/stats)
[![CI](https://img.shields.io/github/actions/workflow/status/atldays/laravel-geo/ci.yml?style=for-the-badge&label=CI)](https://github.com/atldays/laravel-geo/actions/workflows/ci.yml)
[![Live](https://img.shields.io/github/actions/workflow/status/atldays/laravel-geo/live.yml?style=for-the-badge&label=Live)](https://github.com/atldays/laravel-geo/actions/workflows/live.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](LICENSE.md)

`atldays/laravel-geo` retrieves visitor location from IP addresses using both online and local services. No matter which provider is used, the result is normalized geo data such as country, city, continent, and coordinates.

You can use it directly from the current request, from any `Request` instance, or from an explicit IP address.

All supported drivers are normalized into the same strongly typed `GeoDataContract`, so you work with consistent DTOs instead of provider-specific arrays.

## Drivers

The package includes these drivers out of the box:

- [IpApi](https://ip-api.com/)
- [MaxMind](https://www.maxmind.com/)

The default driver is `IpApi`, because it lets developers install the package and see real results immediately.

## Installation

```bash
composer require atldays/laravel-geo
```

Publish the config file if you want to customize driver order or provider settings:

```bash
php artisan vendor:publish --tag=laravel-geo-config
```

## Quick Start

### Current request geo data

Use the `Geo` facade when you want data for the current request.

```php
use Atldays\Geo\Facades\Geo;

$country = Geo::country();
$city = Geo::city();
$latitude = Geo::latitude();
$payload = Geo::data();
```

Available facade methods match `GeoDataContract`:

- `Geo::ip()`
- `Geo::provider()`
- `Geo::continent()`
- `Geo::country()`
- `Geo::city()`
- `Geo::registeredCountry()`
- `Geo::accuracyRadius()`
- `Geo::latitude()`
- `Geo::longitude()`
- `Geo::timeZone()`
- `Geo::postalCode()`
- `Geo::data()`
- `Geo::toArray()`

### Explicit IP and request lookups

Use the `GeoManager` facade when you want to resolve a specific IP or request instance.

```php
use Atldays\Geo\Facades\GeoManager;
use Illuminate\Http\Request;

$byIp = GeoManager::ip('8.8.8.8');
$currentRequest = GeoManager::request();

$request = Request::create('/?ip=8.8.8.8', 'GET');
$geo = GeoManager::request($request);

$country = $geo->country();
$city = $geo->city();
$payload = $geo->data();
```

`GeoManager::ip()` and `GeoManager::request()` return `Atldays\Geo\Contracts\GeoDataContract`.

## Configuration

The main config file is [config/geo.php](/Users/anjeytsibylskij/Documents/Github/atldays/laravel-geo/config/geo.php).

### Default driver and fallbacks

```php
use Atldays\Geo\Drivers\IpApi;

return [
    'driver' => IpApi::class,
    'fallbacks' => [],
];
```

You can switch to `MaxMind` and keep `IpApi` as a fallback:

```php
use Atldays\Geo\Drivers\IpApi;
use Atldays\Geo\Drivers\MaxMind;

return [
    'driver' => MaxMind::class,
    'fallbacks' => [
        IpApi::class,
    ],
];
```

Drivers are resolved in this order:

1. `geo.driver`
2. every class from `geo.fallbacks`

If a driver throws `DriverUnavailableException`, the manager moves to the next configured driver.

## Request Macros

The package registers two request macros:

- `request()->geo()`
- `request()->realIp()`
- `request()->fakeIp()`

`GeoManager::request()` uses:

1. `fakeIp()` when debug mode is enabled and a valid fake IP is present
2. otherwise `realIp()`

The fake IP input key is configurable:

```php
'request' => [
    'fake_ip_key' => env('GEO_FAKE_IP_KEY', 'ip'),
],
```

That makes local testing convenient:

```php
// In debug mode, with GEO_FAKE_IP_KEY=ip
// GET /some-page?ip=8.8.8.8

Geo::country();
```

You can also resolve geo data directly from any `Request` instance:

```php
use Illuminate\Http\Request;

$request = Request::create('/?ip=8.8.8.8', 'GET');
$geo = $request->geo();

$country = $geo->country();
$city = $geo->city();
```

## IP-API

[IP-API](https://ip-api.com/) is the default driver because it gives immediate feedback after installation.

You do not need to create credentials or download a local database to start using it.

If you install the package and keep the default configuration, `Geo` and `GeoManager` will already resolve data through `IpApi`.

Example:

```php
use Atldays\Geo\Facades\Geo;
use Atldays\Geo\Facades\GeoManager;

$currentCountry = Geo::country();
$byIp = GeoManager::ip('8.8.8.8');
```

Config:

```php
'ip_api' => [
    'base_url' => env('GEO_IP_API_BASE_URL', 'http://ip-api.com'),
    'timeout' => env('GEO_IP_API_TIMEOUT'),
],
```

This is the recommended choice when you want to try the package quickly without creating external credentials or downloading a local database first.

## MaxMind

[MaxMind](https://www.maxmind.com/) uses a local `.mmdb` database and is the better choice when you want stable local lookups backed by a real database file.

To use it, you need a MaxMind account and credentials for the GeoLite2 download service.

GeoLite2 is free, so developers can start with the free MaxMind offering and still get a solid local integration.

Config:

```php
'maxmind' => [
    'account_id' => env('MAXMIND_ACCOUNT_ID'),
    'license_key' => env('MAXMIND_LICENSE_KEY'),
    'edition_id' => env('MAXMIND_EDITION_ID', 'GeoLite2-City'),
    'download_url' => env('MAXMIND_DOWNLOAD_URL'),
    'database_path' => env('MAXMIND_DATABASE_PATH', storage_path('app/geo/maxmind')),
    'database_filename' => env('MAXMIND_DATABASE_FILENAME'),
    'metadata_filename' => env('MAXMIND_METADATA_FILENAME', 'metadata.json'),
],
```

### MaxMind setup flow

1. Create or sign in to your MaxMind account.
2. Generate a license key for GeoLite2 downloads.
3. Add `MAXMIND_ACCOUNT_ID` and `MAXMIND_LICENSE_KEY` to your environment.
4. Switch your `geo.driver` to `MaxMind::class` if you want it as the primary driver.
5. Run the update command to download the local database.

Example environment:

```dotenv
MAXMIND_ACCOUNT_ID=your-account-id
MAXMIND_LICENSE_KEY=your-license-key
```

### Updating the MaxMind database

Run:

```bash
php artisan geo:update
```

Force a fresh download even if the local file appears current:

```bash
php artisan geo:update --force
```

The updater stores:

- the downloaded `.mmdb` file
- a metadata JSON file next to it

`UpdateResult` is generic and only reports:

- whether the resource was downloaded
- the local stored path
- the metadata path

Source-specific details such as `edition_id`, `download_url`, or `remote_last_modified` live inside metadata instead of the shared DTO.

## Geo Data

Resolved geo data is normalized into `GeoDataContract`.

That means driver-specific payloads are not exposed as arbitrary top-level structures. Every supported driver is mapped into the same typed result shape.

Depending on the driver and available source data, you may receive:

- provider
- continent
- country
- city
- registered country
- accuracy radius
- latitude and longitude
- time zone
- postal code
- raw provider payload

Nested geo objects are normalized too:

- `continent()` returns `ContinentContract`
  It provides a strict name, continent code, and nullable external ID.
- `country()` and `registeredCountry()` return `CountryContract`
  They provide a strict name, ISO code, normalized continent object, and nullable external ID.
- `city()` returns `CityContract`
  It provides a strict name, normalized country object, subdivisions collection, and nullable external ID.
- city subdivisions implement `SubdivisionContract`
  They provide a strict name, ISO code, and nullable external ID.

`provider()` returns the driver name that produced the result, such as `MaxMind` or `IpApi`.

`externalId` is provider-specific metadata. For `MaxMind`, it maps to `geoname_id`. For `IpApi`, it is `null`. It should not be treated as a globally stable cross-provider identifier.

Some drivers may return partial data. A lookup can still be successful even if only part of the geo payload is available.

## Public API

Main public package entry points:

- `Atldays\Geo\Facades\Geo`
- `Atldays\Geo\Facades\GeoManager`
- `Atldays\Geo\Contracts\GeoDataContract`
- `Atldays\Geo\Contracts\GeoDriver`
- `Atldays\Geo\Contracts\GeoDriverUpdatable`
- `Atldays\Geo\Contracts\UpdateResultContract`

## Testing

Run the standard test suite:

```bash
composer test
```

Run formatting checks:

```bash
composer format:test
```

Run live driver checks:

```bash
composer test:live
```

Live tests cover real providers and may require external credentials, especially for `MaxMind`.

## License

The MIT License (MIT). Please see [LICENSE.md](/Users/anjeytsibylskij/Documents/Github/atldays/laravel-geo/LICENSE.md) for more information.
