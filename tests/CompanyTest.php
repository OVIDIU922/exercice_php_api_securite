<?php

namespace App\Tests;

use App\Entity\Company;
use PHPUnit\Framework\TestCase;

class CompanyTest extends TestCase
{
    public function testCompanyCreation()
    {
        $company = new Company();
        $company->setName('Test Company');
        $company->setSiret('12345678901234');

        $this->assertEquals('Test Company', $company->getName());
        $this->assertEquals('12345678901234', $company->getSiret());
    }

    // Test pour valider que le SIRET a bien 14 chiffres
    public function testSiretValidation()
    {
        $company = new Company();
        $company->setSiret('12345678901234'); // Valeur valide

        $this->assertMatchesRegularExpression('/\d{14}/', $company->getSiret(), 'Le SIRET doit contenir 14 chiffres');
    }

    // Test pour valider un SIRET trop court
    public function testSiretTooShort()
    {
        $company = new Company();
        $company->setSiret('1234567890123'); // 13 chiffres

        $this->assertDoesNotMatchRegularExpression('/\d{14}/', $company->getSiret(), 'Le SIRET doit contenir exactement 14 chiffres');
    }

    // Test pour valider un SIRET trop long
    public function testSiretTooLong()
    {
        $company = new Company();
        $company->setSiret('123456789012345'); // 15 chiffres

        $this->assertDoesNotMatchRegularExpression('/\d{14}/', $company->getSiret(), 'Le SIRET doit contenir exactement 14 chiffres');
    }

    // Test pour valider un SIRET avec des caractères non numériques
    public function testSiretWithNonNumericCharacters()
    {
        $company = new Company();
        $company->setSiret('1234567890ABCD'); // Contient des lettres

        $this->assertDoesNotMatchRegularExpression('/\d{14}/', $company->getSiret(), 'Le SIRET ne doit contenir que des chiffres');
    }
}
