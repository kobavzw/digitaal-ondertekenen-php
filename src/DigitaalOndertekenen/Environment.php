<?php

declare(strict_types=1);

namespace Koba\DigitaalOndertekenen;

enum Environment
{
    case PRODUCTION;
    case TEST;

    public function getApiBaseUri(): string
    {
        return match ($this) {
            self::PRODUCTION => 'https://digitaal-ondertekenen-openapi.vlaanderen.be',
            self::TEST => 'https://digitaal-ondertekenen-openapi-ti.vlaanderen.be',
        };
    }

    public function getVlaanderenAccessTokenUrl(): string
    {
        return match ($this) {
            self::PRODUCTION => 'https://authenticatie.vlaanderen.be/op/v1/token',
            self::TEST => 'https://authenticatie-ti.vlaanderen.be/op/v1/token',
        };
    }

    public function getDigitaalOndertekenenAccessTokenUrl(): string
    {
        return match ($this) {
            self::PRODUCTION => 'https://digitaal-ondertekenen-openapi.vlaanderen.be/authenticate/single-sign-on',
            self::TEST => 'https://digitaal-ondertekenen-openapi-ti.vlaanderen.be/authenticate/single-sign-on',
        };
    }

    public function getDefaultProfileName(): string
    {
        return match ($this) {
            self::PRODUCTION => 'Beheerportaal PRD',
            self::TEST => 'beheerdersportaal_test',
        };
    }
}
