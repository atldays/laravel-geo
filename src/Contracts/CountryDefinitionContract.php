<?php

namespace Atldays\Geo\Contracts;

interface CountryDefinitionContract
{
    public function getName(): ?string;

    public function getOfficialName(): ?string;

    public function getNativeName(): ?string;

    public function getNativeOfficialName(): ?string;

    public function getNativeNames(): ?array;

    public function getDemonym(): ?string;

    public function getCapital(): ?string;

    public function getIsoAlpha2(): ?string;

    public function getIsoAlpha3(): ?string;

    public function getIsoNumeric(): ?string;

    public function getCurrencies(): ?array;

    public function getTld(): ?string;

    public function getTlds(): ?array;

    public function getAltSpellings(): ?array;

    public function getLanguage(): ?string;

    public function getLanguages(): ?array;

    public function getTranslations(): array;

    public function getGeodata(): ?array;

    public function getContinent(): ?string;

    public function usesPostalCode(): ?bool;

    public function getLatitude(): ?string;

    public function getLongitude(): ?string;

    public function getLatitudeDesc(): ?string;

    public function getLongitudeDesc(): ?string;

    public function getMaxLatitude(): ?string;

    public function getMaxLongitude(): ?string;

    public function getMinLatitude(): ?string;

    public function getMinLongitude(): ?string;

    public function getArea(): ?int;

    public function getRegion(): ?string;

    public function getSubregion(): ?string;

    public function getWorldRegion(): ?string;

    public function getRegionCode(): ?string;

    public function getSubregionCode(): ?string;

    public function isLandlocked(): ?bool;

    public function getBorders(): ?array;

    public function isIndependent(): ?string;

    public function getCallingCode(): ?string;

    public function getCallingCodes(): ?array;

    public function getNationalPrefix(): ?string;

    public function getNationalNumberLength(): ?int;

    public function getNationalNumberLengths(): ?array;

    public function getNationalDestinationCodeLength(): ?int;

    public function getNationalDestinationCodeLengths(): ?array;

    public function getInternationalPrefix(): ?string;

    public function getExtra(): ?array;

    public function getGeonameId(): ?int;

    public function getEdgar(): ?string;

    public function getItu(): ?string;

    public function getMarc(): ?string;

    public function getWmo(): ?string;

    public function getDs(): ?string;

    public function getFifa(): ?string;

    public function getFips(): ?string;

    public function getGaul(): ?int;

    public function getIoc(): ?string;

    public function getCowc(): ?string;

    public function getCown(): ?int;

    public function getFao(): ?int;

    public function getImf(): ?int;

    public function getAr5(): ?string;

    public function getAddressFormat(): ?string;

    public function isEuMember(): ?bool;

    public function getDataProtection(): ?string;

    public function getVatRates(): ?array;

    public function getEmoji(): ?string;

    public function getGeoJson(): ?string;

    public function getFlag(): ?string;

    public function getDivisions(): ?array;

    public function getDivision(string $division): ?array;

    public function getTimezones(): ?array;
}
