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
 * @package     CLI Craftsman
 * @author      David Sosa Valdes
 * @link        https://gitlab.com/david-sosa-valdes/craftsman
 * @copyright   Copyright (c) 2014, David Sosa Valdes.
 * @version     1.2.1
 */
class Info extends Command
{
    protected function configure()
    {
        $this
            ->setName('migration:reset')
            ->setDescription('Rollback all migrations and run them all again')
            ->addOption(
                'module', 
                'm', 
                InputOption::VALUE_REQUIRED, 
                'Set the module name', 
                FALSE
            )
            ->addOption(
               'ci-route',
               'cr',
               InputOption::VALUE_OPTIONAL,
               'If you are using a secure installation of CI, set where can i find the index.php script.'
            );            
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('migration:run'); 

        $arguments = array(
            'command' => 'migration:run',
            'work'    => 'info',
            '-m'      => $input->getOption('module'),
        );  

        $input = new ArrayInput($arguments);
        $command->run($input, $output); 
    }
}