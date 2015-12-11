<?php
namespace Codeigniter\Migrations;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;


/**
 * Migration:Latest Class
 *
 * Calls Migration:run command with a predefined params and set the latest migration scheme version.
 *
 * @package     CLI Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2014, David Sosa Valdes.
 *
 */
class Latest extends Command
{
    protected function configure()
    {
        $this
            ->setName('migration:latest')
            ->setDescription('Run the latest migration')
            ->addOption(
                'module', 
                'm', 
                InputOption::VALUE_REQUIRED, 
                'Set the module name', 
                FALSE
            )
            ->addOption(
                'path',
                'p',
                InputOption::VALUE_REQUIRED,
                'Set the migration path',
                APPPATH
            )
            ->addOption(
                'environment',
                'e',
                InputOption::VALUE_REQUIRED,
                'Set the system environment',
                ENVIRONMENT
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
            '-e'      => $input->getOption('environment'),
        );  

        $input = new ArrayInput($arguments);
        $command->run($input, $output); 
    }
}