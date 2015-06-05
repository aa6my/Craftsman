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
use Symfony\Component\Console\Helper\Table;

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
        'module' => '/usr/bin/env php {ci_instance} climigration module',
        'default' => '/usr/bin/env php {ci_instance} climigration'
    );

    private $_ci_instance = 'index.php';

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
            ->addOption(
               'ci-route',
               'cr',
               InputOption::VALUE_OPTIONAL,
               'If you are using a secure installation of CI, set where can i find the index.php script.'
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
        $this->_work    = $input->getArgument('work');
        $this->_version = $input->getArgument('version');
        $this->_module  = $input->getOption('module');
        
        $output->writeln(PHP_EOL."Craftsman Migration");
        $output->writeln(str_repeat("--", 10));

        $output->writeln('Work: <info>'.$this->_work.'</info>');
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
                
                $command = $this->_commands["default"]." {$this->_work} {$this->_version}";
            } 
            else 
            {
                $command = $this->_commands["default"]." {$this->_work}";
            }
        }
       if ($this->_work == 'version') 
       {
         $output->writeln('Version: <info>'.$this->_version.'</info>');
       }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Continue with this action <comment>[yes]</comment>? ', TRUE);     
        if (!$helper->ask($input, $output, $question)) {
            return;
        }
        if ($input->getOption('ci-route')) 
        {
            $this->_ci_instance = $input->getOption('ci-route');    
        }

        try {
            if (! file_exists($this->_ci_instance)) 
            {
                throw new Exception("Trying to run 'php {$this->_ci_instance}'. CI instance is not declared properly, set with --ci-route.");
            }
            else
            {
                $command = str_replace('{ci_instance}', $this->_ci_instance, $command);
            }
        } catch (Exception $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');
            return;
        }
        $process = new Process($command);
        $process->run();
        $cli_output = $process->getOutput();
        try {
            // executes after the command finishes
            if (!$process->isSuccessful()) {
                throw new \RuntimeException($process->getErrorOutput());
            }           
        } catch (\RuntimeException $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');
            return;
        }

        if ($this->_work == 'index') 
        {
            $table = new Table($output);    

            $output_lines = explode("\n", $cli_output);
            for ($i=0; $i < count($output_lines); $i++) { 
                $output_lines[$i] = explode(': ',$output_lines[$i]);
            }   

            $table
                ->setHeaders(array('Info','Value'))
                ->setRows($output_lines);
            $table->render();                  
        }      
        else
        { 
            $output->writeln(PHP_EOL.$cli_output); 
        }    
    }   
}