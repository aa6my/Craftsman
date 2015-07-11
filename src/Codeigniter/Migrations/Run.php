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
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\TableStyle;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
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
     * Current migration version
     * @var integer
     */
    private $_version = 0;

    /**
     * [$_migration_class description]
     * @var [type]
     */
    protected $_migration_class;

    /**
     * [__construct description]
     * @param object $CI_Migration_Class [description]
     */
    public function __construct($CI_Migration_Class)
    {
        parent::__construct();
        $this->_migration_class = $CI_Migration_Class;
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
        $style = new OutputFormatterStyle('cyan', 'black', array('bold'));
        $output->getFormatter()->setStyle('title', $style);

        $style = new OutputFormatterStyle('cyan', 'black', array('bold'));
        $output->getFormatter()->setStyle('fire', $style);        

        $this->_work    = strtolower($input->getArgument('work'));
        $this->_version = $input->getArgument('version');
        $this->_module  = $input->getOption('module');
        
        $helper = $this->getHelper('question');
        
        $output->writeln("<title> -- Craftsman Migration -- </title>");

        if (! in_array($this->_work, $this->_valid_works)) 
        {
            $output->writeln('<error>The work: '.$this->_work.' is not valid.</error>');
            return;
        }

        if ($this->_work == 'version') 
        {
            if ($this->_version == NULL) 
            {
                $output->writeln("<error>You're gonna need to specify a version</error>");
                return;
            }
            else
            {
                $this->_version = abs($this->_version);
            }
        }       

        $migrations            = $this->_migration_class->find_migrations();
        $latest_file_version   = abs($this->_migration_class->get_number(abs(basename(end($migrations)))));
        $latest_db_version     = abs($this->_migration_class->get_db_version());
        $latest_config_version = abs($this->_migration_class->get_current_config_version());
        $rollback_version      = 0;

        if ($this->_work === 'rollback') 
        {
            $migration_keys = array_keys($this->_migration_class->find_migrations());
            array_walk($migration_keys, function(&$item, $key){$item = abs($item);});
            if (count($migration_keys) > 1) 
            {
                end($migration_keys);
                $rollback_version = prev($migration_keys);
                while ($rollback_version >= $latest_db_version) 
                {
                    if ($rollback_version === FALSE || $rollback_version === 1) 
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
            'info' => array(
                'Info Mode',
                '---'
            ),
            'current' => array(
                'Config Version',
                $latest_config_version
            ),
            'latest' => array(
                'Latest File Version',
                $latest_file_version
            ),
            'version' => array(
                'File Version',
                $this->_version
            ),
            'reset' => array(
                'Reset Version',
                0
            ),
            'rollback' => array(
                'Rollback Version',
                $rollback_version
            ),
            'refresh' => array(
                'Refresh Version',
                $latest_file_version
            )
        );
        list($work_field,$work_version) = $info[$this->_work];     
        
        if ($this->_work == 'refresh') 
        {
            $current_version = 'Up';
        }
        elseif ($this->_work == 'info') 
        {
            $current_version = 'None';
        }
        else
        {
            $current_version = $this->_set_migration_status($latest_db_version, $work_version);
        }

        if ($this->_module !== FALSE) 
        {
            $this->_migration_class->set_params([
                'module_name' => $this->_module
            ]);
        }        
        
        $table = new Table($output); 

        $style = new TableStyle();
        $style->setCellHeaderFormat('<fire>%s</fire>');

        $table->setStyle($style);

        $table
            ->setHeaders(['Config','Value'])
            ->setRows([
                ['Work', $this->_work],
                ['Environment', ENVIRONMENT],
                ['Module', $this->_migration_class->get_module_name()],
                ['DB Version', $latest_db_version],
                new TableSeparator,
                ['Path', $this->_migration_class->get_module_path()],
                new TableSeparator,
                [$work_field, '<fire>'.$work_version.'</fire>'],
                ['Action', '<fire>'.$current_version.'</fire>'],
            ])
            ->render(); 
        if ($this->_work !== 'info') 
        {
            $message = 'Continue with this action <comment>[yes]</comment>? ';
            $question = new ConfirmationQuestion($message, TRUE);    
            if (! $helper->ask($input, $output, $question))
            { 
                return;
            } 
            if (
                ENVIRONMENT === 'production' 
                && $latest_db_version > $current_version
            ) {
                $output->writeln("<error>It's not possible rollback from {$latest_db_version} to {$current_version} in 'PRODUCTION' environment.</error>");
                return;
            }              
        }
        if (empty($migrations)) 
        {
            $output->writeln('<error>No migrations were found</error>');
            return;
        }              
        
        $response = FALSE;
        switch ($this->_work) 
        {
            case 'current':
                $this->_migration_class->current() && $response = TRUE;
                break;
            case 'latest':
                $this->_migration_class->latest() && $response = TRUE;
                break;
            case 'version':
                $this->_migration_class->version($this->_version) && $response = TRUE;
                break;
            case 'reset':
                $this->_migration_class->version(0) && $response = TRUE;
                break;
            case 'rollback':
                if ($latest_db_version <= 0) 
                {
                    $output->writeln("<error>Not posible to rollback from 0</error>");
                    return;
                }
                else
                {
                    $this->_migration_class->version($rollback_version) && $response = TRUE;
                }
                break;
            case 'refresh':
                $this->_migration_class->version(0);
                $this->_migration_class->latest() && $response = TRUE;
                break;
        }
        if ($response !== FALSE || ($latest_db_version == $work_version)) 
        {
            if ($this->_work !== 'info') 
            {
                $output->writeln('<info>SUCCESS</info>');
            }
        }
        else
        {
            $output->writeln('<error>'.$this->_migration_class->error_string().'</error>');
        }
        return;
    }  

    /**
     * [_set_migration_status description]
     * @param integer $db_version      [description]
     * @param integer $current_version [description]
     */
    private function _set_migration_status($db_version = 0, $current_version = 0)
    {
        if ($db_version == $current_version) 
        {
            return 'Not change';
        }
        return ($current_version > $db_version)? "Up" : "Down";
    }
}