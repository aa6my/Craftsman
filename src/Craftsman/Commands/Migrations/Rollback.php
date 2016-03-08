<?php
namespace Craftsman\Commands\Migrations;

use Craftsman\Classes\Migration;

/**
 * Migration - Rollback Command
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 */
class Rollback extends Migration
{
	protected $name        = 'migration:rollback';
	protected $description = 'Rollback from the last migration';

	protected function start()
	{
		$migrations = $this->_model->find_migrations();
		$versions   = array_map('intval', array_keys($migrations));

		$db_version = intval($this->_model->get_db_version());

		end($versions);
		while ($version = prev($versions)) 
		{
			if ($version == ($db_version - 1)) 
			{
				break;
			}
		}

		if($version < 0)
		{
			return $this->note("Can't rollback anymore");
		}
		else
		{
			if ($version == $db_version) 
			{
				$version = 0;
			}
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
