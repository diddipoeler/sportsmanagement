<?php 


defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

/**
 * sportsmanagementViewStatsRanking
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewStatsRanking extends JViewLegacy
{
	/**
	 * sportsmanagementViewStatsRanking::display()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function display($tpl = null)
	{
		// Get a refrence of the page instance in joomla
		$document = JFactory::getDocument();
        $app = JFactory::getApplication();

		// read the config-data from template file
		$model = $this->getModel();
		//$config = $model->getTemplateConfig($this->getName());
        sportsmanagementModelProject::setProjectID(JRequest::getInt('p',0),$model::$cfg_which_database);
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName(),$model::$cfg_which_database,__METHOD__);
		
		$this->assign('project', sportsmanagementModelProject::getProject($model::$cfg_which_database,__METHOD__) );
		$this->assign('division', sportsmanagementModelProject::getDivision(0,$model::$cfg_which_database) );
		$this->assign('teamid', $model->getTeamId() );
		
        $teams = sportsmanagementModelProject::getTeamsIndexedById(0,'name',$model::$cfg_which_database);
        //$teams = sportsmanagementModelProject::getTeamsIndexedByPtid(0,'name',$model::$cfg_which_database);
        
		if ( $this->teamid != 0 )
		{
			foreach ( $teams AS $k => $v)
			{
				if ($k != $this->teamid)
				{
					unset( $teams[$k] );
				}
			}
		}

		$this->assignRef('teams', $teams );
        
        
		$this->assign('overallconfig', sportsmanagementModelProject::getOverallConfig($model::$cfg_which_database) );
		$this->assignRef('config', $config );
		$this->assign('favteams', sportsmanagementModelProject::getFavTeams($model::$cfg_which_database) );
		$this->assign('stats', $model->getProjectUniqueStats() );
		$this->assign('playersstats', $model->getPlayersStats() );
		$this->assign('limit', $model->getLimit() );
		$this->assign('limitstart', $model->getLimitStart() );
		$this->assign('multiple_stats', count($this->stats) > 1 );
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teams<br><pre>'.print_r($this->teams,true).'</pre>'),'');    
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' playersstats<br><pre>'.print_r($this->playersstats,true).'</pre>'),'');
}
		$prefix = JText::_('COM_SPORTSMANAGEMENT_STATSRANKING_PAGE_TITLE');
		if ( $this->multiple_stats )
		{
			$prefix .= " - " . JText::_('COM_SPORTSMANAGEMENT_STATSRANKING_TITLE' );
		}
		else
		{
			// Next query will result in an array with exactly 1 statistic id
			$sid = array_keys($this->stats);
			// Take the first result then.
			$prefix .= " - " . $this->stats[$sid[0]]->name;
		}

		// Set page title
		$titleInfo = sportsmanagementHelper::createTitleInfo($prefix);
		if (!empty($this->project))
		{
			$titleInfo->projectName = $this->project->name;
			$titleInfo->leagueName = $this->project->league_name;
			$titleInfo->seasonName = $this->project->season_name;
		}
		if (!empty( $this->division ) && $this->division->id != 0)
		{
			$titleInfo->divisionName = $this->division->name;
		}
		$this->assign('pagetitle', sportsmanagementHelper::formatTitle($titleInfo, $this->config["page_title_format"]));
		$document->setTitle($this->pagetitle);
		
		parent::display( $tpl );
	}
}
?>
