<?php

namespace Craftsman\Commands\Generators;

use Craftsman\Classes\Generator;

class Controller extends Generator
{
	protected $name        = 'generator:controller';
	protected $description = 'Generate a Controller';

	public function start()
	{
        $filename = ucfirst($this->getArgument('filename'));
        $basepath = rtrim($this->getOption('path'),'/').'/controllers/';

		// We could try to create a directory if doesn't exist.
		(! $this->_filesystem->exists($basepath)) && $this->_filesystem->mkdir($basepath);

        // Confirm the action
	    if($this->confirm('Do you want me to create a '.$filename.'Controller?', TRUE))
	    {
	    	$test_file = $basepath.$filename.'.php';
	    	$options = array(
	    		'NAME'       => $filename,
	    		'COLLECTION' => $filename,
	    		'FILENAME'   => basename($test_file),
	    		'PATH'       => $test_file
	    	);
	    	if ($this->make($test_file, 'src/Templates/Controllers', $options)) 
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
