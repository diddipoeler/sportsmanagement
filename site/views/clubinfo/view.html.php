<?php 
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

//require_once( JPATH_COMPONENT . DS . 'helpers' . DS . 'pagination.php' );

class sportsmanagementViewClubInfo extends JView
{

	function display( $tpl = null )
	{
		// Get a refrence of the page instance in joomla
		$document = JFactory::getDocument();
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
		$model			= $this->getModel();
		$club			= $model->getClub() ;
		
		$config			= sportsmanagementModelProject::getTemplateConfig( $this->getName() );	
		$project 		= sportsmanagementModelProject::getProject();
		$overallconfig	= sportsmanagementModelProject::getOverallConfig();
		$teams			= $model->getTeamsByClubId();
		$stadiums	 	= $model->getStadiums();
		$playgrounds	= $model->getPlaygrounds();
		$isEditor		= sportsmanagementModelProject::hasEditPermission('club.edit');
		$address_string = $model->getAddressString();
		$map_config		= sportsmanagementModelProject::getMapConfig();
		$google_map		= $model->getGoogleMap( $map_config, $address_string );
        $this->assign( 'checkextrafields', $model->checkUserExtraFields() );
        //$mainframe->enqueueMessage(JText::_('clubinfo checkextrafields -> '.'<pre>'.print_r($this->checkextrafields,true).'</pre>' ),'');
		
        if ( $this->checkextrafields )
        {
            $this->assignRef( 'extrafields', $model->getUserExtraFields($club->id) );
        }
        
		$lat ='';
    $lng ='';
		
		$this->assignRef( 'project',		$project );
		$this->assignRef( 'overallconfig',	$overallconfig );
		$this->assignRef( 'config',			$config );

		$this->assignRef( 'showclubconfig',	$showclubconfig );
		$this->assignRef( 'club',			$club);
		$clubassoc			= $model->getClubAssociation($this->club->associations) ;
		$this->assignRef( 'clubassoc',			$clubassoc);

		$extended = sportsmanagementHelper::getExtended($club->extended, 'club');
		$this->assignRef( 'extended', $extended );
        $this->assignRef('model',				$model);

		$this->assignRef( 'teams',			$teams );
		$this->assignRef( 'stadiums',		$stadiums );
		$this->assignRef( 'playgrounds',	$playgrounds );
		$this->assignRef( 'showediticon',	$isEditor );

		$this->assignRef( 'address_string', $address_string);
		$this->assignRef( 'mapconfig',		$map_config ); // Loads the project-template -settings for the GoogleMap

		$this->assignRef( 'gmap',			$google_map );

    if ( ($this->config['show_club_rssfeed']) == 1 )
	  {
    $mod_name               = "mod_jw_srfr";
    $rssfeeditems = '';
    $rssfeedlink = $this->extended->getValue('COM_SPORTSMANAGEMENT_CLUB_RSS_FEED');
    
    //echo 'rssfeed<br><pre>'.print_r($rssfeedlink,true).'</pre><br>';
    if ( $rssfeedlink )
    {
    $this->assignRef( 'rssfeeditems', $model->getRssFeeds($rssfeedlink,$this->overallconfig['rssitems']) );
    }
    else
    {
    $this->assignRef( 'rssfeeditems', $rssfeeditems );
    }
    
    
    }
    
    
    
    
    
    if (($this->config['show_maps'])==1)
	  {
	
	}
	
        if (($this->config['show_maps'])==1)
	  {
            // diddipoeler
        $this->geo = new simpleGMapGeocoder();
        $this->geo->genkml3file($this->club->id,$this->address_string,'club',$this->club->logo_big,$this->club->name,$this->club->latitude,$this->club->longitude);  
}

    $this->assign('show_debug_info', JComponentHelper::getParams($option)->get('show_debug_info',0) );
    $this->assign('use_joomlaworks', JComponentHelper::getParams($option)->get('use_joomlaworks',0) );
    
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_CLUBINFO_PAGE_TITLE' );
		if ( isset( $this->club ) )
		{
			$pageTitle .= ': ' . $this->club->name;
		}
		$document->setTitle( $pageTitle );
		parent::display( $tpl );
	}
}
?>