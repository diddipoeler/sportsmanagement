<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       sportsmanagements.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\ListModel;

/**
 * SportsManagementList Model
 */
class sportsmanagementModelsportsmanagements extends ListModel
{
	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return string    An SQL query
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);

		// Select some fields
		$query->select('id,greeting');

		// From the hello table
		$query->from('#__sportsmanagement');

		return $query;
	}
}
