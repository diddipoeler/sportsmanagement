<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage tables
 * @file       agegroup.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Filter\OutputFilter;
use Joomla\Registry\Registry;

/**
 * sportsmanagementTableagegroup
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementTableagegroup extends JSMTable
{
	/**
	 * Constructor
	 *
	 * @param   object Database connector object
	 *
	 * @since 1.0
	 */
	function __construct(&$db)
	{
		$db = sportsmanagementHelper::getDBConnection();
		parent::__construct('#__sportsmanagement_agegroup', 'id', $db);
	}

	/**
	 * Overloaded check method to ensure data integrity
	 *
	 * @access public
	 * @return boolean True on success
	 * @since  1.0
	 */
	function check()
	{
		// Setting alias
		if (empty($this->alias))
		{
			$this->alias = OutputFilter::stringURLSafe($this->name);
		}
		else
		{
			$this->alias = OutputFilter::stringURLSafe($this->alias); // Make sure the user didn't modify it to something illegal...
		}

		// Should check name unicity
		return true;
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

			if (version_compare(JVERSION, '3.0.0', 'ge'))
			{
				$params->loadString($this->extended);
			}
			else
			{
				$params->loadJSON($this->extended);
			}

			// $params->toArray($this->extended);
			$this->extended = $params->toArray($this->extended);

			return true;
		}
		else
		{
			return false;
		}
	}

}
