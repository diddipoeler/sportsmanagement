<?php defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class sportsmanagementViewTeamInfo extends JView
{
	function display( $tpl = null )
	{
		// Get a reference of the page instance in joomla
		$document	= JFactory::getDocument();
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
		$model		= $this->getModel();
		$config		= sportsmanagementModelProject::getTemplateConfig( $this->getName() );
		$project	= sportsmanagementModelProject::getProject();
        $this->assign( 'checkextrafields', $model->checkUserExtraFields() );
//        $mainframe->enqueueMessage(JText::_('teaminfo checkextrafields -> '.'<pre>'.print_r($this->checkextrafields,true).'</pre>' ),'');
		$this->assignRef( 'project', $project );
		$isEditor = sportsmanagementModelProject::hasEditPermission('projectteam.edit');

		if ( isset($this->project->id) )
		{
			$overallconfig = sportsmanagementModelProject::getOverallConfig();
			$this->assignRef( 'overallconfig',  $overallconfig);
			$this->assignRef( 'config', $config );
			$team = $model->getTeamByProject();
			$this->assignRef( 'team',  $team );
			$club = $model->getClub() ;
			$this->assignRef( 'club', $club);
			$seasons = $model->getSeasons( $config );
			$this->assignRef( 'seasons', $seasons );
			$this->assignRef('showediticon', $isEditor);
			$this->assignRef('projectteamid', $model->projectteamid);
            $this->assignRef('teamid', $model->teamid);
            
            $trainingData = $model->getTrainigData($this->project->id);
			$this->assignRef( 'trainingData', $trainingData );
            if ( $this->checkextrafields )
            {
            $this->assignRef( 'extrafields', $model->getUserExtraFields($model->teamid) );
            }

			$daysOfWeek=array(
				1 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_MONDAY'),
				2 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_TUESDAY'),
				3 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_WEDNESDAY'),
				4 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_THURSDAY'),
				5 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_FRIDAY'),
				6 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SATURDAY'),
				7 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SUNDAY')
			);
			$this->assignRef( 'daysOfWeek', $daysOfWeek );
            
      
      if ( $this->team->merge_clubs )
      {
      $merge_clubs = $model->getMergeClubs( $this->team->merge_clubs );
			$this->assignRef( 'merge_clubs', $merge_clubs );
      }
      
            
            if ($this->config['show_history_leagues']==1)
	{
            $this->assign( 'leaguerankoverview', $model->getLeagueRankOverview( $this->seasons ) );
			$this->assign( 'leaguerankoverviewdetail', $model->getLeagueRankOverviewDetail( $this->seasons ) );
}

		}
		
    $document->addScript( JURI::base(true).'/components/com_sportsmanagement/assets/js/highslide.js');
		$document->addStyleSheet( JURI::base(true) . '/components/com_sportsmanagement/assets/css/highslide/highslide.css' );
    
    $js = "hs.graphicsDir = '".JURI::base(true) . "/components/com_sportsmanagement/assets/css/highslide/graphics/"."';\n";
    $js .= "hs.outlineType = 'rounded-white';\n";
    $js .= "
    hs.lang = {
   cssDirection:     'ltr',
   loadingText :     'Lade...',
   loadingTitle :    'Klick zum Abbrechen',
   focusTitle :      'Klick um nach vorn zu bringen',
   fullExpandTitle : 'Zur Originalgr&ouml;&szlig;e erweitern',
   fullExpandText :  'Vollbild',
   creditsText :     '',
   creditsTitle :    '',
   previousText :    'Voriges',
   previousTitle :   'Voriges (Pfeiltaste links)',
   nextText :        'N&auml;chstes',
   nextTitle :       'N&auml;chstes (Pfeiltaste rechts)',
   moveTitle :       'Verschieben',
   moveText :        'Verschieben',
   closeText :       'Schlie&szlig;en',
   closeTitle :      'Schlie&szlig;en (Esc)',
   resizeTitle :     'Gr&ouml;&szlig;e wiederherstellen',
   playText :        'Abspielen',
   playTitle :       'Slideshow abspielen (Leertaste)',
   pauseText :       'Pause',
   pauseTitle :      'Pausiere Slideshow (Leertaste)',
   number :          'Bild %1/%2',
   restoreTitle :    'Klick um das Bild zu schlie&szlig;en, klick und ziehe um zu verschieben. Benutze Pfeiltasten für vor und zurück.'
};

    
    \n";
    
    $document->addScriptDeclaration( $js );
    	
		$extended = sportsmanagementHelper::getExtended($team->teamextended, 'team');
		$this->assignRef( 'extended', $extended );
    $this->assign('show_debug_info', JComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info',0) );
    $this->assign('use_joomlaworks', JComponentHelper::getParams('com_sportsmanagement')->get('use_joomlaworks',0) );
    
		// Set page title
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_PAGE_TITLE' );
		if ( isset( $this->team ) )
		{
			$pageTitle .= ': ' . $this->team->tname;
		}
		$document->setTitle( $pageTitle );

		parent::display( $tpl );
	}
}
?>