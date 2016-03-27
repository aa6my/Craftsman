<?php
namespace Craftsman\Classes;

use Craftsman\Commands\Base as Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base Migration Class
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 * @version     1.0.0
 */
class Migration extends Command
{
    /**
     * Codeigniter migration class.
     * @var object
     */
    protected $migration;

    /**
     * Harmless mode - without confirm the action.
     * @var boolean
     */
    protected $harmless = FALSE;

    /**
     * Constructor
     * 
     * @param Craftsman_Migration $Model
     */
    public function __construct(\CI_Controller $CI)
    {
        parent::__construct();
        /**
         * We need to load the special migration settings.
         * 
         * Config file: 
         *  src/Extend/config/migration.php
         */
        $CI->config->load('migration', TRUE, TRUE);
        $this->CI = &$CI;
    }

    /**
     * Command configuration method.
     * 
     * Configure all the arguments and options.
     */
    protected function configure()
    {
    	parent::configure();
    	
        $this
            ->addOption(
                'name', 
                NULL, 
                InputOption::VALUE_REQUIRED, 
                'Set the migration version name', 
                'ci_system'
            )
            ->addOption(
                'path',
                NULL,
                InputOption::VALUE_REQUIRED,
                'Set the migration path',
                'application/migrations/'
            )
            ->addOption(
                'timestamp',
                NULL,
                InputOption::VALUE_NONE,
                'If set, the migration will run with timestamp mode active'
            );    
    }

    /**
     * Execute the Migration command
     * 
     * @param  InputInterface  $input  [description]
     * @param  OutputInterface $output [description]
     * @return [type]                  [description]
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (! $this->harmless) 
        {
            $message = 'WARNING! You are about to execute a database migration that could '
                .'result in schema changes and data lost. Do you wish to continue?';
            
            if (! $this->confirm($message)) 
            {
                $this->error('Process aborted!');
                exit(3);
            }           
        }
        if ($params = $this->CI->config->item('migration')) 
        {
            ($this->getOption('timestamp') !== FALSE) && $params['migration_type'] = 'timestamp';
            $this->CI->load->library('migration', $params);
            $this->migration = $this->CI->migration;
        }
        else
        {
            throw new \RuntimeException("Craftsman migration settings does not appear to set correctly.");
        }        
        $this->setModelArguments();
        parent::execute($input, $output);
    }

    /**
     * Set Codeigniter Craftsman Migration Library arguments
     */
    protected function setModelArguments()
    {
        return $this->migration->set_params(array(
            'module_path' => rtrim($this->getOption('path'),'/').'/',
            'module_name' => strtolower($this->getOption('name'))
        ));
    }

    /**
     * Measure the queries execution time and show in console
     * 
     * @param  array   $queries         CI Database queries
     * @param  boolean $show_in_console Hide/Show in the console mode
     * @return array                    Array of total exec time and the
     *                                  amount of queries.
     */
    protected function measureQueries(array $queries, $show_in_console = TRUE)
    {
        $migration_table = $this->migration->getTable();
        $query_exec_time = 0;
        $exec_queries    = 0;

        for ($i = 2; $i < count($queries); $i++) 
        {
            if (! strpos($queries[$i], $migration_table)) 
            {
                ($show_in_console !== FALSE) && $this->text('<comment>-></comment> '.$queries[$i]);
                $query_exec_time += $this->migration->db->query_times[$i]; 
                $exec_queries += 1;     
            }
        }
        return array($query_exec_time, $exec_queries);        
    }

    /**
     * Display in the console all the processes
     * 
     * @param  string $signal           Migration command signal (++,--)
     * @param  float  $time_start       Unix timestamp with microseconds from the start of process
     * @param  float  $time_end         Unix timestamp with microseconds from the end of process
     * @param  float  $query_exec_time  Queries execution time in seconds
     * @param  int    $exec_queries     Amount of executed queries
     */
    protected function summary($signal, $time_start, $time_end, $query_exec_time, $exec_queries)
    {
        $this->newLine();
        $this->text('<info>'.$signal.'</info> query process ('.number_format($query_exec_time, 4).'s)');
        $this->newLine();
        $this->text('<comment>'.str_repeat('-', 30).'</comment>');
        $this->newLine();
        $execution_time = ($time_end - $time_start);
        $this->text('<info>++</info> finished in '. number_format($execution_time, 4).'s');
        $this->text('<info>++</info> '.$exec_queries.' sql queries');        
    }
}
