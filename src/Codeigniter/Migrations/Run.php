<?php 
namespace Codeigniter\Migrations;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Run Command
 *
 * This is the main migration command that runs the CodeIgniter migration class.
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2015, David Sosa Valdes.
 * @version     1.3.0
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
     * CodeIgniter migration class.
     * @var object
     */
    protected $_ci_migration;

    /**
     * Constructor
     * 
     * @param object Craftsman_Migration class
     */
    public function __construct($CI_Migration_Class)
    {
        parent::__construct();
        $this->_ci_migration = $CI_Migration_Class;
    }

    /**
     * Command configuration method.
     * 
     * Configure all the arguments and options.
     */
    protected function configure()
    {
        $this
            ->setName('migration:run')
            ->setDescription('Main migration command that runs the CodeIgniter migration class')
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
        $output->getFormatter()->setStyle(
            'title', 
            new OutputFormatterStyle('cyan', 'black', array('bold'))
        );
        $output->getFormatter()->setStyle(
            'action', 
            new OutputFormatterStyle('cyan', 'black', array('bold'))
        );        

        $work        = strtolower($input->getArgument('work'));
        $version     = $input->getArgument('version');
        $module      = strtolower($input->getOption('module'));
        $environment = strtolower($input->getOption('environment'));
        $path        = rtrim($input->getOption('path'),'/').'/';
        
        $output->writeln("<title> -- Craftsman -- </title>");

        $helper = $this->getHelper('question');    

        if (! in_array($work, $this->_valid_works)) 
        {
            throw new \RuntimeException('The work: ['.$work.'] is not valid.');
        }
        elseif ($work == 'version') 
        {
            if ($version == NULL) 
            {
                throw new \RuntimeException("You're gonna need to specify a version.");
            }
        }
        // Set CI Migration params.
        $this->_ci_migration->set_params([
            'module_path' => $path,
            'module_name' => empty($module)? 'ci_system': $module
        ]);      
        // Get all available migration files
        $migrations = $this->_ci_migration->find_migrations();
        // Set all version status types
        $latest_file_version   = $this->_ci_migration->get_latest_version($migrations);
        $latest_db_version     = $this->_ci_migration->get_db_version();
        $latest_config_version = $this->_ci_migration->get_config_version();
        $rollback_version      = 0;

        if ($work === 'rollback') 
        {
            array_walk($migration_keys = array_keys($migrations), 
                function(&$item, $key) {
                    $item = abs($item);
                }
            );

            if (count($migration_keys) >= 1) 
            {
                end($migration_keys);
                $rollback_version = prev($migration_keys);
                while ($rollback_version >= $latest_db_version) 
                {
                    if ((! $rollback_version) || $rollback_version === 1) 
                    {
                        $rollback_version = 0;
                        break;
                    }
                    $rollback_version = prev($migration_keys);
                }
            }
            else
            {
                $rollback_version = reset($migration_keys);
            }
        }         

        $info = array(
            'info'     => array('Info Mode','Viewer'),
            'current'  => array('Config Version', $latest_config_version),
            'latest'   => array('Latest File Version', $latest_file_version),
            'version'  => array('File Version', $version),
            'reset'    => array('Reset Version', 0),
            'rollback' => array('Rollback Version', $rollback_version),
            'refresh'  => array('Refresh Version', $latest_file_version)
        );
        list($work_field, $work_version) = $info[$work];     
        
        if ($work == 'refresh') 
        {
            $current_version = 'Up';
        }
        elseif ($work == 'info') 
        {
            $current_version = 'None';
        }
        else
        {
            $current_version = $this->_set_migration_status($latest_db_version, $work_version);
        }     
        
        $table = new Table($output); 
        $table
            ->setStyle((new TableStyle)->setCellHeaderFormat('<action>%s</action>'))
            ->setHeaders(['Config','Value'])
            ->setRows([
                ['Work', $work],
                ['Environment', $environment],
                ['Module', $this->_ci_migration->get_module_name()],
                ['DB Version', $latest_db_version],
                new TableSeparator,
                ['Path', $this->_ci_migration->get_module_path()],
                new TableSeparator,
                [$work_field, '<action>'.$work_version.'</action>'],
                ['Action', '<action>'.$current_version.'</action>'],
            ])
            ->render(); 

        if ($work !== 'info') 
        {
            $message = 'Continue with this action <comment>[yes]</comment>? ';
            $question = new ConfirmationQuestion($message, TRUE);    
            if (! $helper->ask($input, $output, $question))
            { 
                return;
            } 
            if (
                $environment === 'production' 
                && $latest_db_version > $current_version
            ) {
                throw new \RuntimeException("It's not possible rollback from {$latest_db_version}"
                    . " to {$current_version} in a PRODUCTION environment,"
                    ." you can change it with '-e' argument.");
                
            }              
        }
        if (empty($migrations) && $work !== 'info') 
        {
            throw new \RuntimeException("No migrations were found.");
        }
        switch ($work) 
        {
            case 'current':
                $this->_ci_migration->current();
                break;
            case 'latest':
                $this->_ci_migration->latest();
                break;
            case 'version':
                $this->_ci_migration->version($version);
                break;
            case 'reset':
                $this->_ci_migration->version(0);
                break;
            case 'rollback':
                if ($latest_db_version <= 0) 
                {
                    throw new \RuntimeException("Not posible to rollback from 0.");
                }
                else
                {
                    $this->_ci_migration->version($rollback_version);
                }
                break;
            case 'refresh':
                $this->_ci_migration->version(0);
                $this->_ci_migration->latest();
                break;
        }
        if ($latest_db_version == $work_version) 
        {
            if ($work !== 'info') 
            {
                $output->writeln('<info>SUCCESS</info>');
            }
        }
        elseif (! empty($this->_ci_migration->error_string())) 
        {
            throw new \RuntimeException($this->_ci_migration->error_string());
        }
    }  

    /**
     * Set migration status depending on which work is being done.
     * 
     * @param integer $db_version      Current DB migration version
     * @param integer $current_version Current version 
     *
     * @return string Migration status
     */
    private function _set_migration_status($db_version = 0, $current_version = 0)
    {
        return ($db_version == $current_version)
            ? 'Not change'
            : ($current_version > $db_version)? "Up" : "Down";
    }
}