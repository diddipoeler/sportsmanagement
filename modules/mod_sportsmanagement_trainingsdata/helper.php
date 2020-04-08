<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_trainingsdata
 * @file       helper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

/**
 * modJSMTrainingsData
 *
 * @package
 * @author    abcde
 * @copyright 2015
 * @version   $Id$
 * @access    public
 */
class modJSMTrainingsData
{

	/**
	 * modJSMTrainingsData::getData()
	 *
	 * @param   mixed $params
	 * @return
	 */
	public static function getData($params)
	{
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;

		// Get a db connection.
		$db = sportsmanagementHelper::getDBConnection();

		// Create a new query object.
		$query = $db->getQuery(true);

			  $result = array();

			  $query->select('*');
		$query->from('#__sportsmanagement_team_trainingdata');
		$query->where('team_id = ' . (int) $params->get('teams'));
		$query->order('dayofweek ASC');

			  $db->setQuery($query);
		$result = $db->loadObjectList();

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			  return $result;

	}

}
