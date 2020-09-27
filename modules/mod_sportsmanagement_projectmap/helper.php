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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;

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
     * modJSMprojectmaphelper::getmain_settings()
     * 
     * @return void
     */
    function getmain_settings()
    {
    $main_settings = '
        //General settings
		//width: "700", //or "responsive"
		
		width: "responsive",
    background_color: "#FFFFFF",
    background_transparent: "yes",
    popups: "detect",
    
		//State defaults
		state_description: "State description",
    state_color: "#88A4BC",
    state_hover_color: "#3B729F",
   // state_url: "https://simplemaps.com",
    border_size: 1.5,
    border_color: "#ffffff",
    all_states_inactive: "no",
    all_states_zoomable: "no",
    
		//Location defaults
		location_description: "Location description",
    location_color: "#FF0067",
    location_opacity: 0.8,
    location_hover_opacity: 1,
    location_url: "",
    location_size: 25,
    location_type: "square",
    location_border_color: "#FFFFFF",
    location_border: 2,
    location_hover_border: 2.5,
    all_locations_inactive: "no",
    all_locations_hidden: "no",
    
		//Label defaults
		label_color: "#ffffff",
    label_hover_color: "#ffffff",
    label_size: 22,
    label_font: "Arial",
    hide_labels: "no",
   
		//Zoom settings
		manual_zoom: "no",
    back_image: "no",
    arrow_box: "no",
    navigation_size: "40",
    navigation_color: "#f7f7f7",
    navigation_border_color: "#636363",
    initial_back: "no",
    initial_zoom: -1,
    initial_zoom_solo: "no",
    region_opacity: 1,
    region_hover_opacity: 0.6,
    zoom_out_incrementally: "yes",
    zoom_percentage: 0.99,
    zoom_time: 0.5,
    
		//Popup settings
		popup_color: "white",
    popup_opacity: 0.9,
    popup_shadow: 1,
    popup_corners: 5,
    popup_font: "12px/1.5 Verdana, Arial, Helvetica, sans-serif",
    popup_nocss: "no",
    
		//Advanced settings
		div: "map",
    auto_load: "yes",
    rotate: "0",
    url_new_tab: "no",
    images_directory: "default",
    import_labels: "no",
    fade_time: 0.1,
    link_text: "View Website"
    
    ';    
        
    return $main_settings;    
    }
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


/**
 * modJSMprojectmaphelper::createregions()
 * 
 * @param mixed $projects
 * @return void
 */
function createregions($projects)
{
    
    
foreach ($projects as $count_i => $project)
{    
$regionsname[$project->country_federation] = $project->federation_name;
$regionscountry[$project->country_federation][] = $project->country_alpha2;  
 
  
}
ksort($regionsname);
ksort($regionscountry);  
//echo '<pre>'.print_r($regionsname,true).'</pre>';
//echo '<pre>'.print_r($regionscountry,true).'</pre>';  

//HTMLHelper::image(Uri::root() . 'media/com_sportsmanagement/jl_images/discuss.gif', $imgTitle, array(' title' => $imgTitle, ' border' => 0, ' style' => 'vertical-align: middle'));  
foreach ($regionsname as $count_i => $name)
{   
$image = "<img src='https://simplemaps.com/static/img/frog.png' style='width: 75px' >";  
  
//echo '<pre>'.print_r($image,true).'</pre>';  
$regions[] = $count_i.': { name: "'.$name.'", description: "'.$image.'", states: ["'.implode("\",\"", $regionscountry[$count_i]).'"] }';   
}  
  
//echo '<pre>'.print_r($regions,true).'</pre>';    
//echo '<pre>'.print_r(implode(",",$regions),true).'</pre>';      
  
return implode(",\n",$regions);  
}


/**
 * modJSMprojectmaphelper::state_specific()
 * 
 * @param mixed $projects
 * @return void
 */
function createstate_specific($projects)
{
/*    
league_picture
project_picture
country_picture
federation_picture
*/


//echo '<pre>'.print_r($projects,true).'</pre>';    
foreach ($projects as $count_i => $project)
{    
//$regionsname[$project->country_federation] = $project->federation_name;
//$regionscountry[$project->country_federation][] = $project->country_alpha2;  
$routeparameter                       = array();
$routeparameter['cfg_which_database'] = 0;
$routeparameter['s']                  = 0;
$routeparameter['p']                  = $project->project_slug;
$routeparameter['type']               = 0;
$routeparameter['r']                  = 0;
$routeparameter['from']               = 0;
$routeparameter['to']                 = 0;
$routeparameter['division']           = 0;
$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking', $routeparameter);  
$state_specific[] = $project->country_alpha2.': {
      name: "'.Text::_($project->country_name).'",
      description: "'.Text::_($project->liganame).' :<br>'.Text::_($project->name).'",
      color: "default",
      hover_color: "default",
      url: "'.$link.'"
    }'; 
  
}    
    
//echo '<pre>'.print_r($state_specific,true).'</pre>';    
//echo '<pre>'.print_r(implode(",",$state_specific),true).'</pre>';  
return implode(",\n",$state_specific);  
}
 
  
}
