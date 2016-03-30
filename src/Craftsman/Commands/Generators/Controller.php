<?php

namespace Craftsman\Commands\Generators;

use Craftsman\Classes\Generator;
use Craftsman\Interfaces\Generator as GeneratorInterface;

/**
 * Generator - Controller Command
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 */
class Controller extends Generator implements GeneratorInterface
{
	protected $name        = 'generator:controller';
	protected $description = 'Generate a Controller';

	public function start()
	{
        $filename = ucfirst($this->getArgument('filename'));
        $basepath = rtrim($this->getOption('path'),'/').'/controllers/';

		// We could try to create a directory if doesn't exist.
		(! $this->_filesystem->exists($basepath)) && $this->_filesystem->mkdir($basepath);

		$this->text('Controller path: <comment>'.$basepath.'</comment>');
		$this->text('Filename: <comment>'.$filename.'.php</comment>');		

        // Confirm the action
	    if($this->confirm('Do you want to create a '.$filename.' Controller?', TRUE))
	    {
	    	$test_file = $basepath.$filename.'.php';
	    	$options = array(
	    		'NAME'       => $filename,
	    		'COLLECTION' => strtolower($filename),
	    		'FILENAME'   => basename($test_file),
	    		'PATH'       => $test_file
	    	);
	    	if ($this->make($test_file, CRAFTSMANPATH.'src/Templates/Controllers', $options)) 
	    	{
	    		$this->success('Controller created successfully!');
	    	}
	    }
	    else
	    {
	    	$this->warning('Process aborted!');
	    }
	}	

}
