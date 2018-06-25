<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage resultsmatrix
 */

defined('_JEXEC') or die('Restricted access');

require_once(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'pagination.php');
require_once(JPATH_COMPONENT_SITE.DS.'models'.DS.'matrix.php');
require_once(JPATH_COMPONENT_SITE.DS.'models'.DS.'results.php');
require_once(JPATH_COMPONENT_SITE.DS.'views'.DS.'results'.DS.'view.html.php');

jimport('joomla.application.component.view');
jimport('joomla.filesystem.file');
//jimport('joomla.html.pane');

/**
 * sportsmanagementViewResultsmatrix
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewResultsmatrix extends sportsmanagementView  
{

	
	/**
	 * sportsmanagementViewResultsmatrix::init()
	 * 
	 * @return void
	 */
	function init()
	{

		$params = $this->app->getParams();
        
        $this->document->addScript ( JUri::root(true).'/components/'.$this->option.'/assets/js/smsportsmanagement.js' );
        
		// add the matrix model
		$matrixmodel = new sportsmanagementModelMatrix();
		// add the matrix config file
		$matrixconfig = sportsmanagementModelProject::getTemplateConfig('matrix',$this->jinput->getInt('cfg_which_database',0));

		// add the results model
		$resultsmodel	= new sportsmanagementModelResults();
		$project = sportsmanagementModelProject::getProject($this->jinput->getInt('cfg_which_database',0));
        
		// add the results config file
		$resultsconfig = sportsmanagementModelProject::getTemplateConfig('results',$this->jinput->getInt('cfg_which_database',0));
		
		$mdlRound = JModelLegacy::getInstance("Round", "sportsmanagementModel");
		$roundcode = $mdlRound->getRoundcode($resultsmodel::$roundid);
		$rounds = sportsmanagementModelProject::getRoundOptions('ASC',$this->jinput->getInt('cfg_which_database',0));
		
		if (!isset($resultsconfig['switch_home_guest'])){$resultsconfig['switch_home_guest']=0;}
		if (!isset($resultsconfig['show_dnp_teams_icons'])){$resultsconfig['show_dnp_teams_icons']=0;}
		if (!isset($resultsconfig['show_results_ranking'])){$resultsconfig['show_results_ranking']=0;}
		$resultsconfig['show_matchday_dropdown']=0;
		// merge the 2 config files
		$config = array_merge($matrixconfig, $resultsconfig);

		$this->config = array_merge($this->overallconfig, $config);
		$this->tableconfig = $matrixconfig;
		$this->params = $params;
		$this->showediticon = $resultsmodel->getShowEditIcon();
		$this->division = $resultsmodel->getDivision();
		$this->divisionid = $matrixmodel::$divisionid;
		$this->division = $matrixmodel->getDivision();
		$this->teams = sportsmanagementModelProject::getTeamsIndexedByPtid( $matrixmodel::$divisionid,'name',$this->jinput->getInt('cfg_which_database',0) );
		$this->results = $matrixmodel->getMatrixResults( $project->id );
		$this->favteams = sportsmanagementModelProject::getFavTeams($this->jinput->getInt('cfg_which_database',0));
		$this->matches = $resultsmodel->getMatches($this->jinput->getInt('cfg_which_database',0));
		$this->round = $resultsmodel::$roundid;
		$this->roundid = $resultsmodel::$roundid;
		$this->roundcode = $roundcode;
		
		$options = self::getRoundSelectNavigation($rounds);
        
		$this->matchdaysoptions = $options;
        $routeparameter = array();
$routeparameter['cfg_which_database'] = $this->jinput->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $project->slug;
$routeparameter['r'] = $this->roundid;
$routeparameter['division'] = 0;
$routeparameter['mode'] = 0;
$routeparameter['order'] = 0;
$routeparameter['layout'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('resultsmatrix',$routeparameter);
		$this->currenturl = $link;
		$this->rounds = sportsmanagementModelProject::getRounds('ASC',$this->jinput->getInt('cfg_which_database',0));
		$this->favteams = sportsmanagementModelProject::getFavTeams($project->id);
		$this->projectevents = sportsmanagementModelProject::getProjectEvents(0,$this->jinput->getInt('cfg_which_database',0));
		$this->model = $resultsmodel;
		$this->isAllowed = $resultsmodel->isAllowed();

		$this->action = $this->uri->toString();
        
        if ( !isset($this->config['teamnames']) )
        {
        $this->config['teamnames'] = 'name';    
        }
        
        if ( !isset($this->config['image_placeholder']) )
        {
		$this->config['image_placeholder'] = '';
        }
        
		// Set page title
		$pageTitle = ($this->params->get('what_to_show_first', 0) == 0)
		? JText::_('COM_SPORTSMANAGEMENT_RESULTS_PAGE_TITLE').' & ' . JText :: _('COM_SPORTSMANAGEMENT_MATRIX_PAGE_TITLE')
		: JText::_('COM_SPORTSMANAGEMENT_MATRIX_PAGE_TITLE').' & ' . JText :: _('COM_SPORTSMANAGEMENT_RESULTS_PAGE_TITLE');
		if ( isset( $this->project->name ) )
		{
			$pageTitle .= ' - ' . $this->project->name;
		}
		$this->document->setTitle($pageTitle);
        
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$this->option.'/assets/css/'.$this->view.'.css'.'" type="text/css" />' ."\n";
        $this->document->addCustomTag($stylelink);
        
		/*
		 //build feed links
		 $feed = 'index.php?option=com_sportsmanagement&view=results&p='.$this->project->id.'&format=feed';
		 $rss = array('type' => 'application/rss+xml', 'title' => JText::_('COM_SPORTSMANAGEMENT_RESULTS_RSSFEED'));

		 // add the links
		 $document->addHeadLink(JRoute::_($feed.'&type=rss'), 'alternate', 'rel', $rss);
		 */
         
         sportsmanagementHelperHtml::$project = $project;
         sportsmanagementHelperHtml::$teams = $this->teams;
         
	}

	/**
	 * sportsmanagementViewResultsmatrix::getRoundSelectNavigation()
	 * 
	 * @param mixed $rounds
	 * @return
	 */
	function getRoundSelectNavigation(&$rounds)
	{
	   // Get a refrence of the page instance in joomla
		$document = JFactory::getDocument();
        
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' round<br><pre>'.print_r($round,true).'</pre>'),'');
        
		$options = array();
		foreach ($rounds as $r)
		{
		  $routeparameter = array();
$routeparameter['cfg_which_database'] = $jinput->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $this->project->slug;
$routeparameter['r'] = $r->slug;
$routeparameter['division'] = 0;
$routeparameter['mode'] = 0;
$routeparameter['order'] = 0;
$routeparameter['layout'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('resultsmatrix',$routeparameter);


			$options[] = JHTML::_('select.option', $link, $r->text);
		}
		return $options;
	}
    
}
?>
