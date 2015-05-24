<?php
namespace Codeigniter\Migrations;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

class Rollback extends Command
{
    protected function configure()
    {
        $this
            ->setName('migration:rollback')
            ->setDescription('Rollback the last migration operation')
            ->addOption(
                'module', 
                'm', 
                InputOption::VALUE_REQUIRED, 
                'Set the module name', 
                FALSE
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('migration:run'); 

        $arguments = array(
            'command' => 'migration:run',
            'work'    => 'rollback',
            '-m'      => $input->getOption('module'),
        );  

        $input = new ArrayInput($arguments);
        $command->run($input, $output); 
    }
}