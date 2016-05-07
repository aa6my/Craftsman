<?php
namespace Craftsman\Commands\Generators;

use Craftsman\Classes\Generator;

/**
 * Generator\Controller Command
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 */
class Controller extends Generator implements \Craftsman\Interfaces\Command
{
	protected $name        = 'generator:controller';
	protected $description = 'Generate a Controller';

	public function start()
	{
        $filename = ucfirst($this->getArgument('filename'));
        $basepath = rtrim($this->getOption('path'),'/').'/controllers/';

		$this->text('Controller path: <comment>'.$basepath.'</comment>');
		$this->text('Filename: <comment>'.$filename.'.php</comment>');		

        // Confirm the action
	    if($this->confirm('Do you want to create a '.$filename.' Controller?', TRUE))
	    {
			// We could try to create a directory if doesn't exist.
			(! $this->_filesystem->exists($basepath)) && $this->_filesystem->mkdir($basepath);	    	
	    	
	    	$test_file = $basepath.$filename.'.php';
	    	
	    	$options = array(
	    		'NAME'       => $filename,
	    		'COLLECTION' => strtolower($filename),
	    		'FILENAME'   => basename($test_file),
	    		'PATH'       => $test_file,
	    		'ACTIONS'    => $this->getArgument('options')
	    	);
	    	$this->comment('Controller');

	    	if ($this->make($test_file, CRAFTSMANPATH.'src/Templates/Controllers', $options)) 
	    	{
	    		$this->text('<info>create</> '.$test_file);
	    	}	    	

	    	$views = empty($options['ACTIONS'])
	    		? array('index','get','create','edit')
	    		: $options['ACTIONS'];

	    	$viewpath = rtrim($this->getOption('path'),'/').'/views/'.strtolower($filename).'/';

	   		// We could try to create a directory if doesn't exist.
			(! $this->_filesystem->exists($viewpath)) && $this->_filesystem->mkdir($viewpath);	 

	    	$options['EXT']      = '.php';
	    	$options['CLASS']    = $filename;
	    	$options['VIEWPATH'] = $viewpath;	

	    	$this->comment('Views');

	    	foreach ($views as $view) 
	    	{
	    		$viewfile = $viewpath.$view.'.php';

	    		$options['METHOD'] = $view;

	    		$this->make($viewfile, CRAFTSMANPATH.'src/Templates/Views',$options);
	    		$this->text('<info>create</info> '.$viewfile);
	    	}
	    }
	    else
	    {
	    	$this->warning('Process aborted!');
	    }
	}	

}
