<?php

namespace Atldays\Geo\CountryDefinitions;

use Atldays\Geo\Concerns\InteractsWithData;
use Atldays\Geo\Contracts\{CountryDefinitionContract, CountryDefinitionProvider};
use Atldays\Geo\Data\CountryDefinition;
use Atldays\Geo\Exceptions\{DefinitionNotFound, DefinitionUnavailable};
use DateTimeZone;
use Rinvex\Country\{Country, CountryLoader};
use Throwable;

class Rinvex implements CountryDefinitionProvider
{
    use InteractsWithData;

    protected ?Country $country = null;

    public function resolve(string $isoAlpha2): CountryDefinitionContract
    {
        if (!class_exists(CountryLoader::class) || !class_exists(Country::class)) {
            throw DefinitionUnavailable::forPackage(
                static::class,
                'rinvex/countries',
            );
        }

        $isoAlpha2 = strtoupper(trim($isoAlpha2));

        if ($isoAlpha2 === '') {
            throw DefinitionNotFound::forIsoAlpha2($isoAlpha2);
        }

        try {
            $country = CountryLoader::country($isoAlpha2);
        } catch (Throwable $exception) {
            throw DefinitionNotFound::forIsoAlpha2($isoAlpha2, $exception);
        }

        if (!$country instanceof Country) {
            throw DefinitionNotFound::forIsoAlpha2($isoAlpha2);
        }

        $this->country = $country;

        return CountryDefinition::from($this->definition());
    }

    protected function definition(): array
    {
        $country = $this->country();

        return [
            'name' => $country->getName(),
            'officialName' => $country->getOfficialName(),
            'nativeName' => $country->getNativeName(),
            'nativeOfficialName' => $country->getNativeOfficialName(),
            'nativeNames' => $this->dataArray('name.native'),
            'demonym' => $this->dataString('demonym'),
            'capital' => $this->dataString('capital'),
            'isoAlpha2' => $this->dataString('iso_3166_1_alpha2'),
            'isoAlpha3' => $this->dataString('iso_3166_1_alpha3'),
            'isoNumeric' => $this->dataString('iso_3166_1_numeric'),
            'currencies' => $this->dataArray('currency'),
            'tld' => $country->getTld(),
            'tlds' => $this->dataArray('tld'),
            'altSpellings' => $this->dataArray('alt_spellings'),
            'language' => $country->getLanguage(),
            'languages' => $this->dataArray('languages'),
            'translations' => $this->valueArray($country->getTranslations()) ?? [],
            'geodata' => $this->dataArray('geo'),
            'continent' => $country->getContinent(),
            'postalCode' => $this->dataBool('geo.postal_code'),
            'latitude' => $this->dataString('geo.latitude'),
            'longitude' => $this->dataString('geo.longitude'),
            'latitudeDesc' => $this->dataString('geo.latitude_desc'),
            'longitudeDesc' => $this->dataString('geo.longitude_desc'),
            'maxLatitude' => $this->dataString('geo.max_latitude'),
            'maxLongitude' => $this->dataString('geo.max_longitude'),
            'minLatitude' => $this->dataString('geo.min_latitude'),
            'minLongitude' => $this->dataString('geo.min_longitude'),
            'area' => $this->dataInt('geo.area'),
            'region' => $this->dataString('geo.region'),
            'subregion' => $this->dataString('geo.subregion'),
            'worldRegion' => $this->dataString('geo.world_region'),
            'regionCode' => $this->dataString('geo.region_code'),
            'subregionCode' => $this->dataString('geo.subregion_code'),
            'landlocked' => $this->dataBool('geo.landlocked'),
            'borders' => $this->dataArray('geo.borders'),
            'independent' => $this->dataString('geo.independent'),
            'callingCode' => $country->getCallingCode(),
            'callingCodes' => $this->dataArray('dialling.calling_code'),
            'nationalPrefix' => $this->dataString('dialling.national_prefix'),
            'nationalNumberLength' => $this->firstDataInt('dialling.national_number_lengths'),
            'nationalNumberLengths' => $this->dataArray('dialling.national_number_lengths'),
            'nationalDestinationCodeLength' => $this->firstDataInt('dialling.national_destination_code_lengths'),
            'nationalDestinationCodeLengths' => $this->dataArray('dialling.national_destination_code_lengths'),
            'internationalPrefix' => $this->dataString('dialling.international_prefix'),
            'extra' => $this->dataArray('extra'),
            'geonameId' => $this->dataInt('extra.geonameid'),
            'edgar' => $this->dataString('extra.edgar'),
            'itu' => $this->dataString('extra.itu'),
            'marc' => $this->dataString('extra.marc'),
            'wmo' => $this->dataString('extra.wmo'),
            'ds' => $this->dataString('extra.ds'),
            'fifa' => $this->dataString('extra.fifa'),
            'fips' => $this->dataString('extra.fips'),
            'gaul' => $this->dataInt('extra.gaul'),
            'ioc' => $this->dataString('extra.ioc'),
            'cowc' => $this->dataString('extra.cowc'),
            'cown' => $this->dataInt('extra.cown'),
            'fao' => $this->dataInt('extra.fao'),
            'imf' => $this->dataInt('extra.imf'),
            'ar5' => $this->dataString('extra.ar5'),
            'addressFormat' => $this->dataString('extra.address_format'),
            'euMember' => $this->dataBool('extra.eu_member'),
            'dataProtection' => $this->dataString('extra.data_protection'),
            'vatRates' => $this->dataArray('extra.vat_rates'),
            'emoji' => $country->getEmoji(),
            'geoJson' => $country->getGeoJson(),
            'flag' => $country->getFlag(),
            'divisions' => $country->getDivisions(),
            'timezones' => DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $country->getIsoAlpha2()),
        ];
    }

    protected function data(): array
    {
        return $this->country()?->getAttributes() ?? [];
    }

    protected function country(): Country
    {
        if (!$this->country instanceof Country) {
            throw DefinitionNotFound::forIsoAlpha2('');
        }

        return $this->country;
    }

    protected function firstDataInt(string $key): ?int
    {
        $value = $this->dataArray($key);

        return is_numeric($value[0] ?? null) ? (int)$value[0] : null;
    }
}
