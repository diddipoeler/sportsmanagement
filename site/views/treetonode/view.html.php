<?php 
defined('_JEXEC') or die;


class sportsmanagementViewTreetonode extends sportsmanagementView
{

	function init()
	{
		// Get a refrence of the page instance in joomla
		//$document= JFactory::getDocument();

		//$model = $this->getModel();
		//$project = $model->getProject();
		//no treeko !!!
		$config = sportsmanagementModelProject::getTemplateConfig('tree');
		
		$this->project = sportsmanagementModelProject::getProject();
		$this->overallconfig = sportsmanagementModelProject::getOverallConfig();
		$this->config = $config;
		$this->node = $this->model->getTreetonode();
		
		$this->roundname = $this->model->getRoundName();
        
        //$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' roundname<br><pre>'.print_r($this->roundname,true).'</pre>'),'Notice');
        
		//$this->model = $model;
		
		// Set page title
		///TODO: treeto name, no project name
		$titleInfo = sportsmanagementHelper::createTitleInfo(JText::_('COM_JOOMLEAGUE_TREETO_PAGE_TITLE'));
		if (!empty($this->project))
		{
			$titleInfo->projectName = $this->project->name;
			$titleInfo->leagueName = $this->project->league_name;
			$titleInfo->seasonName = $this->project->season_name;
		}
		$division = sportsmanagementModelProject::getDivision(JFactory::getApplication()->input->getInt('division',0));
		if (!empty( $division ) && $division->id != 0)
		{
			$titleInfo->divisionName = $division->name;
		}
        if ( isset($this->config["page_title_format"]) )
        {
		$this->pagetitle = sportsmanagementHelper::formatTitle($titleInfo, $this->config["page_title_format"]);
        }
        else
        {
        $this->pagetitle = '';    
        }
		$this->document->setTitle($this->pagetitle);
		
		//parent::display($tpl);
	}
}
