<?php 
namespace Codeigniter\Migrations;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

use Twig_Autoloader;
use Twig_Loader_Filesystem;
use Twig_Environment;

use Exception;

/**
 * Migration:Generate Class
 *
 *
 * @package     CLI Craftsman
 * @author      David Sosa Valdes
 * @link        https://gitlab.com/david-sosa-valdes/craftsman
 * @copyright   Copyright (c) 2014, David Sosa Valdes.
 * @version     1.2.1
 *
 */
class Generate extends Command
{
    /**
     * Available migration folder.
     * @var string
     */
    private $_default_folder = 'migrations';

    /**
     * Default migrations folder path.
     * @var string
     */
    private $_path = 'application/migrations/';

    /**
     * Set of migrations available inside the Migration Filesystem.
     * @var array
     */
    private $_migrations = array();

    /**
     * New Migration Filename 
     * @var string
     */
    private $_filename;

    /**
     * New Migration name (without extension and prefix)
     * @var string
     */
    private $_name;

    /**
     * Migration Type (numeric,timestamp) 
     * @var string
     */
    private $_type;

    /**
     * [$_migration_regex description]
     * @var array
     */
    private $_migration_regex = array(
        '/^\d{14}_(\w+)$/',
        '/^\d{3}_(\w+)$/' 
    );

    /**
     * Command configuration method.
     * 
     * Configure all the arguments and options.
     */
    protected function configure()
    {
        $this
            ->setName('migration:generate')
            ->setDescription('Migration Generate')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The name of the new migration'
            )
            ->addArgument(
                'columns',
                InputArgument::IS_ARRAY,
                'table fields (E.j <field>:<type> <field_n>:<type_n>)'
            )            
            ->addOption(
                'type', 
                't', 
                InputOption::VALUE_REQUIRED, 
                'Set migration type (default,timestamp)', 
                'default'
            )
        ;
    }

    /**
     * Execute the command
     *
     * Create the migration scheme file and move it inside the filesystem.
     * 
     * @param  InputInterface  $input  
     * @param  OutputInterface $output 
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_name = $input->getArgument('name');
        $this->_type = $input->getOption('type');

        $migration_regex = ($this->_type === 'timestamp')
            ? '/^\d{14}_(\w+)$/'
            : '/^\d{3}_(\w+)$/';        

        $comment = 'Installation path (relative) [<comment>'.$this->_path.'</comment>]: ';
        
        $helper = $this->getHelper('question');        
        
        $question = new Question($comment, $this->_path);
        $question->setValidator(function($answer){
            if (basename($answer) !== $this->_default_folder) 
            {
                throw new \RuntimeException('Not a valid migration directory.');
            }
            return $answer;
        });
        $this->_path = $helper->ask($input, $output, $question);

        try {
            $filesystem = new Filesystem();
            
            if (! $filesystem->exists($this->_path)) 
            {
                throw new Exception("Directory {$this->_path} doesn't exist.");
            }
            if ($handle = opendir($this->_path)) 
            {      
                while (false !== ($entry = readdir($handle))) 
                {     
                    if ($entry != "." && $entry != "..") 
                    {      
                        $file = basename($entry, '.php');   
                        if (preg_match($migration_regex, $file))
                        {
                            $number = sscanf($file, '%[0-9]+', $number)? $number : '0'; 
                            try {
                                if (isset($this->_migrations[$number])) 
                                {
                                    throw new Exception("Cannot be duplicate migration numbers");
                                }
                            } catch (Exception $e) {
                                $output->writeln("<error>".$e->getMessage()."</error>");
                                return;
                            }
                            $this->_migrations[$number] = $file; 
                        }
                    }
                }       
                closedir($handle);
                ksort($this->_migrations);
            }            
        } catch (IOExceptionInterface $e) {
            echo "An error occurred while creating your directory at ".$e->getPath();
            return;
        }
                 
        if ($this->_type == 'timestamp') 
        {
            # check default timezone correctly
            date_default_timezone_set('UTC');
            $target_version = date("YmdHis");
        } 
        else 
        {
            $target_version = sprintf('%03d', abs(end($this->_migrations))+1);
        }

        $question = new Question('Target Version [<comment>'.$target_version.'</comment>]: ', $target_version);
        $target_version = $helper->ask($input, $output, $question); 

        $this->_filename = $target_version."_".$this->_name.".php";       

        $output->writeln("Installation path: <info>{$this->_path}</info>");
        $output->writeln("Filename: <info>{$this->_filename}</info>");
        
        $question = new ConfirmationQuestion('Generate migration file? [<comment>yes</comment>]? ', TRUE);
        if (! $helper->ask($input, $output, $question)) {
            $output->writeln("<info>Aborting...</info>");
            return;
        } 
        # Set the migration template
        $params = array(
            'name'       => $this->_name,
            'filename'   => $this->_filename,
            'path'       => DIRECTORY_SEPARATOR.$this->_path.$this->_filename,
            'table_name' => $this->_name
        );

        list($migration_type) = explode('_', $this->_name);

        switch ($migration_type) 
        {
            case 'create':
                $template_name = 'Migration_create.php.twig'; 
                $params['fields'] = (array) $input->getArgument('columns');
                break;
            case 'update':
                $template_name = 'Migration_update.php.twig';
                break;
            default:
                $template_name = 'Migration_default.php.twig';
                break;
        }
        $template = $this->_create_script_template($params,$template_name);
        $filesystem->dumpFile($this->_path.$this->_filename, $template); 
    }

    /**
     * Create a script template using Twig.
     * 
     * @param  array  $params   Twig template params.
     * @param  string $name     Template filename.
     * @return string           Twig render output.
     */
    private function _create_script_template($params = array(),$template_name = "")
    {
        Twig_Autoloader::register();
        $loader = new Twig_Loader_Filesystem(TEMPLATEPATH);
        $twig = new Twig_Environment($loader);
        $function = new \Twig_SimpleFunction('set_command', function ($field = "") {
          return array_combine(array('name','type'), explode(':', $field));
        });
        $twig->addFunction($function);        
        $template = $twig->loadTemplate($template_name);
        return $template->render($params);
    }


}
