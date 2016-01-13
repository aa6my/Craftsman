<?php 
namespace Codeigniter\Scaffold;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;
use Twig_Autoloader;
use Twig_Loader_Filesystem;
use Twig_Environment;

/**
 * Scaffold Generator Class
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2015, David Sosa Valdes.
 * @version     1.0.0
 */
class Generate extends Command
{
    /**
     * Default controllers path.
     * @var string
     */
    private $_controllers_path = 'controllers/';

    /**
     * Default models path.
     * @var string
     */
    private $_models_path = 'models/';

    /**
     * Default views path.
     * @var string
     */
    private $_views_path =  'views/';

    /**
     * Command configuration.
     * 
     * Configure all the arguments and options.
     */
    protected function configure()
    {
        $this
            ->setName('scaffold:generate')
            ->setDescription('Scaffold Generate')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The name of the new scaffold'
            )
            ->addArgument(
                'options',
                InputArgument::IS_ARRAY,
                'Options passed to all files'
            )            
            ->addOption(
                'path', 
                'p', 
                InputOption::VALUE_REQUIRED, 
                'Set the scaffold base path', 
                APPPATH
            )
        ;
    }

    /**
     * Execute the command
     * 
     * Create the scaffold files.
     * 
     * @param InputInterface  $input  
     * @param OutputInterface $output 
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output
            ->getFormatter()
            ->setStyle(
                'title', 
                new OutputFormatterStyle('cyan', 'black', ['bold'])
            );
        $output
            ->getFormatter()
            ->setStyle(
                'action', 
                new OutputFormatterStyle('cyan', 'black', ['bold'])
            );

        $helper = $this->getHelper('question');

        $name    = ucfirst($input->getArgument('name'));
        $options = $input->getArgument('options');
        $path    = $input->getOption('path');
        $files   = array();

        $output->writeln("<title> -- Craftsman Scaffold: Generate -- </title>");
        
        $question = new Question('Installation path [<comment>'.$path.'</comment>]: ', $path);
        $question->setAutocompleterValues(array(
          'application/',
          'system/',
          'bundles/'
        ));
        
        $path = rtrim($helper->ask($input, $output, $question),'/').'/';
        
        $filesystem = new Filesystem();

        try 
        {
            // We could try to create a directory if doesn't exist.
            (! $filesystem->exists($path)) && $filesystem->mkdir($path);
            // Iterate over all the folders
            foreach ([$this->_controllers_path, $this->_models_path, $this->_views_path] as $folder) 
            {
                $folder_path = rtrim($path.$folder,'/').'/';
                // Also we could try to create a scaffold directory if doesn't exist.
                //(! $filesystem->exists()) && $filesystem->mkdir($folder_path);
                
                switch ($folder) 
                {
                    case $this->_models_path:
                        $test_files = array(
                            $folder_path.$name.'_model.php'
                        );
                        break;
                    
                    case $this->_views_path:
                        
                        $key = $folder_path.strtolower($name).'/';
                        (! $filesystem->exists($key)) && $filesystem->mkdir($key); 
                        
                        $test_files = array(
                            $key.'create.twig',
                            $key.'read.twig',
                            $key.'update.twig',
                            $key.'delete.twig'
                        );

                        break;

                    case $this->_controllers_path:
                        $test_files = array(
                            $folder_path.$name.'.php'
                        );
                        break;
                }

                foreach ($test_files as $file) 
                {
                    if ($filesystem->exists($file)) 
                    {
                     throw new \RuntimeException("Cannot duplicate: [".$test_file."]");
                    }
                    else
                    {
                        $files[$folder][] = $file;
                    }
                }
            } 
        }
        catch (IOExceptionInterface $e) 
        {
            echo "An error occurred while creating your directory at ".$e->getPath();
            return;
        }
        
        $table = new Table($output);
        $table
            ->setStyle((new TableStyle)->setCellHeaderFormat('<action>%s</action>'))
            ->setHeaders(array('Folder','File'))
            ->setRows(array(
                array($this->_controllers_path, implode(",\n", $files[$this->_controllers_path])),
                array($this->_models_path, implode(",\n", $files[$this->_models_path])),
                array($this->_views_path, implode(",\n", $files[$this->_views_path]))
            ))
            ->render();
        
        $question = new ConfirmationQuestion('Do you want to continue? [<comment>yes</comment>]? ', TRUE);
        if (! $helper->ask($input, $output, $question)) {
            $output->writeln("<info>Aborting...</info>");
            return;
        } 

        Twig_Autoloader::register();

        $loader = new Twig_Loader_Filesystem(array(
            ROOTPATH.'src/Templates/Controllers',
            ROOTPATH.'src/Templates/Models',
            ROOTPATH.'src/Templates/Views'
        ));
        $twig = new Twig_Environment($loader);

        empty($options) && $options = array('name:type');

        array_walk($options, function(&$field){
            $field = array_combine(array('name', 'type'), explode(':', $field));
        });

        foreach ($files as $folder => $files_path) 
        {           
            foreach ($files_path as $file_path) 
            {
                $params = array(
                    'name'     => $name,
                    'filename' => basename($file_path),
                    'path'     => $file_path,
                    'options'  => $options
                );

                switch ($folder) 
                {
                    case $this->_controllers_path:
                        $template = 'BaseController.php.twig';
                        break;
                    case $this->_models_path:
                        $template = 'BaseModel.php.twig';
                        break;
                    case $this->_views_path:
                        $template = 'View{mode}.php.twig';
                        break;
                }                   

                try
                {
                    if ($folder == $this->_views_path) 
                    {
                        $mode = str_replace('.twig', '', $params['filename']);
                        $template = str_replace('{mode}', ucfirst($mode) , $template);
                    }
                    $filesystem->dumpFile(
                        $file_path, 
                        $twig->loadTemplate($template)->render($params)
                    );
                }
                catch (IOExceptionInterface $e) 
                {
                    echo "An error occurred while creating your file at: ".$e->getPath();
                    return;
                }        
            }                     
        }
    }
}