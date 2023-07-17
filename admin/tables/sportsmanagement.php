<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage tables
 * @file       sportsmanagement.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

/**
 * sportsmanagementTablesportsmanagement
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementTablesportsmanagement extends JSMTable
{
	/**
	 * Constructor
	 *
	 * @param   object Database connector object
	 */
	function __construct(&$db)
	{
		parent::__construct('#__session', 'session_id', $db);
	}

	/**
	 * Overloaded bind function
	 *
	 * @param   array           named array
	 *
	 * @return null|string     null is operation was satisfactory, otherwise returns an error
	 * @see    JTable:bind
	 * @since  1.5
	 */
	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			// Convert the params field to a string.
			$parameter = new Registry;
			$parameter->loadArray($array['params']);
			$array['params'] = (string) $parameter;
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded load function
	 *
	 * @param   int      $pk     primary key
	 * @param   boolean  $reset  reset data
	 *
	 * @return boolean
	 * @see    JTable:load
	 */
	public function load($pk = null, $reset = true)
	{
		if (parent::load($pk, $reset))
		{
			// Convert the params field to a registry.
			$params = new Registry;
			$params->loadJSON($this->params);
			$this->params = $params;

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form `table_name.id`
	 * where id is the value of the primary key of the table.
	 *
	 * @return string
	 * @since  1.6
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_sportsmanagement.message.' . (int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return string
	 * @since  1.6
	 */
	protected function _getAssetTitle()
	{
		return $this->greeting;
	}

	/**
	 * Get the parent asset id for the record
	 *
	 * @return integer
	 * @since  1.6
	 */
	protected function _getAssetParentId()
	{
		$asset = Table::getInstance('Asset');
		$asset->loadByName('com_sportsmanagement');

		return $asset->id;
	}
}
