<?php
/** Joomla Sports Management ein Programm zur Verwaltung für alle Sportarten
* @version 1.0.26
* @file		components/sportsmanagement/views/ranking/view.html.php
* @author diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license This file is part of Joomla Sports Management.
*
* Joomla Sports Management is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Joomla Sports Management is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Joomla Sports Management. If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von Joomla Sports Management.
*
* Joomla Sports Management ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* Joomla Sports Management wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
jimport('joomla.filesystem.file');

/**
 * sportsmanagementViewRanking
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewRanking extends JViewLegacy 
{
	
	/**
	 * sportsmanagementViewRanking::display()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function display($tpl = null) 
	{
		// Get a refrence of the page instance in joomla
		$document = JFactory :: getDocument();
		$uri = JFactory :: getURI();
        $app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        
        $document->addScript ( JUri::root(true).'/components/'.$option.'/assets/js/smsportsmanagement.js' );

		$model = $this->getModel();
        //$mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
        $mdlDivisions = JModelLegacy::getInstance("Divisions", "sportsmanagementModel");
        $mdlProjectteams = JModelLegacy::getInstance("Projectteams", "sportsmanagementModel");
        $mdlTeams = JModelLegacy::getInstance("Teams", "sportsmanagementModel");
        
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName());
		$project = sportsmanagementModelProject::getProject();
		
		$rounds = sportsmanagementHelper::getRoundsOptions($project->id, 'ASC', true);
			
		sportsmanagementModelProject::setProjectId($project->id);
		
		$this->model = $model;
		$this->project = $project;
        $extended = sportsmanagementHelper::getExtended($this->project->extended, 'project');
        $this->extended = $extended;
        
		$this->overallconfig = sportsmanagementModelProject::getOverallConfig();
		$this->tableconfig = $config;
		$this->config = $config;
        
        


if ( ($this->overallconfig['show_project_rss_feed']) == 1 )
	  {
	  $mod_name               = "mod_jw_srfr";
	  $rssfeeditems = '';
    $rssfeedlink = $this->extended->getValue('COM_SPORTSMANAGEMENT_PROJECT_RSS_FEED');
    if ( $rssfeedlink )
    {
    $this->rssfeeditems = $model->getRssFeeds($rssfeedlink,$this->overallconfig['rssitems']);
    }
    else
    {
    $this->rssfeeditems = $rssfeeditems;
    }
    
        
       }
       
       if ($this->config['show_half_of_season']==1)
	{
	   if ($this->config['show_table_4']==1)
	{
       $model->part = 1;
       $model->from = 0;
       $model->to = 0;
       unset ($model->currentRanking);
	   unset ($model->previousRanking);
       $model->computeRanking($model::$cfg_which_database);
       $this->firstRank = $model->currentRanking;
     }
     
     if ($this->config['show_table_5']==1)
	{  
       $model->part = 2;
       $model->from = 0;
       $model->to = 0;
       unset ($model->currentRanking);
	   unset ($model->previousRanking);
       $model->computeRanking($model::$cfg_which_database);
       $this->secondRank = $model->currentRanking;
     }  
       
       $model->part = 0;
       unset ($model->currentRanking);
	   unset ($model->previousRanking);
	   }
       
		$model->computeRanking($model::$cfg_which_database);

		$this->round = $model->round;
		$this->part = $model->part;
		$this->rounds = $rounds;
		$this->divisions = $mdlDivisions->getDivisions($project->id);
		$this->type = $model->type;
		$this->from = $model->from;
		$this->to = $model->to;
		$this->divLevel = $model->divLevel;
        
        if ($this->config['show_table_1']==1)
	{
		$this->currentRanking = $model->currentRanking;
        }
        
		$this->previousRanking = $model->previousRanking;
        
        if ($this->config['show_table_2']==1)
	{
		$this->homeRank = $model->homeRank;
        }
        
        if ($this->config['show_table_3']==1)
	{
		$this->awayRank = $model->awayRank;
        }
        
        $this->current_round = sportsmanagementModelProject::getCurrentRound(__METHOD__.' '.JFactory::getApplication()->input->getVar("view"));
        
        // mannschaften holen
		$this->teams = sportsmanagementModelProject::getTeamsIndexedByPtid();
		
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' divisions'.'<pre>'.print_r($this->divisions,true).'</pre>' ),'');
       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' currentRanking'.'<pre>'.print_r($this->currentRanking,true).'</pre>' ),'');
       }
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' current_round'.'<pre>'.print_r($this->current_round,true).'</pre>' ),'');
		
		$no_ranking_reason = '';
		if ($this->config['show_notes'] == 1 )
	{
	$ranking_reason = array();
		foreach ( $this->teams as $teams ) 
        {
        
        if ( $teams->start_points )
        {
        
        if ( $teams->start_points < 0 )
        {
        $color = "red";
        }
        else
        {
        $color = "green";
        }
        
        $ranking_reason[$teams->name] = '<font color="'.$color.'">'.$teams->name.': '.$teams->start_points.' Punkte Grund: '.$teams->reason.'</font>';
        }
        
        }
        }
		
		if ( sizeof($ranking_reason) > 0 )
		{
		$this->ranking_notes = implode(", ",$ranking_reason);
		}
		else
		{
    $this->ranking_notes = $no_ranking_reason;
    }
		
		
		
		
		$this->previousgames = $model->getPreviousGames();
		$this->action = $uri->toString();

		$frommatchday[] = JHTML :: _('select.option', '0', JText :: _('COM_SPORTSMANAGEMENT_RANKING_FROM_MATCHDAY'));
		$frommatchday = array_merge($frommatchday, $rounds);
		$lists['frommatchday'] = $frommatchday;
		$tomatchday[] = JHTML :: _('select.option', '0', JText :: _('COM_SPORTSMANAGEMENT_RANKING_TO_MATCHDAY'));
		$tomatchday = array_merge($tomatchday, $rounds);
		$lists['tomatchday'] = $tomatchday;

		$opp_arr = array ();
		$opp_arr[] = JHTML :: _('select.option', "0", JText :: _('COM_SPORTSMANAGEMENT_RANKING_FULL_RANKING'));
		$opp_arr[] = JHTML :: _('select.option', "1", JText :: _('COM_SPORTSMANAGEMENT_RANKING_HOME_RANKING'));
		$opp_arr[] = JHTML :: _('select.option', "2", JText :: _('COM_SPORTSMANAGEMENT_RANKING_AWAY_RANKING'));

		$lists['type'] = $opp_arr;
		$this->lists = $lists;

		if (!isset ($config['colors'])) {
			$config['colors'] = "";
		}

		$this->colors = sportsmanagementModelProject::getColors($config['colors']);

    // diddipoeler
		$this->allteams = $mdlProjectteams->getAllProjectTeams($project->id);
		
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' overallconfig<br><pre>'.print_r($this->overallconfig,true).'</pre>'),'');
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' config<br><pre>'.print_r($this->config,true).'</pre>'),'');   
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' currentRanking<br><pre>'.print_r($this->currentRanking,true).'</pre>'),'');
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' previousRanking<br><pre>'.print_r($this->previousRanking,true).'</pre>'),'');
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' homeRank<br><pre>'.print_r($this->homeRank,true).'</pre>'),'');
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' awayRank<br><pre>'.print_r($this->awayRank,true).'</pre>'),'');
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' teams<br><pre>'.print_r($this->teams,true).'</pre>'),'');
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' allteams<br><pre>'.print_r($this->allteams,true).'</pre>'),'');
        }
        
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' teams<br><pre>'.print_r($this->teams,true).'</pre>'),'');
        
		if (($this->config['show_ranking_maps'])==1)
	  {
	  $this->geo = new JSMsimpleGMapGeocoder();
	  $this->geo->genkml3($project->id,$this->allteams);
	  
// 	  $this->map = new simpleGMapAPI();
//   $this->geo = new simpleGMapGeocoder();
//   $this->map->setWidth($this->mapconfig['width']);
//   $this->map->setHeight($this->mapconfig['height']);
//   $this->map->setZoomLevel($this->mapconfig['map_zoom']); 
//   $this->map->setMapType($this->mapconfig['default_map_type']);
//   $this->map->setBackgroundColor('#d0d0d0');
//   $this->map->setMapDraggable(true);
//   $this->map->setDoubleclickZoom(false);
//   $this->map->setScrollwheelZoom(true);
//   $this->map->showDefaultUI(false);
//   $this->map->showMapTypeControl(true, 'DROPDOWN_MENU');
//   $this->map->showNavigationControl(true, 'DEFAULT');
//   $this->map->showScaleControl(true);
//   $this->map->showStreetViewControl(true);
//   $this->map->setInfoWindowBehaviour('SINGLE_CLOSE_ON_MAPCLICK');
//   $this->map->setInfoWindowTrigger('CLICK');
  
  //echo 'allteams <br><pre>'.print_r($this->allteams,true).'</pre><br>';

  
  
  foreach ( $this->allteams as $row )
    {
    $address_parts = array();
		if (!empty($row->club_address))
		{
			$address_parts[] = $row->club_address;
		}
		if (!empty($row->club_state))
		{
			$address_parts[] = $row->club_state;
		}
		if (!empty($row->club_location))
		{
			if (!empty($row->club_zipcode))
			{
				$address_parts[] = $row->club_zipcode. ' ' .$row->club_location;
			}
			else
			{
				$address_parts[] = $row->club_location;
			}
		}
		if (!empty($row->club_country))
		{
			$address_parts[] = JSMCountries::getShortCountryName($row->club_country);
		}
		$row->address_string = implode(', ', $address_parts);
//    $this->map->addMarkerByAddress($row->address_string, $row->team_name, '"<a href="'.$row->club_www.'" target="_blank">'.$row->club_www.'</a>"', "http://maps.google.com/mapfiles/kml/pal2/icon49.png");		
    
    /*
    $paramsdata	= $row->club_extended;
		$paramsdefs	= JLG_PATH_ADMIN . DS . 'assets' . DS . 'extended' . DS . 'club.xml';
		$extended	= new JLGExtraParams( $paramsdata, $paramsdefs );
		foreach ( $extended->getGroups() as $key => $groups )
		{
		$lat = $extended->get('JL_ADMINISTRATIVE_AREA_LEVEL_1_LATITUDE');
    $lng = $extended->get('JL_ADMINISTRATIVE_AREA_LEVEL_1_LONGITUDE');
		}
		
    if ( $lat && $lng )
    {
    $adressecountry_flag = JSMCountries::getCountryFlag($row->club_country);
        
    //echo JURI::root().'<br>';
    						
		if ( $row->logo_big )
    {
    $path = JURI::root().$row->logo_big;
    }
    else
    {
    $path = JURI::root().'media/com_sportsmanagement/placeholders/'.'placeholder_150.png';
    }
    
    //echo $path.'<br>';
    						
    $this->map->addMarker($lat, $lng, $row->club_name, $adressecountry_flag.' '.$row->address_string.'<br>',$path);
    }
    */
    
    }
    
  
//   $document->addScript($this->map->JLprintGMapsJS());
//   $document->addScriptDeclaration($this->map->JLshowMap(false));
  
	}

		// Set page title
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE' );
		if ( isset( $this->project->name ) )
		{
			$pageTitle .= ': ' . $this->project->name;
		}
		$document->setTitle( $pageTitle );
		$view = JFactory::getApplication()->input->getVar( "view") ;
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
        //$document->addCustomTag($stylelink);
		parent :: display($tpl);
	}
		
}
?>
