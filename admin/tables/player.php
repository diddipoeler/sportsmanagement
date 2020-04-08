<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage tables
 * @file       player.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Filter\OutputFilter;


/**
 * sportsmanagementTableplayer
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2019
 * @version   $Id$
 * @access    public
 */
class sportsmanagementTableplayer extends JSMTable
{


	/**
	 * sportsmanagementTableplayer::__construct()
	 *
	 * @param   mixed $db
	 * @return
	 */
	function __construct(& $db)
	{
		  $db = sportsmanagementHelper::getDBConnection();
		parent::__construct('#__sportsmanagement_person', 'id', $db);
	}



	/**
	 * sportsmanagementTableplayer::check()
	 *
	 * @return
	 */
	function check()
	{
		if (empty($this->firstname) && empty($this->lastname))
		{
			$this->setError(Text::_('ERROR FIRSTNAME OR LASTNAME REQUIRED'));

			return false;
		}

		$parts = array( trim($this->firstname), trim($this->lastname) );
		$alias = OutputFilter::stringURLSafe(implode(' ', $parts));

		if (empty($this->alias))
		{
			$this->alias = $alias;
		}
		else
		{
			$this->alias = OutputFilter::stringURLSafe($this->alias); // Make sure the user didn't modify it to something illegal...
		}

		return true;
	}





}
