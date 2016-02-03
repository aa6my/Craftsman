<?php

namespace Craftsman\Commands\Generators;

use Craftsman\Classes\Generator;

class Model extends Generator
{
	protected $name        = 'generator:model';
	protected $description = 'Generate a Model';

	public function start()
	{
        $filename = ucfirst($this->getArgument('filename'));
        $basepath = rtrim($this->getOption('path'),'/').'/models/';

		// We could try to create a directory if doesn't exist.
		(! $this->_filesystem->exists($basepath)) && $this->_filesystem->mkdir($basepath);

        // Confirm the action
	    if($this->confirm('Do you want me to create a '.$filename.'Model?', TRUE))
	    {
	    	$test_file = $basepath.$filename.'_model.php';
	    	$options = array(
	    		'NAME' => $filename.'_model',
	    		'COLLECTION' => $filename,
	    		'FILENAME'   => basename($test_file),
	    		'PATH'       => $test_file	    		
	    	);
	    	if ($this->make($test_file, 'src/Templates/Models', $options)) 
	    	{
	    		$this->success('Model created successfully!');
	    	}
	    }
	    else
	    {
	    	$this->warning('Process aborted!');
	    }
	}	

}
