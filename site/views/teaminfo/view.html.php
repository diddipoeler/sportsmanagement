<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage teaminfo
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * sportsmanagementViewTeamInfo
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewTeamInfo extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewTeamInfo::init()
	 * 
	 * @return void
	 */
	function init()
	{

        $this->checkextrafields = sportsmanagementHelper::checkUserExtraFields('frontend',sportsmanagementModelTeamInfo::$cfg_which_database);

		if ( $this->project->id )
		{
			$this->team = sportsmanagementModelTeamInfo::getTeamByProject(1);
			$this->club = sportsmanagementModelTeamInfo::getClub();
			$this->seasons = sportsmanagementModelTeamInfo::getSeasons( $this->config,0 );
			$this->showediticon =sportsmanagementModelProject::hasEditPermission('projectteam.edit');
			$this->projectteamid = sportsmanagementModelTeamInfo::$projectteamid;
            $this->teamid = sportsmanagementModelTeamInfo::$teamid;
			$this->trainingData = sportsmanagementModelTeamInfo::getTrainigData($this->project->id);
            if ( $this->checkextrafields )
            {
            $this->extrafields = sportsmanagementHelper::getUserExtraFields(sportsmanagementModelTeamInfo::$teamid,'frontend',sportsmanagementModelTeamInfo::$cfg_which_database);
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
			$this->daysOfWeek = $daysOfWeek;
                  
	if ( $this->team->merge_clubs )
	{
		$this->merge_clubs = sportsmanagementModelTeamInfo::getMergeClubs( $this->team->merge_clubs );
	}

     
//	if ( $this->config['show_history_leagues'] )
//	{
		$this->seasons = sportsmanagementModelTeamInfo::getSeasons( $this->config,1 );
		$this->leaguerankoverview = sportsmanagementModelTeamInfo::getLeagueRankOverview( $this->seasons );
		$this->leaguerankoverviewdetail = sportsmanagementModelTeamInfo::getLeagueRankOverviewDetail( $this->seasons );
//	}

		}
    	
		$this->extended = sportsmanagementHelper::getExtended($this->team->teamextended, 'team');
    
		// Set page title
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_TEAMINFO_PAGE_TITLE' );
		if ( isset( $this->team ) )
		{
			$pageTitle .= ': ' . $this->team->tname;
		}
		$this->document->setTitle( $pageTitle );

/**
 * da wir komplett mit bootstrap arbeiten benötigen wir das nicht mehr        
 * $view = $jinput->getVar( "view") ;
 * $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
 * $document->addCustomTag($stylelink);
 */
        
        if ( !isset($this->config['table_class']) )
        {
            $this->config['table_class'] = 'table';
        }

	}
}
?>
