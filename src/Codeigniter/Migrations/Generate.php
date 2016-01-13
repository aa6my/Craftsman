<?php 
namespace Codeigniter\Migrations;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use Twig_Autoloader;
use Twig_Loader_Filesystem;
use Twig_Environment;

/**
 * Migration:Generate Class
 *
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2015, David Sosa Valdes.
 * @version     1.3.0
 *
 */
class Generate extends Command
{
    /**
     * Default migrations folder path.
     * @var string
     */
    private $_path = APPPATH.'migrations/';


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
        // Set default timezone if we use a timestamp migration version.
        date_default_timezone_set('UTC');

        $output->getFormatter()->setStyle('title', 
            new OutputFormatterStyle('cyan', 'black', array('bold'))
        );

        $name = ucfirst($input->getArgument('name'));
        $type = strtolower($input->getOption('type'));

        $migration_regex = ($type == 'timestamp')
            ? '/^\d{14}_(\w+)$/'
            : '/^\d{3}_(\w+)$/';  

        $output->writeln("<title> -- Craftsman Migration: Generate -- </title>");      

        // Show the short version path.
        $comment = 'Installation path [<comment>'
            .basename(dirname($this->_path)).'/'
            .basename($this->_path).'/</comment>]: ';
        
        $helper = $this->getHelper('question');        
        
        $question = new Question($comment, $this->_path);
        // Posible CI migrations paths
        $question->setAutocompleterValues(array(
          'application/',
          'system/',
          'application/vendor/',
          'bundles/',
          'application/migrations/',
        ));
        
        $this->_path = rtrim($helper->ask($input, $output, $question),'/').'/';
        // Create the migration file based on target version
        try 
        {
            $filesystem = new Filesystem();
            // We could try to create a directory for you if doesn't exist.
            (! $filesystem->exists($this->_path)) &&  $filesystem->mkdir($this->_path);
            // And now let's figure out the migration target version
            if ($handle = opendir($this->_path)) 
            {      
                while (($entry = readdir($handle)) !== FALSE) 
                {     
                    if ($entry == "." && $entry == "..") 
                    {      
                        continue;  
                    }

                    if (preg_match($migration_regex, $file = basename($entry, '.php')))
                    {
                        $number = sscanf($file, '%[0-9]+', $number)? $number : '0'; 
                        if (isset($migrations[$number])) 
                        {
                            throw new \RuntimeException("Cannot be duplicate migration numbers");
                        }
                        $migrations[$number] = $file; 
                    }                    
                }       
                closedir($handle);
                ksort($migrations);
            }            
        } 
        catch (IOExceptionInterface $e) 
        {
            echo "An error occurred while creating your directory at ".$e->getPath();
            return;
        }

        $question = new Question('Target Version [<comment>'.$target_version.'</comment>]: ', ($type == 'timestamp')
            ? date("YmdHis")
            : sprintf('%03d', abs(end($migrations)) + 1)
        );

        $target_version = $helper->ask($input, $output, $question); 
        $filename = $target_version."_".$name.".php";       

        $output->writeln('Installation path: <info>'.$this->_path.'</info>');
        $output->writeln('Filename: <info>'.$filename.'</info>');
        
        $question = new ConfirmationQuestion('Generate migration file? [<comment>yes</comment>]? ', TRUE);
        if (! $helper->ask($input, $output, $question)) {
            $output->writeln("<info>Aborting...</info>");
            return;
        } 
        # Set the migration template arguments
        $params = array(
            'name'       => $name,
            'filename'   => $filename,
            'path'       => $this->_path.$filename,
            'table_name' => $name,
            'fields'     => (array) $input->getArgument('columns')
        );

        switch (list($_type) = explode('_', $name)) 
        {
            case 'create':
                $template_name = 'Create.php.twig'; 
                break;
            case 'modify':
                $template_name = 'Modify.php.twig';
                empty($params['fields']) && $params['fields'] = array('column_name:column_type');
                break;
            default:
                $template_name = 'Default.php.twig';
                break;
        }
        $filesystem->dumpFile(
            $this->_path.$filename, 
            $this->_create_template($params, $template_name)
        ); 
    }

    /**
     * Create a migration template using Twig.
     * 
     * @param  array  $params   Twig template params.
     * @param  string $name     Template filename.
     * @return string           Twig render output.
     */
    private function _create_template($params = array(),$template_name = "")
    {
        Twig_Autoloader::register();
        $loader = new Twig_Loader_Filesystem(ROOTPATH.'src/Templates/Migrations');
        $twig   = new Twig_Environment($loader);
        
        $function = new \Twig_SimpleFunction('set_command', function ($field = "") {
          return array_combine(array('name','type'), explode(':', $field));
        });
        $twig->addFunction($function);        

        return $twig->loadTemplate($template_name)->render($params);
    }
}
