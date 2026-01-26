<?php
/**
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */

namespace fsmaker;

class ApiGenerator
{
    public static function generate(): void
    {
        if (false === Utils::isPluginFolder()) {
            Utils::echo("* Esta no es la carpeta raíz del plugin.\n");
            return;
        }

        $name = Utils::prompt(
            label: 'Nombre del controlador de API',
            placeholder: 'Ej: CreateMultiInvoices',
            hint: 'El nombre del controlador de API debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.',
            regex: '/^[A-Z][a-zA-Z0-9_]*$/',
            errorMessage: 'Inválido, debe empezar por mayúscula y solo puede contener letras, números y guiones bajos.'
        );

        $file_path = 'Controller/' . $name . '.php';
        if (empty($name)) {
            return;
        } elseif (file_exists($file_path)) {
            Utils::echo("* El controlador " . $name . " YA EXISTE.\n");
            return;
        }

        $endpoint = Utils::prompt(
            label: 'Endpoint de la API',
            placeholder: 'Ej: /api/3/create-multi-invoices',
            hint: 'El nombre del endpoint de la API debe comenzar con /api/3/ y tener solo letras, números, guiones o barras.',
            regex: '/^\/api\/3\/[a-zA-Z0-9_\/-]*$/',
            errorMessage: 'Inválido, debe comenzar con /api/3/ y tener solo letras, números, guiones o barras.'
        );

        $sample = file_get_contents(Utils::getFolder() . "/samples/ApiController.php.sample");
        $template = str_replace(['[[NAME_SPACE]]', '[[NAME]]'], [Utils::getNamespace(), $name], $sample);
        Utils::createFolder('Controller');
        file_put_contents($file_path, $template);
        Utils::echo('* ' . $file_path . " -> OK.\n");

        $use = "use FacturaScripts\Core\Controller\ApiRoot;\n"
            . "use FacturaScripts\Core\Kernel;";
        $new_content = InitEditor::addUse($use);
        if ($new_content) {
            InitEditor::setInitContent($new_content);
        }

        $code = "Kernel::addRoute('" . $endpoint . "', '" . $name . "', -1);\n"
            . "ApiRoot::addCustomResource('" . substr($endpoint, 7) . "');";

        $new_content = InitEditor::addToInitFunction($code, true);
        if ($new_content) {
            InitEditor::setInitContent($new_content);
        }
    }
}
