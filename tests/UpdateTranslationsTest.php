<?php

namespace fsmaker\Tests;

use fsmaker\UpdateTranslations;

/**
 * Tests de la lista de idiomas que usa el comando translations: la lectura del JSON
 * publicado en facturascripts.com y el respaldo con la lista fija TRANSLATIONS.
 *
 * Usa archivos locales en el directorio temporal de CommandTestCase en lugar de la URL real,
 * para no depender de la red.
 */
final class UpdateTranslationsTest extends CommandTestCase
{
    public function testParseLanguagesReturnsObjectKeys(): void
    {
        $json = '{"ca_ES": "Catalán (España)", "es_ES": "Español (España)", "nl_NL": "Neerlandés (Países Bajos)"}';
        $this->assertSame(['ca_ES', 'es_ES', 'nl_NL'], UpdateTranslations::parseLanguages($json));
    }

    public function testParseLanguagesSkipsInvalidCodes(): void
    {
        $json = '{"es_ES": "Español", "../etc": "x", "demasiado_largo": "x", "a b": "x", "": "x", "pt-BR": "Portugués"}';
        $this->assertSame(['es_ES', 'pt-BR'], UpdateTranslations::parseLanguages($json));
    }

    public function testParseLanguagesRejectsInvalidResponses(): void
    {
        $this->assertSame([], UpdateTranslations::parseLanguages(false));
        $this->assertSame([], UpdateTranslations::parseLanguages(''));
        $this->assertSame([], UpdateTranslations::parseLanguages('<!DOCTYPE html><html></html>'));
        $this->assertSame([], UpdateTranslations::parseLanguages('{}'));
        $this->assertSame([], UpdateTranslations::parseLanguages('["es_ES", "en_EN"]'));
        $this->assertSame([], UpdateTranslations::parseLanguages('"es_ES"'));
    }

    public function testGetLanguagesUsesDownloadedList(): void
    {
        file_put_contents('languages.json', '{"es_ES": "Español (España)", "nl_NL": "Neerlandés (Países Bajos)"}');
        $this->assertSame(['es_ES', 'nl_NL'], UpdateTranslations::getLanguages($this->testDir . '/languages.json'));
    }

    public function testGetLanguagesFallsBackToFixedList(): void
    {
        $fallback = explode(',', UpdateTranslations::TRANSLATIONS);

        // archivo inexistente: la petición falla
        $this->assertSame($fallback, UpdateTranslations::getLanguages($this->testDir . '/no-existe.json'));

        // respuesta que no es un objeto JSON
        file_put_contents('languages.html', '<!DOCTYPE html><html></html>');
        $this->assertSame($fallback, UpdateTranslations::getLanguages($this->testDir . '/languages.html'));
    }
}
