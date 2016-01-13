<?php
namespace Codeigniter\Migrations;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;


/**
 * Migration:Info Class
 *
 * Calls Migration:run info command and display the current migration information
 * of application/hmvc object.
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2014, David Sosa Valdes.
 */
class Info extends Command
{
    protected function configure()
    {
        $this
            ->setName('migration:info')
            ->setDescription('Display the current migration information of app or hmvc module')
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
            );                     
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('migration:run'); 

        $arguments = array(
            'command' => 'migration:run',
            'work'    => 'info',
            '-m'      => $input->getOption('module'),
            '-p'      => $input->getOption('path'),
        );  

        $input = new ArrayInput($arguments);
        $command->run($input, $output); 
    }
}