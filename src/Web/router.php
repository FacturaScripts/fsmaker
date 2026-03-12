<?php
declare(strict_types=1);

$projectRoot = dirname(__DIR__, 2);
require $projectRoot . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use fsmaker\Column;
use fsmaker\FileGenerator;
use fsmaker\Utils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$request = Request::createFromGlobals();
$response = new Response();

$command = $request->request->get('command');
if ($command) {
    switch ($command) {
        case 'crear-modelo':

            $nombreModelo = $request->request->get('nombreModelo');
            $nombreTabla = $request->request->get('nombreTabla');

            $fields[] = new Column([
                'nombre' => 'campo1',
                'tipo' => 'character varying',
                'longitud' => 50
            ]);


            $filePath = getcwd() . '/' . (Utils::isCoreFolder() ? 'Core/Model/' : 'Model/');
            $filePath = str_replace('/', DIRECTORY_SEPARATOR, $filePath);
            $fileName = $filePath . $nombreModelo . '.php';
            Utils::createFolder($filePath);
            if (file_exists($fileName)) {
                // TODO devolver error por ajax
            }

            FileGenerator::createModelByFields($fileName, $nombreTabla, $fields, $nombreModelo, Utils::getNamespace());

            $response->setContent(json_encode(['success' => true]));
            $response->headers->set('Content-Type', 'application/json');
            $response->send();
            return;
    }
}

$response->setContent(file_get_contents(__DIR__ . '/public/index.html'));
$response->send();
