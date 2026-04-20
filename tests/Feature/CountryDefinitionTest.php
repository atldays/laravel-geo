<?php

namespace Tests\Feature;

use Atldays\Geo\Contracts\{CountryDefinitionContract, CountryDefinitionProvider};
use Atldays\Geo\CountryDefinitionManager;
use Atldays\Geo\CountryDefinitions\Rinvex;
use Atldays\Geo\Data\{Continent, Country, CountryDefinition};
use Atldays\Geo\Exceptions\{
    DefinitionNotFound,
    DefinitionUnavailable,
    InvalidDefinitionProvider
};
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class CountryDefinitionTest extends TestCase
{
    public function test_country_dto_uses_manager_provider_to_resolve_definition(): void
    {
        Config::set('geo.definitions.country', FakeCountryProvider::class);

        $country = new Country(
            name: 'Ukraine',
            isoCode: 'UA',
            continent: new Continent(name: 'Europe', code: 'EU'),
        );

        $definition = $country->definition();

        $this->assertInstanceOf(CountryDefinitionContract::class, $definition);
        $this->assertSame('Fake Ukraine', $definition->getName());
        $this->assertSame('UKR', $definition->getIsoAlpha3());
    }

    public function test_country_dto_can_resolve_country_definition(): void
    {
        Config::set('geo.definitions.country', Rinvex::class);

        $country = new Country(
            name: 'Ukraine',
            isoCode: 'UA',
            continent: new Continent(name: 'Europe', code: 'EU'),
        );

        $definition = $country->definition();

        $this->assertInstanceOf(CountryDefinitionContract::class, $definition);
        $this->assertInstanceOf(CountryDefinition::class, $definition);
        $this->assertSame('Ukraine', $definition->getName());
        $this->assertSame('UKR', $definition->getIsoAlpha3());
        $this->assertSame('.ua', $definition->getTld());
        $this->assertArrayHasKey('eng', $definition->getTranslations());
        $this->assertContains('Europe/Kyiv', $definition->getTimezones() ?? []);
    }

    public function test_country_definition_throws_for_unknown_iso_alpha_two_code(): void
    {
        Config::set('geo.definitions.country', Rinvex::class);

        $country = new Country(
            name: 'Unknown',
            isoCode: 'XX',
            continent: new Continent(name: 'Europe', code: 'EU'),
        );

        $this->expectException(DefinitionNotFound::class);
        $this->expectExceptionMessage('The country definition could not be resolved for ISO alpha-2 code [XX].');

        $country->definition();
    }

    public function test_country_definition_manager_rejects_invalid_configured_provider(): void
    {
        Config::set('geo.definitions.country', \stdClass::class);

        $this->expectException(InvalidDefinitionProvider::class);
        $this->expectExceptionMessage('configured country definition provider');

        $this->app->make(CountryDefinitionManager::class)->provider();
    }

    public function test_country_definition_manager_reports_missing_optional_dependency(): void
    {
        Config::set('geo.definitions.country', UnavailableRinvexProvider::class);

        $this->expectException(DefinitionUnavailable::class);
        $this->expectExceptionMessage('requires package [rinvex/countries]');

        $this->app->make(CountryDefinitionManager::class)->isoCode('UA');
    }

    public function test_rinvex_resolve_validates_immediately(): void
    {
        $provider = new Rinvex;

        $this->expectException(DefinitionNotFound::class);
        $this->expectExceptionMessage('The country definition could not be resolved for ISO alpha-2 code [XX].');

        $provider->resolve('XX');
    }

    public function test_rinvex_resolve_returns_package_definition_dto(): void
    {
        $definition = (new Rinvex)->resolve('UA');

        $this->assertInstanceOf(CountryDefinition::class, $definition);
        $this->assertSame('Ukraine', $definition->getName());
        $this->assertSame('Україна', $definition->getNativeName('ukr'));
        $this->assertSame('UKR', $definition->getIsoAlpha3());
        $this->assertSame('.ua', $definition->getTld());
        $this->assertSame('UAH', $definition->getCurrency('uah')['iso_4217_code'] ?? null);
        $this->assertSame(['common' => 'Ukraine', 'official' => 'Ukraine'], $definition->getTranslation('eng'));
        $this->assertSame('Kyïv', $definition->getDivision('30')['name'] ?? null);
    }
}

class FakeCountryProvider implements CountryDefinitionProvider
{
    public function resolve(string $isoAlpha2): CountryDefinitionContract
    {
        return new FakeCountryDefinition($isoAlpha2);
    }
}

class FakeCountryDefinition implements CountryDefinitionContract
{
    public function __construct(protected string $isoAlpha2) {}

    public function getName(): ?string
    {
        return 'Fake Ukraine';
    }

    public function getOfficialName(): ?string
    {
        return 'Fake Ukraine';
    }

    public function getNativeName(?string $languageCode = null): ?string
    {
        return 'Fake Ukraine';
    }

    public function getNativeOfficialName(?string $languageCode = null): ?string
    {
        return 'Fake Ukraine';
    }

    public function getNativeNames(): ?array
    {
        return [];
    }

    public function getDemonym(): ?string
    {
        return null;
    }

    public function getCapital(): ?string
    {
        return null;
    }

    public function getIsoAlpha2(): ?string
    {
        return strtoupper($this->isoAlpha2);
    }

    public function getIsoAlpha3(): ?string
    {
        return 'UKR';
    }

    public function getIsoNumeric(): ?string
    {
        return '804';
    }

    public function getCurrency(?string $currency = null): ?array
    {
        return null;
    }

    public function getCurrencies(): ?array
    {
        return [];
    }

    public function getTld(): ?string
    {
        return '.ua';
    }

    public function getTlds(): ?array
    {
        return ['.ua'];
    }

    public function getAltSpellings(): ?array
    {
        return [];
    }

    public function getLanguage(?string $languageCode = null): ?string
    {
        return null;
    }

    public function getLanguages(): ?array
    {
        return [];
    }

    public function getTranslations(): array
    {
        return ['eng' => ['common' => 'Fake Ukraine', 'official' => 'Fake Ukraine']];
    }

    public function getTranslation(?string $languageCode = null): array
    {
        return $this->getTranslations()['eng'];
    }

    public function getGeodata(): ?array
    {
        return [];
    }

    public function getContinent(): ?string
    {
        return 'Europe';
    }

    public function usesPostalCode(): ?bool
    {
        return true;
    }

    public function getLatitude(): ?string
    {
        return null;
    }

    public function getLongitude(): ?string
    {
        return null;
    }

    public function getLatitudeDesc(): ?string
    {
        return null;
    }

    public function getLongitudeDesc(): ?string
    {
        return null;
    }

    public function getMaxLatitude(): ?string
    {
        return null;
    }

    public function getMaxLongitude(): ?string
    {
        return null;
    }

    public function getMinLatitude(): ?string
    {
        return null;
    }

    public function getMinLongitude(): ?string
    {
        return null;
    }

    public function getArea(): ?int
    {
        return null;
    }

    public function getRegion(): ?string
    {
        return null;
    }

    public function getSubregion(): ?string
    {
        return null;
    }

    public function getWorldRegion(): ?string
    {
        return null;
    }

    public function getRegionCode(): ?string
    {
        return null;
    }

    public function getSubregionCode(): ?string
    {
        return null;
    }

    public function isLandlocked(): ?bool
    {
        return null;
    }

    public function getBorders(): ?array
    {
        return [];
    }

    public function isIndependent(): ?string
    {
        return null;
    }

    public function getCallingCode(): ?string
    {
        return null;
    }

    public function getCallingCodes(): ?array
    {
        return [];
    }

    public function getNationalPrefix(): ?string
    {
        return null;
    }

    public function getNationalNumberLength(): ?int
    {
        return null;
    }

    public function getNationalNumberLengths(): ?array
    {
        return [];
    }

    public function getNationalDestinationCodeLength(): ?int
    {
        return null;
    }

    public function getNationalDestinationCodeLengths(): ?array
    {
        return [];
    }

    public function getInternationalPrefix(): ?string
    {
        return null;
    }

    public function getExtra(): ?array
    {
        return [];
    }

    public function getGeonameId(): ?int
    {
        return null;
    }

    public function getEdgar(): ?string
    {
        return null;
    }

    public function getItu(): ?string
    {
        return null;
    }

    public function getMarc(): ?string
    {
        return null;
    }

    public function getWmo(): ?string
    {
        return null;
    }

    public function getDs(): ?string
    {
        return null;
    }

    public function getFifa(): ?string
    {
        return null;
    }

    public function getFips(): ?string
    {
        return null;
    }

    public function getGaul(): ?int
    {
        return null;
    }

    public function getIoc(): ?string
    {
        return null;
    }

    public function getCowc(): ?string
    {
        return null;
    }

    public function getCown(): ?int
    {
        return null;
    }

    public function getFao(): ?int
    {
        return null;
    }

    public function getImf(): ?int
    {
        return null;
    }

    public function getAr5(): ?string
    {
        return null;
    }

    public function getAddressFormat(): ?string
    {
        return null;
    }

    public function isEuMember(): ?bool
    {
        return null;
    }

    public function getDataProtection(): ?string
    {
        return null;
    }

    public function getVatRates(): ?array
    {
        return [];
    }

    public function getEmoji(): ?string
    {
        return null;
    }

    public function getGeoJson(): ?string
    {
        return null;
    }

    public function getFlag(): ?string
    {
        return null;
    }

    public function getDivisions(): ?array
    {
        return [];
    }

    public function getDivision(string $division): ?array
    {
        return null;
    }

    public function getTimezones(): ?array
    {
        return [];
    }
}

class UnavailableRinvexProvider implements CountryDefinitionProvider
{
    public function resolve(string $isoAlpha2): CountryDefinitionContract
    {
        throw DefinitionUnavailable::forPackage(
            self::class,
            'rinvex/countries',
        );
    }
}
