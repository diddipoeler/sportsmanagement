<?php 


defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

class sportsmanagementViewEventsRanking extends JViewLegacy
{
	function display($tpl=null)
	{
		// Get a refrence of the page instance in joomla
		$document = JFactory::getDocument();

		// read the config-data from template file
		$model = $this->getModel();
		//$config = sportsmanagementModelProject::getTemplateConfig($this->getName());
        sportsmanagementModelProject::setProjectID(JRequest::getInt('p',0),$model::$cfg_which_database);
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName(),$model::$cfg_which_database,__METHOD__);

		$this->assign('project', sportsmanagementModelProject::getProject($model::$cfg_which_database,__METHOD__));
		$this->assign('division', sportsmanagementModelProject::getDivision(0,$model::$cfg_which_database));
		$this->assignRef('matchid', $model::$matchid);
		$this->assign('overallconfig', sportsmanagementModelProject::getOverallConfig($model::$cfg_which_database));
		$this->assignRef('config', $config);
		$this->assign('teamid', $model->getTeamId());
		$this->assign('teams', sportsmanagementModelProject::getTeamsIndexedById(0,'name',$model::$cfg_which_database));
		$this->assign('favteams', sportsmanagementModelProject::getFavTeams($model::$cfg_which_database));
		$this->assign('eventtypes', sportsmanagementModelProject::getEventTypes(0,$model::$cfg_which_database));
		$this->assign('limit', $model->getLimit());
		$this->assign('limitstart', $model->getLimitStart());
		$this->assign('pagination', $this->get('Pagination'));
		$this->assign('eventranking', $model->getEventRankings($this->limit));
		$this->assign('multiple_events', count($this->eventtypes) > 1 );

		$prefix = JText::_('COM_SPORTSMANAGEMENT_EVENTSRANKING_PAGE_TITLE');
		if ( $this->multiple_events )
		{
			$prefix .= " - " . JText::_( 'COM_SPORTSMANAGEMENT_EVENTSRANKING_TITLE' );
		}
		else
		{
			// Next query will result in an array with exactly 1 statistic id
			$evid = array_keys($this->eventtypes);

			// Selected one valid eventtype, so show its name
			$prefix .= " - " . JText::_($this->eventtypes[$evid[0]]->name);
		}

		// Set page title
		$titleInfo = sportsmanagementHelper::createTitleInfo($prefix);
		if (!empty($this->teamid) && array_key_exists($this->teamid, $this->teams))
		{
			$titleInfo->team1Name = $this->teams[$this->teamid]->name;
		}
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

		parent::display($tpl);
	}

}
?>
