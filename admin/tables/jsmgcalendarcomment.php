<?php
/**
 * @package		GCalendar Action Pack
 * @author		Digital Peak http://www.digital-peak.com
 * @copyright	Copyright (C) 2012 - 2013 Digital Peak. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

JLoader::import('joomla.database.table');

class  sportsmanagementTablejsmgcalendarComment extends JTable {

	public function __construct(&$db) {
		parent::__construct('#__sportsmanagement_gcalendarap_comment', 'id', $db);
	}

	public function bind($array, $ignore = '') {
		if (isset($array['params']) && is_array($array['params'])) {
			// Convert the params field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($array['params']);
			$array['params'] = (string)$parameter;
		}
		return parent::bind($array, $ignore);
	}
}