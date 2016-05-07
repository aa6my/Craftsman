<?php
namespace Craftsman\Classes;

/**
 * Base Seeder Class
 *
 * @package     Craftsman
 * @author      David Sosa Valdes
 * @link        https://github.com/davidsosavaldes/Craftsman
 * @copyright   Copyright (c) 2016, David Sosa Valdes.
 * @version     1.0.0
 */
abstract class Seeder
{
	private $CI;
	
	protected $db;
	
	protected $dbforge;

	public function __construct()
	{
		$this->CI =& get_instance();
		
		$this->CI->load->database();
		$this->CI->load->dbforge();
		
		$this->db = $this->CI->db;
		$this->dbforge = $this->CI->dbforge;
	}

	public function __get($property)
	{
		return $this->CI->{$property};
	}
}
