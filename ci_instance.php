<?php

/**
 * ClI Instance for CodeIgniter
 *
 * @author     David Sosa Valdes <https://gitlab.com/david-sosa-valdes>
 * @license    MIT License
 * @copyright  2015 David Sosa Valdes
 * @link       
 *
 * Based on http://codeinphp.github.io/post/codeigniter-tip-accessing-codeigniter-instance-outside/
 */

ob_start();
require_once 'index.php'; // adjust path accordingly
ob_get_clean();
return $CI;