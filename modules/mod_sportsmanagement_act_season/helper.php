<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_act_season
 * @file       helper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
JLoader::import('components.com_sportsmanagement.helpers.route', JPATH_SITE);

/**
 * modJSMActSeasonHelper
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2016
 * @version   $Id$
 * @access    public
 */
class modJSMActSeasonHelper
{

	public static function getDataCcountryFederation()
	{
	  $app    = Factory::getApplication();
		$date   = Factory::getDate();
		$user   = Factory::getUser();
		$db     = Factory::getDBO();
		$query  = $db->getQuery(true);
		$result = array();
         $federation = array();
		$query->clear();
$query->select('alpha3,federation');
$query->from('#__sportsmanagement_countries');
$db->setQuery($query);
$federation = $db->loadObjectList(); 
	return $federation;	
	}
/**
 * modJSMActSeasonHelper::getDataFederation()
 * 
 * @param mixed $data
 * @return void
 */
public static function getDataFederation($data)
	{
	  $app    = Factory::getApplication();
		$date   = Factory::getDate();
		$user   = Factory::getUser();
		$db     = Factory::getDBO();
		$query  = $db->getQuery(true);
		$result = array();
         $federation = array();
         
         foreach ( $data as $key => $value )
         {
         $federation[$value->federation] = new stdClass();   
         }
        
        foreach ( $federation as $key => $value )
         {
         $query->clear();
$query->select('objassoc.*');
$query->from('#__sportsmanagement_federations as objassoc');
$query->where('objassoc.id =  '.$key);
$db->setQuery($query);
$federation[$key]  = $db->loadObject(); 
         }
         
         return $federation;
        // echo '<pre>'.print_r($federation,true).'</pre>';
         
       }
       
	/**
	 * modJSMActSeasonHelper::getData()
	 *
	 * @param   mixed  $season_ids
	 *
	 * @return
	 */
	public static function getData($season_ids)
	{
		$app    = Factory::getApplication();
		$date   = Factory::getDate();
		$user   = Factory::getUser();
		$db     = Factory::getDBO();
		$query  = $db->getQuery(true);
		$result = array();

		if ( $season_ids )
        {
        $seasons = implode(",", $season_ids);
        
		$query->select('pro.id,pro.name,CONCAT_WS(\':\',pro.id,pro.alias) AS project_slug,le.name as liganame,le.country');
		$query->select('le.picture as league_picture,pro.picture as project_picture,le.federation');
		$query->select('CONCAT_WS(\':\',r.id,r.alias) AS roundcode');
		$query->from('#__sportsmanagement_project as pro');
		$query->join('INNER', '#__sportsmanagement_league as le on le.id = pro.league_id');
		$query->join('INNER', '#__sportsmanagement_round as r on r.id = pro.current_round');
		$query->where('le.published_act_season = 1 ');
		$query->where('pro.season_id IN (' . $seasons . ')');
		$query->order('le.country ASC, pro.name ASC');

		try{
        $db->setQuery($query);
		$result = $db->loadObjectList();
        }
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'error');
			$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'error');
		
		}
        }
		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
//echo '<pre>'.print_r($result,true).'</pre>';
		return $result;

	}

}
