<?php
/**
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */

namespace fsmaker;

class UpdateTranslations
{
    const LANGUAGES_URL = 'https://facturascripts.com/traducciones?action=json-languages';
    const TRANSLATIONS = 'ca_ES,cs_CZ,de_DE,en_EN,es_AR,es_CL,es_CO,es_CR,es_DO,es_EC,es_ES,es_GT,es_MX,es_PA,es_PE,es_UY,eu_ES,fr_FR,gl_ES,it_IT,pl_PL,pt_BR,pt_PT,tr_TR,va_ES';

    public static function create(string $name): void
    {
        foreach (explode(',', self::TRANSLATIONS) as $filename) {
            file_put_contents(
                $name . '/Translation/' . $filename . '.json',
                '{"' . strtolower($name) . '": "' . $name . '"}'
            );
            Utils::echo('* ' . $name . '/Translation/' . $filename . ".json -> OK.\n");
        }
    }

    /**
     * Devuelve los códigos de idioma publicados en facturascripts.com.
     * Si la petición falla o la respuesta no es válida, usa la lista fija TRANSLATIONS.
     */
    public static function getLanguages(string $url = self::LANGUAGES_URL): array
    {
        $context = stream_context_create(['http' => ['timeout' => 10]]);
        $languages = self::parseLanguages(@file_get_contents($url, false, $context));
        if (!empty($languages)) {
            return $languages;
        }

        Utils::echo("* No se ha podido obtener la lista de idiomas de facturascripts.com, se usa la lista predeterminada.\n");
        return explode(',', self::TRANSLATIONS);
    }

    /**
     * Extrae los códigos de idioma del JSON {"ca_ES": "Catalán (España)", ...}.
     * Devuelve un array vacío si el JSON no es un objeto válido.
     */
    public static function parseLanguages(string|false $json): array
    {
        if (empty($json)) {
            return [];
        }

        $data = json_decode($json, true);
        if (!is_array($data) || array_is_list($data)) {
            return [];
        }

        $languages = [];
        foreach (array_keys($data) as $code) {
            if (preg_match('/^[a-zA-Z0-9_-]{1,8}$/', (string)$code)) {
                $languages[] = (string)$code;
            }
        }
        return $languages;
    }

    public static function run(): void
    {
        if (Utils::isPluginFolder()) {
            $folder = 'Translation/';
            Utils::createFolder($folder);
            $ini = parse_ini_file('facturascripts.ini');
            $project = $ini['name'] ?? '';
        } elseif (Utils::isCoreFolder()) {
            $folder = 'Core/Translation/';
            Utils::createFolder($folder);
            $project = 'CORE';
        } else {
            Utils::echo("* Esta no es la carpeta raíz del plugin.\n");
            return;
        }

        if (empty($project)) {
            Utils::echo("Proyecto desconocido.\n");
            return;
        }

        // descargamos el json de facturascripts.com
        foreach (self::getLanguages() as $filename) {
            // esperamos medio segundo entre peticiones
            usleep(500000);

            Utils::echo("D " . $folder . $filename . ".json");
            $url = "https://facturascripts.com/EditLanguage?action=json&project=" . $project . "&code=" . $filename;
            $json = file_get_contents($url);
            if (!empty($json) && strlen($json) > 10) {
                file_put_contents($folder . $filename . '.json', $json);
                Utils::echo("\n");
                continue;
            }

            Utils::echo(" - vacío\n");
        }
    }
}
