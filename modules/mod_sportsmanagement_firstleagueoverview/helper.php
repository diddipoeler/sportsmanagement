<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_firstleagueoverview
 * @file       helper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;


class modjsmfirstleagueoverview
{

	 public static function getSeasonNames($params)
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
        $query->clear();
        $query->select('name');
		$query->from('#__sportsmanagement_season');
		$query->where('id = ' . (int) $params->get('s'));
		$db->setQuery($query);
		$season_name = $db->loadResult();
    $query->clear();

$query->select('season');
$query->from('#__sportsmanagement_uefawertung ');
$query->where('season <= ' . $db->Quote('' . $season_name . ''));

$query->order('season DESC');
$query->group('season');
$query->setLimit('5');
$db->setQuery($query);

$row = $db->loadAssocList();

//echo __LINE__.' row  <br><pre>'.print_r($row  ,true).'</pre>';

$column = $db->loadColumn();
      return $column;
    
  }
	

	public static function getData($params)
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
        
        $query->clear();
        $query->select('name');
		$query->from('#__sportsmanagement_season');
		$query->where('id = ' . (int) $params->get('s'));
		$db->setQuery($query);
		$season_name = $db->loadResult();
      



		

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $uefawertungneu;

	}

}
