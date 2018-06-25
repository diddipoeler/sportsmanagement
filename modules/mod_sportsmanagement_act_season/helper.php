<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      helper.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage mod_sportsmanagement_act_season
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'helpers'.DS.'countries.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'helpers'.DS.'route.php');

/**
 * modJSMActSeasonHelper
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class modJSMActSeasonHelper
{
	
	/**
	 * modJSMActSeasonHelper::getData()
	 * 
	 * @param mixed $season_ids
	 * @return
	 */
	public static function getData($season_ids)
	{
		// Reference global application object
        $app = JFactory::getApplication();
        $date = JFactory::getDate();
	   $user = JFactory::getUser();
    $db = JFactory::getDBO();
    $query = $db->getQuery(true);
        $result = array();
    
$seasons = implode(",",$season_ids); 
$query->select('pro.id,pro.name,CONCAT_WS(\':\',pro.id,pro.alias) AS project_slug,le.name as liganame,le.country');
$query->select('le.picture as league_picture,pro.picture as project_picture');
$query->select('CONCAT_WS(\':\',r.id,r.alias) AS roundcode');		
$query->from('#__sportsmanagement_project as pro');
$query->join('INNER','#__sportsmanagement_league as le on le.id = pro.league_id');
$query->join('INNER','#__sportsmanagement_round as r on r.id = pro.current_round');		
$query->where('le.published_act_season = 1 ');
$query->where('pro.season_id IN ('.$seasons.')');
$query->order('le.country ASC, pro.name ASC');

$db->setQuery( $query );
$result = $db->loadObjectList();
$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		return $result;
		
	}
	
}
