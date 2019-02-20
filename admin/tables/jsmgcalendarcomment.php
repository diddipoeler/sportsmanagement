<?php


defined('_JEXEC') or die();
use Joomla\Registry\Registry;

JLoader::import('joomla.database.table');

/**
 * sportsmanagementTablejsmgcalendarComment
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class  sportsmanagementTablejsmgcalendarComment extends JTable {

	/**
	 * sportsmanagementTablejsmgcalendarComment::__construct()
	 * 
	 * @param mixed $db
	 * @return
	 */
	public function __construct(&$db) {
		parent::__construct('#__sportsmanagement_gcalendarap_comment', 'id', $db);
	}

	/**
	 * sportsmanagementTablejsmgcalendarComment::bind()
	 * 
	 * @param mixed $array
	 * @param string $ignore
	 * @return
	 */
	public function bind($array, $ignore = '') {
		if (isset($array['params']) && is_array($array['params'])) {
			// Convert the params field to a string.
			$parameter = new Registry;
			$parameter->loadArray($array['params']);
			$array['params'] = (string)$parameter;
		}
		return parent::bind($array, $ignore);
	}
}