<?php

namespace Craftsman\Commands\Migrations;

use Craftsman\Classes\Migration;

/**
 * Migration - Latest Command
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 */
class Latest extends Migration
{
	protected $name        = 'migration:latest';
	protected $description = 'Run the latest migration';

	protected function start()
	{
		$migrations = $this->_model->find_migrations();
		$version    = $this->_model->get_latest_version($migrations);
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

		$this->_model->latest();

		$time_end = microtime(true);

		$this->newLine();
		list($query_exec_time, $exec_queries) = $this->measureQueries($this->_model->db->queries);
		
		$this->summary($signal, $time_start, $time_end, $query_exec_time, $exec_queries);
	}
}
