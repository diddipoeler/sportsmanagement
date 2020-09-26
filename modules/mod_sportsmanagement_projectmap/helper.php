<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_projectmap
 * @file       helper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

JLoader::import('components.com_sportsmanagement.helpers.route', JPATH_SITE);

/**
 * modJSMprojectmaphelper
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2020
 * @version $Id$
 * @access public
 */
class modJSMprojectmaphelper
{
	
	/**
	 * modJSMprojectmaphelper::getData()
	 * 
	 * @param mixed $season_ids
	 * @return void
	 */
	public static function getData($season_ids)
	{
		$app    = Factory::getApplication();
		$date   = Factory::getDate();
		$user   = Factory::getUser();
		$db     = Factory::getDBO();
		$query  = $db->getQuery(true);
		$result = array();

		$seasons = implode(",", $season_ids);
		$query->select('MAX( pro.id ) as id,pro.name,CONCAT_WS(\':\',pro.id,pro.alias) AS project_slug,le.name as liganame,le.country');
		$query->select('le.picture as league_picture,pro.picture as project_picture');
		$query->select('CONCAT_WS(\':\',r.id,r.alias) AS roundcode');
        
        $query->select('c.alpha2 as country_alpha2,c.name as country_name,c.picture as country_picture,c.federation as country_federation');
        $query->select('f.name as federation_name,f.picture as federation_picture');
      
		$query->from('#__sportsmanagement_project as pro');
		$query->join('INNER', '#__sportsmanagement_league as le on le.id = pro.league_id');
		$query->join('INNER', '#__sportsmanagement_round as r on r.id = pro.current_round');
        $query->join('INNER', '#__sportsmanagement_countries as c on c.alpha3 = le.country');
      
      $query->join('INNER', '#__sportsmanagement_federations as f on f.id = c.federation');
        
		$query->where('le.published_act_season = 1 ');
      $query->where('le.league_level = 1 ');
		$query->where('pro.season_id IN (' . $seasons . ')');
		$query->order('le.country ASC, pro.name ASC');
      $query->group('le.country');

		$db->setQuery($query);
		$result = $db->loadObjectList();
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

//echo '<pre>'.print_r($result,true).'</pre>';

		return $result;
        
        
    
  }
  
  
}
