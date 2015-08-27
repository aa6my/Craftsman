<?php
namespace Codeigniter\Cerberus;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Cerberus:Migration Class
 *
 * Calls Migration:run command and update the Cerberus migration scheme.
 *
 * @package     CLI Craftsman
 * @author      David Sosa Valdes
 * @link        https://gitlab.com/david-sosa-valdes/Cerberus
 * @copyright   Copyright (c) 2014, David Sosa Valdes.
 * @version     1.0.0
 */
class Migration extends Command
{
    protected function configure()
    {
        $this
            ->setName('cerberus:migration')
            ->setDescription('Calls Migration:run command and update the Cerberus migration scheme')
            ->addOption(
                'module', 
                'm', 
                InputOption::VALUE_REQUIRED, 
                'Set the HMVC module name', 
                'cerberus'
            )
            ->addOption(
                'path',
                'p',
                InputOption::VALUE_REQUIRED,
                'Set the migration path',
                'application/libraries/Cerberus/migrations/'
            );                     
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('migration:run'); 

        $arguments = array(
            'command' => 'migration:run',
            'work'    => 'latest',
            '-m'      => $input->getOption('module'),
            '-p'      => $input->getOption('path'),
        );  

        $input = new ArrayInput($arguments);
        $command->run($input, $output); 
    }
}