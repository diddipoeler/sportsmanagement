<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.pdf.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage ranking
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\HTML\HTMLHelper;

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
		$document = Factory::getDocument();
		$uri = Factory::getURI();
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        
        $document->addScript ( Uri::root(true).'/components/'.$option.'/assets/js/smsportsmanagement.js' );

		$model = $this->getModel();
        $mdlDivisions = BaseDatabaseModel::getInstance("Divisions", "sportsmanagementModel");
        $mdlProjectteams = BaseDatabaseModel::getInstance("Projectteams", "sportsmanagementModel");
        $mdlTeams = BaseDatabaseModel::getInstance("Teams", "sportsmanagementModel");
        
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
          $mod_name = "mod_jw_srfr";
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
       
        if ($this->config['show_half_of_season'] == 1)
        {
            if ($this->config['show_table_4'] == 1)
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
        
        if ($this->config['show_table_1'] == 1)
	{
		$this->currentRanking = $model->currentRanking;
        }
        
		$this->previousRanking = $model->previousRanking;
        
        if ($this->config['show_table_2'] == 1)
	{
		$this->homeRank = $model->homeRank;
        }
        
        if ($this->config['show_table_3'] == 1)
	{
		$this->awayRank = $model->awayRank;
        }
        
        $this->current_round = sportsmanagementModelProject::getCurrentRound(__METHOD__.' '.Factory::getApplication()->input->getVar("view"));
        
        // mannschaften holen
		$this->teams = sportsmanagementModelProject::getTeamsIndexedByPtid();
		
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
       {
       $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' divisions'.'<pre>'.print_r($this->divisions,true).'</pre>' ),'');
       $app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' currentRanking'.'<pre>'.print_r($this->currentRanking,true).'</pre>' ),'');
       }
        
        //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' current_round'.'<pre>'.print_r($this->current_round,true).'</pre>' ),'');
		
		$no_ranking_reason = '';
		if ($this->config['show_notes'] == 1 )
	{
	$ranking_reason = array();
		Uri:: ( $this->teams as $teams ) 
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

		$frommatchday[] = HTMLHelper::_('select.option', '0', Text :: _('COM_SPORTSMANAGEMENT_RANKING_FROM_MATCHDAY'));
		$frommatchday = array_merge($frommatchday, $rounds);
		$lists['frommatchday'] = $frommatchday;
		$tomatchday[] = HTMLHelper::_('select.option', '0', Text :: _('COM_SPORTSMANAGEMENT_RANKING_TO_MATCHDAY'));
		$tomatchday = array_merge($tomatchday, $rounds);
		$lists['tomatchday'] = $tomatchday;

		$opp_arr = array ();
		$opp_arr[] = HTMLHelper::_('select.option', "0", Text :: _('COM_SPORTSMANAGEMENT_RANKING_FULL_RANKING'));
		$opp_arr[] = HTMLHelper::_('select.option', "1", Text :: _('COM_SPORTSMANAGEMENT_RANKING_HOME_RANKING'));
		$opp_arr[] = HTMLHelper::_('select.option', "2", Text :: _('COM_SPORTSMANAGEMENT_RANKING_AWAY_RANKING'));

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
        $app->enqueueMessage(Text::_(get_class($this).' '.__FUNCTION__.' overallconfig<br><pre>'.print_r($this->overallconfig,true).'</pre>'),'');
        $app->enqueueMessage(Text::_(get_class($this).' '.__FUNCTION__.' config<br><pre>'.print_r($this->config,true).'</pre>'),'');   
        $app->enqueueMessage(Text::_(get_class($this).' '.__FUNCTION__.' currentRanking<br><pre>'.print_r($this->currentRanking,true).'</pre>'),'');
        $app->enqueueMessage(Text::_(get_class($this).' '.__FUNCTION__.' previousRanking<br><pre>'.print_r($this->previousRanking,true).'</pre>'),'');
        $app->enqueueMessage(Text::_(get_class($this).' '.__FUNCTION__.' homeRank<br><pre>'.print_r($this->homeRank,true).'</pre>'),'');
        $app->enqueueMessage(Text::_(get_class($this).' '.__FUNCTION__.' awayRank<br><pre>'.print_r($this->awayRank,true).'</pre>'),'');
        $app->enqueueMessage(Text::_(get_class($this).' '.__FUNCTION__.' teams<br><pre>'.print_r($this->teams,true).'</pre>'),'');
        $app->enqueueMessage(Text::_(get_class($this).' '.__FUNCTION__.' allteams<br><pre>'.print_r($this->allteams,true).'</pre>'),'');
        }
        
        //$app->enqueueMessage(Text::_(get_class($this).' '.__FUNCTION__.' teams<br><pre>'.print_r($this->teams,true).'</pre>'),'');
        
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

  
  
  Uri:: ( $this->allteams as $row )
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
    
    }
    
  
//   $document->addScript($this->map->JLprintGMapsJS());
//   $document->addScriptDeclaration($this->map->JLshowMap(false));
  
	}

		// Set page title
		$pageTitle = Text::_( 'COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE' );
		if ( isset( $this->project->name ) )
		{
			$pageTitle .= ': ' . $this->project->name;
		}
		$document->setTitle( $pageTitle );
		$view = Factory::getApplication()->input->getVar( "view") ;
        $stylelink = '<link rel="stylesheet" href="'.Uri::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
        //$document->addCustomTag($stylelink);
		parent :: display($tpl);
	}
		
}
?>
