<?php

namespace Atldays\Geo\Data;

use Atldays\Geo\Contracts\CountryDefinitionContract;
use Spatie\LaravelData\Data;

class CountryDefinition extends Data implements CountryDefinitionContract
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $officialName = null,
        public readonly ?string $nativeName = null,
        public readonly ?string $nativeOfficialName = null,
        public readonly ?array $nativeNames = null,
        public readonly ?string $demonym = null,
        public readonly ?string $capital = null,
        public readonly ?string $isoAlpha2 = null,
        public readonly ?string $isoAlpha3 = null,
        public readonly ?string $isoNumeric = null,
        public readonly ?array $currencies = null,
        public readonly ?string $tld = null,
        public readonly ?array $tlds = null,
        public readonly ?array $altSpellings = null,
        public readonly ?string $language = null,
        public readonly ?array $languages = null,
        public readonly array $translations = [],
        public readonly ?array $geodata = null,
        public readonly ?string $continent = null,
        public readonly ?bool $postalCode = null,
        public readonly ?string $latitude = null,
        public readonly ?string $longitude = null,
        public readonly ?string $latitudeDesc = null,
        public readonly ?string $longitudeDesc = null,
        public readonly ?string $maxLatitude = null,
        public readonly ?string $maxLongitude = null,
        public readonly ?string $minLatitude = null,
        public readonly ?string $minLongitude = null,
        public readonly ?int $area = null,
        public readonly ?string $region = null,
        public readonly ?string $subregion = null,
        public readonly ?string $worldRegion = null,
        public readonly ?string $regionCode = null,
        public readonly ?string $subregionCode = null,
        public readonly ?bool $landlocked = null,
        public readonly ?array $borders = null,
        public readonly ?string $independent = null,
        public readonly ?string $callingCode = null,
        public readonly ?array $callingCodes = null,
        public readonly ?string $nationalPrefix = null,
        public readonly ?int $nationalNumberLength = null,
        public readonly ?array $nationalNumberLengths = null,
        public readonly ?int $nationalDestinationCodeLength = null,
        public readonly ?array $nationalDestinationCodeLengths = null,
        public readonly ?string $internationalPrefix = null,
        public readonly ?array $extra = null,
        public readonly ?int $geonameId = null,
        public readonly ?string $edgar = null,
        public readonly ?string $itu = null,
        public readonly ?string $marc = null,
        public readonly ?string $wmo = null,
        public readonly ?string $ds = null,
        public readonly ?string $fifa = null,
        public readonly ?string $fips = null,
        public readonly ?int $gaul = null,
        public readonly ?string $ioc = null,
        public readonly ?string $cowc = null,
        public readonly ?int $cown = null,
        public readonly ?int $fao = null,
        public readonly ?int $imf = null,
        public readonly ?string $ar5 = null,
        public readonly ?string $addressFormat = null,
        public readonly ?bool $euMember = null,
        public readonly ?string $dataProtection = null,
        public readonly ?array $vatRates = null,
        public readonly ?string $emoji = null,
        public readonly ?string $geoJson = null,
        public readonly ?string $flag = null,
        public readonly ?array $divisions = null,
        public readonly ?array $timezones = null,
    ) {}

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getOfficialName(): ?string
    {
        return $this->officialName;
    }

    public function getNativeName(): ?string
    {
        return $this->nativeName;
    }

    public function getNativeOfficialName(): ?string
    {
        return $this->nativeOfficialName;
    }

    public function getNativeNames(): ?array
    {
        return $this->nativeNames;
    }

    public function getDemonym(): ?string
    {
        return $this->demonym;
    }

    public function getCapital(): ?string
    {
        return $this->capital;
    }

    public function getIsoAlpha2(): ?string
    {
        return $this->isoAlpha2;
    }

    public function getIsoAlpha3(): ?string
    {
        return $this->isoAlpha3;
    }

    public function getIsoNumeric(): ?string
    {
        return $this->isoNumeric;
    }

    public function getCurrencies(): ?array
    {
        return $this->currencies;
    }

    public function getTld(): ?string
    {
        return $this->tld;
    }

    public function getTlds(): ?array
    {
        return $this->tlds;
    }

    public function getAltSpellings(): ?array
    {
        return $this->altSpellings;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function getLanguages(): ?array
    {
        return $this->languages;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function getGeodata(): ?array
    {
        return $this->geodata;
    }

    public function getContinent(): ?string
    {
        return $this->continent;
    }

    public function usesPostalCode(): ?bool
    {
        return $this->postalCode;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function getLatitudeDesc(): ?string
    {
        return $this->latitudeDesc;
    }

    public function getLongitudeDesc(): ?string
    {
        return $this->longitudeDesc;
    }

    public function getMaxLatitude(): ?string
    {
        return $this->maxLatitude;
    }

    public function getMaxLongitude(): ?string
    {
        return $this->maxLongitude;
    }

    public function getMinLatitude(): ?string
    {
        return $this->minLatitude;
    }

    public function getMinLongitude(): ?string
    {
        return $this->minLongitude;
    }

    public function getArea(): ?int
    {
        return $this->area;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function getSubregion(): ?string
    {
        return $this->subregion;
    }

    public function getWorldRegion(): ?string
    {
        return $this->worldRegion;
    }

    public function getRegionCode(): ?string
    {
        return $this->regionCode;
    }

    public function getSubregionCode(): ?string
    {
        return $this->subregionCode;
    }

    public function isLandlocked(): ?bool
    {
        return $this->landlocked;
    }

    public function getBorders(): ?array
    {
        return $this->borders;
    }

    public function isIndependent(): ?string
    {
        return $this->independent;
    }

    public function getCallingCode(): ?string
    {
        return $this->callingCode;
    }

    public function getCallingCodes(): ?array
    {
        return $this->callingCodes;
    }

    public function getNationalPrefix(): ?string
    {
        return $this->nationalPrefix;
    }

    public function getNationalNumberLength(): ?int
    {
        return $this->nationalNumberLength;
    }

    public function getNationalNumberLengths(): ?array
    {
        return $this->nationalNumberLengths;
    }

    public function getNationalDestinationCodeLength(): ?int
    {
        return $this->nationalDestinationCodeLength;
    }

    public function getNationalDestinationCodeLengths(): ?array
    {
        return $this->nationalDestinationCodeLengths;
    }

    public function getInternationalPrefix(): ?string
    {
        return $this->internationalPrefix;
    }

    public function getExtra(): ?array
    {
        return $this->extra;
    }

    public function getGeonameId(): ?int
    {
        return $this->geonameId;
    }

    public function getEdgar(): ?string
    {
        return $this->edgar;
    }

    public function getItu(): ?string
    {
        return $this->itu;
    }

    public function getMarc(): ?string
    {
        return $this->marc;
    }

    public function getWmo(): ?string
    {
        return $this->wmo;
    }

    public function getDs(): ?string
    {
        return $this->ds;
    }

    public function getFifa(): ?string
    {
        return $this->fifa;
    }

    public function getFips(): ?string
    {
        return $this->fips;
    }

    public function getGaul(): ?int
    {
        return $this->gaul;
    }

    public function getIoc(): ?string
    {
        return $this->ioc;
    }

    public function getCowc(): ?string
    {
        return $this->cowc;
    }

    public function getCown(): ?int
    {
        return $this->cown;
    }

    public function getFao(): ?int
    {
        return $this->fao;
    }

    public function getImf(): ?int
    {
        return $this->imf;
    }

    public function getAr5(): ?string
    {
        return $this->ar5;
    }

    public function getAddressFormat(): ?string
    {
        return $this->addressFormat;
    }

    public function isEuMember(): ?bool
    {
        return $this->euMember;
    }

    public function getDataProtection(): ?string
    {
        return $this->dataProtection;
    }

    public function getVatRates(): ?array
    {
        return $this->vatRates;
    }

    public function getEmoji(): ?string
    {
        return $this->emoji;
    }

    public function getGeoJson(): ?string
    {
        return $this->geoJson;
    }

    public function getFlag(): ?string
    {
        return $this->flag;
    }

    public function getDivisions(): ?array
    {
        return $this->divisions;
    }

    public function getDivision(string $division): ?array
    {
        return $this->divisions[trim($division)] ?? null;
    }

    public function getTimezones(): ?array
    {
        return $this->timezones;
    }
}
