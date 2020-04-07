<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage tables
 * @file       team.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filter\OutputFilter;

/**
 * sportsmanagementTableTeam
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementTableTeam extends JSMTable
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	function __construct(& $db)
	{
		  $db = sportsmanagementHelper::getDBConnection();
		parent::__construct('#__sportsmanagement_team', 'id', $db);
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
		if (empty($this->name))
		{
			$this->setError(Text::_('NAME REQUIRED'));

			return false;
		}

			  // Add default middle size name
		if (empty($this->middle_name))
		{
			$parts = explode(" ", $this->name);
			$this->middle_name = substr($parts[0], 0, 20);
		}

		// Add default short size name
		if (empty($this->short_name))
		{
			$parts = explode(" ", $this->name);
			$this->short_name = substr($parts[0], 0, 2);
		}

		// Setting alias
		$this->alias = OutputFilter::stringURLSafe($this->name);

			  return true;
	}

}
