<?php

namespace Craftsman\Commands\Migrations;

use Craftsman\Classes\Migration;

/**
 * Migration - Reset Command
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 */
class Reset extends Migration
{
	protected $name        = 'migration:reset';
	protected $description = 'Rollback all migrations';

	protected function start()
	{
		$migrations = $this->migration->find_migrations();
		$db_version = intval($this->migration->get_db_version());
		$version    = 0;

		$this->text('Migrating database <info>DOWN</info> to version <comment>'.$version.
			'</comment> from <comment>'.$db_version.'</comment>');
		$case = 'reverting';
		$signal = '--';

		$this->newLine();
		$this->text('<info>'.$signal.'</info> '.$case);		

		$time_start = microtime(true);

		$this->migration->version($version);

		$time_end = microtime(true);

		$this->newLine();
		list($query_exec_time, $exec_queries) = $this->measureQueries($this->migration->db->queries);
		
		$this->summary($signal, $time_start, $time_end, $query_exec_time, $exec_queries);
	}
}
