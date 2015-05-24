<?php 

namespace Codeigniter\Migrations;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;

use Symfony\Component\Process\Process;

use Exception;

/**
 * Migration:Run Class
 *
 * Principal class used as a default command for every other Migration class exept generate. 
 *
 * @package     CLI Craftsman
 * @author      David Sosa Valdes
 * @link        https://gitlab.com/david-sosa-valdes/craftsman
 * @copyright   Copyright (c) 2014, David Sosa Valdes.
 * @version     1.2.1
 *
 */
class Run extends Command
{
    /**
     * Set of all the posible works.
     * @var array
     */
    private $_valid_works = array(
        'current',
        'version',
        'latest',
        'info',
        'rollback',
        'reset',
        'refresh'
    );
    
    /**
     * Currently selected work.
     * @var string
     */
    private $_work;

    /**
     * Currently selected module (HMVC)
     * @var string
     */
    private $_module;

    /**
     * Set of possible Codeigniter CLI commands used
     * @var array
     */
    private $_commands = array(
        'module' => '/usr/bin/env php index.php climigration module',
        'default' => '/usr/bin/env php index.php climigration'
    );

    /**
     * Current migration version
     * @var integer
     */
    private $_version = 0;

    /**
     * Command configuration method.
     * 
     * Configure all the arguments and options.
     */
    protected function configure()
    {
        $this
            ->setName('migration:run')
            ->setDescription('Migration Run')
            ->addArgument(
                'work',
                InputArgument::REQUIRED,
                'Set the work name'
            )
            ->addArgument(
                'version',
                InputArgument::OPTIONAL,
                'Set current version',
                NULL
            )
            ->addOption(
                'module', 
                'm', 
                InputOption::VALUE_REQUIRED, 
                'Set the module name', 
                FALSE
            )
        ;
    }

    /**
     * Execute the command
     *
     * Run the migration work type for a specific module or default application.
     * 
     * @param  InputInterface  $input  
     * @param  OutputInterface $output 
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_work = $input->getArgument('work');
        $this->_version = $input->getArgument('version');
        $this->_module = $input->getOption('module');
        
        $output->writeln('<info>Work: '.$this->_work.'</info>');
        if (! in_array($this->_work, $this->_valid_works)) 
        {
            $output->writeln('<error>The work: '.$this->_work.' is not valid.</error>');
            return;
        }
        $this->_work == 'info' && $this->_work = 'index';
        if ($this->_module !== FALSE && is_string($this->_module)) 
        {   
            if ($this->_work == 'version') 
            {
                $this->_version = abs($this->_version);
                $output->writeln('<info>Version: '.$this->_version.'</info>');
                $command = $this->_commands["module"]." {$this->_module} {$this->_work} {$this->_version}";
            } 
            else 
            {
                $command = $this->_commands["module"]." {$this->_module} {$this->_work}";
            }
        }
        else
        {
            if ($this->_work == 'version') 
            {
                $this->_version = abs($this->_version);
                $output->writeln('<info>Version: '.$this->_version.'</info>');
                $command = $this->_commands["default"]." {$this->_work} {$this->_version}";
            } 
            else 
            {
                $command = $this->_commands["default"]." {$this->_work}";
            }
        }
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Continue with this action <comment>[yes]</comment>? ', TRUE);     
        if (!$helper->ask($input, $output, $question)) {
            return;
        }
        $process = new Process($command);
        $process->run();

        try {
            // executes after the command finishes
            if (!$process->isSuccessful()) {
                throw new \RuntimeException($process->getErrorOutput());
            }           
        } catch (\RuntimeException $e) {
            $output->writeln('<error>'.PHP_EOL.$e->getMessage().'</error>');
            return;
        }

       $output->writeln('<comment>'.PHP_EOL.$process->getOutput().'</comment>');     
    }   
}