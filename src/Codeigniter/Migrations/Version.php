<?php
namespace Codeigniter\Migrations;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;


/**
 * Migration:Version Class
 *
 * Calls Migration:run command with a predefined params and set the migration scheme version.
 *
 * @package     CLI Craftsman
 * @author      David Sosa Valdes
 * @link        https://gitlab.com/david-sosa-valdes/craftsman
 * @copyright   Copyright (c) 2014, David Sosa Valdes.
 * @version     1.0.0
 *
 */
class Version extends Command
{
    protected function configure()
    {
        $this
            ->setName('migration:version')
            ->setDescription('Run a migration version')
            ->addOption(
                'module', 
                'm', 
                InputOption::VALUE_REQUIRED, 
                'Set the HMVC module name', 
                FALSE
            )
            ->addArgument(
                'version',
                InputArgument::OPTIONAL,
                'Set current version',
                NULL
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
            'work'    => 'version',
            'version' => $input->getArgument('version'),
            '-m'      => $input->getOption('module'),
            '-e'      => $input->getOption('environment')
        );  

        $input = new ArrayInput($arguments);
        $command->run($input, $output); 
    }
}