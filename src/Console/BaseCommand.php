<?php
/**
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */

namespace fsmaker\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use fsmaker\Utils;

abstract class BaseCommand extends Command
{
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        // Inicializar Utils con el directorio de trabajo actual y el output de Symfony
        Utils::setFolder(getcwd());
        Utils::setOutput($output);
    }

    /**
     * Verifica que estamos en un directorio de plugin o core de FacturaScripts.
     * Muestra un mensaje de error si no lo es.
     */
    protected function requirePluginOrCore(): bool
    {
        if (!Utils::isPluginFolder() && !Utils::isCoreFolder()) {
            Utils::echo("* Esta no es la carpeta raíz del plugin.\n");
            return false;
        }
        return true;
    }
}
