<?php
namespace Codeigniter\Migrations;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;


/**
 * Migration:Reset Class
 *
 * Calls Migration:run command with a predefined params and reset all the migration schemes.
 *
 * @package     CLI Craftsman
 * @author      David Sosa Valdes
 * @link        https://gitlab.com/david-sosa-valdes/craftsman
 * @copyright   Copyright (c) 2014, David Sosa Valdes.
 * @version     1.2.1
 *
 */
class Reset extends Command
{
    protected function configure()
    {
        $this
            ->setName('migration:reset')
            ->setDescription('Rollback all migrations')
            ->addOption(
                'module', 
                'm', 
                InputOption::VALUE_REQUIRED, 
                'Set the HMVC module name', 
                FALSE
            )
            ->addOption(
                'path',
                'p',
                InputOption::VALUE_REQUIRED,
                'Set the migration path',
                FALSE
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
            'work'    => 'reset',
            '-m'      => $input->getOption('module'),
            '-p'      => $input->getOption('path'),
            '-e'      => $input->getOption('environment'),            
        );  

        $input = new ArrayInput($arguments);
        $command->run($input, $output); 
    }
}