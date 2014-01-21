<?php
defined('_JEXEC') or die('Restricted access');

//require_once (JPATH_COMPONENT . DS . 'helpers' . DS . 'pagination.php');

jimport('joomla.application.component.view');
jimport('joomla.filesystem.file');


//require_once( JPATH_COMPONENT_SITE . DS . 'helpers' . DS . 'ranking.php' );
//require_once( JPATH_COMPONENT_SITE . DS . 'helpers' . DS . 'route.php' );
//require_once( JPATH_COMPONENT_SITE . DS . 'helpers' . DS . 'simpleGMapGeocoder.php' );
//require_once( JPATH_COMPONENT_SITE . DS . 'models' . DS . 'project.php' );
//require_once (JPATH_COMPONENT_ADMINISTRATOR .DS.'models'.DS.'divisions.php');
//require_once (JPATH_COMPONENT_ADMINISTRATOR .DS.'models'.DS.'rounds.php');
//require_once (JPATH_COMPONENT_ADMINISTRATOR .DS.'models'.DS.'teams.php');
//require_once (JPATH_COMPONENT_ADMINISTRATOR .DS.'models'.DS.'projectteams.php');
//require_once (JPATH_COMPONENT_ADMINISTRATOR .DS.'helpers'.DS.'sportsmanagement.php');

class sportsmanagementViewRanking extends JView 
{
	
	function display($tpl = null) 
	{
		// Get a refrence of the page instance in joomla
		$document = JFactory :: getDocument();
		$uri = JFactory :: getURI();
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
        //$version = urlencode(JoomleagueHelper::getVersion());
		//$css='components/com_sportsmanagement/assets/css/tabs.css?v='.$version;
		//$document->addStyleSheet($css);
        $document->addScript ( JUri::root(true).'/components/'.$option.'/assets/js/smsportsmanagement.js' );

		$model = $this->getModel();
        $mdlProject = JModel::getInstance("Project", "sportsmanagementModel");
        $mdlDivisions = JModel::getInstance("Divisions", "sportsmanagementModel");
        $mdlProjectteams = JModel::getInstance("Projectteams", "sportsmanagementModel");
        $mdlTeams = JModel::getInstance("Teams", "sportsmanagementModel");
        
		$config = $mdlProject->getTemplateConfig($this->getName());
		$project = $mdlProject->getProject();
		
		$rounds = sportsmanagementHelper::getRoundsOptions($project->id, 'ASC', true);
			
		$mdlProject->setProjectId($project->id);
		
		//$map_config		= $model->getMapConfig();
		//$this->assignRef( 'mapconfig',		$map_config ); // Loads the project-template -settings for the GoogleMap
		$this->assignRef('model',			$model);
		$this->assignRef('project', $project);
        $extended = sportsmanagementHelper::getExtended($this->project->extended, 'project');
        $this->assignRef( 'extended', $extended );
        
		$this->assign('overallconfig', $mdlProject->getOverallConfig());
		$this->assignRef('tableconfig', $config);
		$this->assignRef('config', $config);
        
        


if ( ($this->overallconfig['show_project_rss_feed']) == 1 )
	  {
	  $mod_name               = "mod_jw_srfr";
	  $rssfeeditems = '';
    $rssfeedlink = $this->extended->getValue('COM_SPORTSMANAGEMENT_PROJECT_RSS_FEED');
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
		$this->assign('divisions', $mdlDivisions->getDivisions($project->id));
		$this->assignRef('type',      $model->type);
		$this->assignRef('from',      $model->from);
		$this->assignRef('to',        $model->to);
		$this->assignRef('divLevel',  $model->divLevel);
		$this->assignRef('currentRanking',  $model->currentRanking);
		$this->assignRef('previousRanking', $model->previousRanking);
		$this->assignRef('homeRank',      $model->homeRank);
		$this->assignRef('awayRank',      $model->awayRank);
        
        
       
		$this->assignRef('current_round', $model->current_round);
        
        // mannschaften holen
		$this->assign('teams',$mdlProject->getTeamsIndexedByPtid());
		
        
		
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
		
		
		
		
		$this->assign('previousgames', $model->getPreviousGames());
		$this->assign('action', $uri->toString());

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
		$this->assignRef('lists', $lists);

		if (!isset ($config['colors'])) {
			$config['colors'] = "";
		}

		$this->assign('colors', $mdlProject->getColors($config['colors']));
		//$this->assignRef('result', $model->getTeamInfo());
		//		$this->assignRef( 'pageNav', $model->pagenav( "ranking", count( $rounds ), $sr->to ) );
		//		$this->assignRef( 'pageNav2', $model->pagenav2( "ranking", count( $rounds ), $sr->to ) );

    // diddipoeler
		$this->assign( 'allteams', $mdlProjectteams->getAllProjectTeams($project->id) );
		
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' overallconfig<br><pre>'.print_r($this->overallconfig,true).'</pre>'),'');
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' config<br><pre>'.print_r($this->config,true).'</pre>'),'');   
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' currentRanking<br><pre>'.print_r($this->currentRanking,true).'</pre>'),'');
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' previousRanking<br><pre>'.print_r($this->previousRanking,true).'</pre>'),'');
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' homeRank<br><pre>'.print_r($this->homeRank,true).'</pre>'),'');
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' awayRank<br><pre>'.print_r($this->awayRank,true).'</pre>'),'');
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' teams<br><pre>'.print_r($this->teams,true).'</pre>'),'');
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' allteams<br><pre>'.print_r($this->allteams,true).'</pre>'),'');
        }
        
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' teams<br><pre>'.print_r($this->teams,true).'</pre>'),'');
        
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
	  //$this->assign('show_debug_info', JComponentHelper::getParams($option)->get('show_debug_info',0) );
	  
		// Set page title
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE' );
		if ( isset( $this->project->name ) )
		{
			$pageTitle .= ': ' . $this->project->name;
		}
		$document->setTitle( $pageTitle );
		$view = JRequest::getVar( "view") ;
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
		parent :: display($tpl);
	}
		
}
?>
