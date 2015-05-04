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
 * 
 */
class Create extends Command
{
    /**
     * [$_default_folder description]
     * @var string
     */
    private $_default_folder = 'migrations';
    /**
     * [$_path description]
     * @var string
     */
    private $_path = 'application/migrations'.DIRECTORY_SEPARATOR;

    /**
     * [$_template_path description]
     * @var string
     */
    private $_template_path = 'templates/Codeigniter'.DIRECTORY_SEPARATOR;

    /**
     * [$_migrations description]
     * @var array
     */
    private $_migrations = array();

    /**
     * [$_filename description]
     * @var [type]
     */
    private $_filename;

    /**
     * [$_name description]
     * @var [type]
     */
    private $_name;

    /**
     * [$_type description]
     * @var [type]
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
     * [configure description]
     * @return [type] [description]
     */
    protected function configure()
    {
        $this
            ->setName('migration:create')
            ->setDescription('Migration Create')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The name of the new migration'
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
     * [execute description]
     * @param  InputInterface  $input  [description]
     * @param  OutputInterface $output [description]
     * @return [type]                  [description]
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
        
        $question = new ConfirmationQuestion('Create migration file? [<comment>yes</comment>]? ', TRUE);
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
        $template = $this->_create_script_template($params);
        $filesystem->dumpFile($this->_path.$this->_filename, $template); 
    }

    /**
     * [_create_script_template description]
     * @param  array  $params [description]
     * @return [type]         [description]
     */
    private function _create_script_template($params = array())
    {
        Twig_Autoloader::register();
        $loader = new Twig_Loader_Filesystem($this->_template_path);
        $twig = new Twig_Environment($loader);
        $template = $twig->loadTemplate('Migration.php.twig');
        return $template->render($params);
    }


}
