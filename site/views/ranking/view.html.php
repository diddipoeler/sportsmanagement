<?php
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_COMPONENT . DS . 'helpers' . DS . 'pagination.php');

jimport('joomla.application.component.view');
jimport('joomla.filesystem.file');

require_once (JLG_PATH_ADMIN .DS.'models'.DS.'divisions.php');

class JoomleagueViewRanking extends JLGView {
	
	function display($tpl = null) 
	{
		// Get a refrence of the page instance in joomla
		$document = JFactory :: getDocument();
		$uri = JFactory :: getURI();
        
        $version = urlencode(JoomleagueHelper::getVersion());
		$css='components/com_joomleague/assets/css/tabs.css?v='.$version;
		$document->addStyleSheet($css);

		$model = $this->getModel();
		$config = $model->getTemplateConfig($this->getName());
		$project = $model->getProject();
		
		$rounds = JoomleagueHelper::getRoundsOptions($project->id, 'ASC', true);
			
		$model->setProjectId($project->id);
		
		$map_config		= $model->getMapConfig();
		$this->assignRef( 'mapconfig',		$map_config ); // Loads the project-template -settings for the GoogleMap
		$this->assignRef('model',			$model);
		$this->assignRef('project', $project);
        $extended = $this->getExtended($this->project->extended, 'project');
        $this->assignRef( 'extended', $extended );
        
		$this->assignRef('overallconfig', $model->getOverallConfig());
		$this->assignRef('tableconfig', $config);
		$this->assignRef('config', $config);

if ( ($this->overallconfig['show_project_rss_feed']) == 1 )
	  {
	  $mod_name               = "mod_jw_srfr";
	  $rssfeeditems = '';
    $rssfeedlink = $this->extended->getValue('COM_JOOMLEAGUE_PROJECT_RSS_FEED');
    if ( $rssfeedlink )
    {
    $this->assignRef( 'rssfeeditems', $model->getRssFeeds($rssfeedlink,$this->overallconfig['rssitems']) );
    }
    else
    {
    $this->assignRef( 'rssfeeditems', $rssfeeditems );
    }
    
        
       }
       
       if ($this->config['show_half_of_season']==1)
	{
	   $model->part = 1;
       $model->from = 0;
       $model->to = 0;
       unset ($model->currentRanking);
	   unset ($model->previousRanking);
       $model->computeRanking();
       $this->assignRef('firstRank',      $model->currentRanking  );
       $model->part = 2;
       $model->from = 0;
       $model->to = 0;
       unset ($model->currentRanking);
	   unset ($model->previousRanking);
       $model->computeRanking();
       $this->assignRef('secondRank',      $model->currentRanking );
       $model->part = 0;
       unset ($model->currentRanking);
	   unset ($model->previousRanking);
	   }
       
		$model->computeRanking();

		$this->assignRef('round',     $model->round);
		$this->assignRef('part',      $model->part);
		$this->assignRef('rounds',    $rounds);
		$this->assignRef('divisions', $model->getDivisions());
		$this->assignRef('type',      $model->type);
		$this->assignRef('from',      $model->from);
		$this->assignRef('to',        $model->to);
		$this->assignRef('divLevel',  $model->divLevel);
		$this->assignRef('currentRanking',  $model->currentRanking);
		$this->assignRef('previousRanking', $model->previousRanking);
		$this->assignRef('homeRank',      $model->homeRank);
		$this->assignRef('awayRank',      $model->awayRank);
        
        
       
		$this->assignRef('current_round', $model->current_round);
		$this->assignRef('teams',			    $model->getTeamsIndexedByPtid());
		
		
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
		$this->assign('ranking_notes', implode(", ",$ranking_reason) );
		}
		else
		{
    $this->assign('ranking_notes', $no_ranking_reason );
    }
		
		
		
		
		$this->assignRef('previousgames', $model->getPreviousGames());
		$this->assign('action', $uri->toString());

		$frommatchday[] = JHTML :: _('select.option', '0', JText :: _('COM_JOOMLEAGUE_RANKING_FROM_MATCHDAY'));
		$frommatchday = array_merge($frommatchday, $rounds);
		$lists['frommatchday'] = $frommatchday;
		$tomatchday[] = JHTML :: _('select.option', '0', JText :: _('COM_JOOMLEAGUE_RANKING_TO_MATCHDAY'));
		$tomatchday = array_merge($tomatchday, $rounds);
		$lists['tomatchday'] = $tomatchday;

		$opp_arr = array ();
		$opp_arr[] = JHTML :: _('select.option', "0", JText :: _('COM_JOOMLEAGUE_RANKING_FULL_RANKING'));
		$opp_arr[] = JHTML :: _('select.option', "1", JText :: _('COM_JOOMLEAGUE_RANKING_HOME_RANKING'));
		$opp_arr[] = JHTML :: _('select.option', "2", JText :: _('COM_JOOMLEAGUE_RANKING_AWAY_RANKING'));

		$lists['type'] = $opp_arr;
		$this->assignRef('lists', $lists);

		if (!isset ($config['colors'])) {
			$config['colors'] = "";
		}

		$this->assignRef('colors', $model->getColors($config['colors']));
		//$this->assignRef('result', $model->getTeamInfo());
		//		$this->assignRef( 'pageNav', $model->pagenav( "ranking", count( $rounds ), $sr->to ) );
		//		$this->assignRef( 'pageNav2', $model->pagenav2( "ranking", count( $rounds ), $sr->to ) );

    // diddipoeler
		$mdlTeams = JModel::getInstance("Teams", "JoomleagueModel");
		$this->assignRef( 'allteams', $mdlTeams->getTeams() );
		
		if (($this->config['show_ranking_maps'])==1)
	  {
	  $this->geo = new simpleGMapGeocoder();
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
			$address_parts[] = Countries::getShortCountryName($row->club_country);
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
    $adressecountry_flag = Countries::getCountryFlag($row->club_country);
        
    //echo JURI::root().'<br>';
    						
		if ( $row->logo_big )
    {
    $path = JURI::root().$row->logo_big;
    }
    else
    {
    $path = JURI::root().'media/com_joomleague/placeholders/'.'placeholder_150.png';
    }
    
    //echo $path.'<br>';
    						
    $this->map->addMarker($lat, $lng, $row->club_name, $adressecountry_flag.' '.$row->address_string.'<br>',$path);
    }
    */
    
    }
    
  
//   $document->addScript($this->map->JLprintGMapsJS());
//   $document->addScriptDeclaration($this->map->JLshowMap(false));
  
	}
	  $this->assign('show_debug_info', JComponentHelper::getParams('com_joomleague')->get('show_debug_info',0) );
	  
		// Set page title
		$pageTitle = JText::_( 'COM_JOOMLEAGUE_RANKING_PAGE_TITLE' );
		if ( isset( $this->project->name ) )
		{
			$pageTitle .= ': ' . $this->project->name;
		}
		$document->setTitle( $pageTitle );
		
		parent :: display($tpl);
	}
		
}
?>
