<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 1.0.0
 */

/**
 * CodeIgniter Migration
 *
 * Run all the posible migration escenarios in a Codeigniter application or HMVC application
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		David Sosa Valdes
 * @copyright   Copyright (c) 2015, David Sosa Valdes.
 */
class MY_Migration extends CI_Migration
{
	/**
	 * HMVC Migration Settings
	 * @var array
	 */
	protected $_settings = array();

	/**
	 * [$_module_path description]
	 * @var [type]
	 */
	protected $_module_path = NULL;

	/**
	 * [$_module_name description]
	 * @var null
	 */
	
	protected $_module_name = 'ci_system';

	/**
	 * [$_module_field description]
	 * @var string
	 */
	protected $_module_field = 'module';

	/**
	 * [__construct description]
	 * @param array $config [description]
	 */
	public function __construct($config = array())
	{
       	parent::__construct($config);

		log_message('info', 'HMVC Migrations Module Class init');

		if (! $this->db->field_exists($this->_module_field, $this->_migration_table))
		{		
			$fields = array(
        		'module' => array(
        			'type' => 'VARCHAR',
        			'first' => TRUE,
        			'constraint' => '100',
        			'null' => FALSE
        		)
			);
			$this->dbforge->add_column($this->_migration_table, $fields);
			
			$this->db->query(
				"ALTER TABLE {$this->_migration_table} "
				."ADD PRIMARY KEY({$this->_module_field});"
			);
			$this->db->query(
				"UPDATE {$this->_migration_table} "
				."SET {$this->_module_field} = '{$this->_module_name}' LIMIT 1;"
			);
		}
		$this->_set_migration_path();
	}

	/**
	 * [get_module_name description]
	 * @return [type] [description]
	 */
	public function get_module_name()
	{
		return $this->_module_name;
	}

	/**
	 * [get_module_path description]
	 * @return [type] [description]
	 */
	public function get_module_path()
	{
		return $this->_migration_path;
	}

	/**
	 * [_get_version description]
	 * @return [type] [description]
	 */
	protected function _get_version()
	{
		$this->db->select("version, {$this->_module_field}");
		$this->db->where($this->_module_field, $this->_module_name);
		$row = $this->db->get($this->_migration_table)->row();
		return (!is_null($row)) ? $row->version : '0';
	}

	public function get_db_version()
	{
		return $this->_get_version();
	}

	public function get_current_config_version()
	{
		return $this->_migration_version;
	}

	/**
	 * [get_number description]
	 * @param  [type] $number [description]
	 * @return [type]         [description]
	 */
	public function get_number($number)
	{
		return $this->_get_migration_number($number);
	}	

	/**
	 * [_set_migration_path description]
	 */
	private function _set_migration_path()
	{
		if ($this->_module_name === 'ci_system') {
			return;
		}
		elseif ($this->_module_path !== NULL) 
		{
			$this->_migration_path = rtrim($this->_module_path,'/').'/';
		}
		return $this;
	}	

	/**
	 * Set alll params you want.
	 *
	 * @param array $config [description]
	 */
	public function set_params($config = array())
	{
		foreach ($config as $key => $val)
		{
			$this->{'_'.$key} = $val;
		}
		$this->_set_migration_path();
		return $this;
	}

	/**
	 * Stores the current schema version
	 *
	 * @param	string	$migration	Migration reached
	 * @return	void
	 */
	protected function _update_version($migration)
	{
		$data = array(
			'version' => $migration,
			$this->_module_field => $this->_module_name
		);
		if ($this->_check_module_exist()) 
		{
			$this->db->where($this->_module_field, $this->_module_name);
			$this->db->update($this->_migration_table, $data);
		}
		else 
		{
			$insert_query = $this->db->insert_string($this->_migration_table, $data);
			$insert_query = str_replace('INSERT INTO','INSERT IGNORE INTO', $insert_query);
			$this->db->query($insert_query);
		}
	}	

	/**
	 * Check if migration module exist in db.
	 * @return [type] [description]
	 */
	protected function _check_module_exist()
	{
		$this->db->from($this->_migration_table);
		$this->db->where($this->_module_field, $this->_module_name);
		$this->db->limit(1);
		$query = $this->db->get();
		return $query->num_rows() >= 1;
	}
}

/* End of file MY_Migration.php */