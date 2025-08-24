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
            'Nombre del controlador de API (ejemplo: CreateMultiInvoices)',
            '/^[A-Z][a-zA-Z0-9_]*$/',
            'empezar por mayúscula y sin espacios'
        );
        $file_path = 'Controller/' . $name . '.php';
        if (empty($name)) {
            return;
        } elseif (file_exists($file_path)) {
            Utils::echo("* El controlador " . $name . " YA EXISTE.\n");
            return;
        }

        $endpoint = Utils::prompt(
            'Endpoint de la API (ejemplo: /api/3/create-multi-invoices)',
            '/^\/api\/3\/[a-zA-Z0-9_\/-]*$/',
            'comenzar con /api/3/ y tener solo letras, números, guiones o barras'
        );
        if (empty($endpoint)) {
            return;
        }

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
