<?php

namespace Craftsman\Commands\Migrations;

use Craftsman\Classes\Migration;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Migration - Version Command
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 */
class Version extends Migration
{
	protected $name        = 'migration:version';
	protected $description = 'Run a migration by version as argument';

	protected function configure()
	{
		parent::configure();
		$this
			->addArgument(
				'version',
				InputArgument::REQUIRED,
				'The version name'
			);
	}

	protected function start()
	{
		$version = abs($this->getArgument('version'));
		$db_version = intval($this->_model->get_db_version());				 

		if($version == $db_version)
		{
			return $this->note('Database is up-to-date');
		}
		elseif ($version > $db_version) 
		{
			$this->text('Migrating database <info>UP</info> to version <comment>'.$version.
				'</comment> from <comment>'.$db_version.'</comment>');
			$case = 'migrating';
			$signal = '++';
		}
		else
		{
			$this->text('Migrating database <info>DOWN</info> to version <comment>'.$version.
				'</comment> from <comment>'.$db_version.'</comment>');
			$case = 'reverting';
			$signal = '--';
		}	

		$this->newLine();
		$this->text('<info>'.$signal.'</info> '.$case);		

		$time_start = microtime(true);

		$this->_model->version($version);

		$time_end = microtime(true);

		$this->newLine();
		list($query_exec_time, $exec_queries) = $this->measureQueries($this->_model->db->queries);

		$this->summary($signal, $time_start, $time_end, $query_exec_time, $exec_queries);
	}
}
