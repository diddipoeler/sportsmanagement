<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');

require_once(JPATH_COMPONENT_SITE . DS . 'helpers' . DS . 'pagination.php');
require_once(JPATH_COMPONENT_SITE . DS . 'models' . DS . 'matrix.php' );
require_once(JPATH_COMPONENT_SITE . DS . 'models' . DS . 'results.php' );
require_once(JPATH_COMPONENT_SITE . DS . 'views' . DS . 'results' . DS . 'view.html.php' );

jimport('joomla.application.component.view');
jimport('joomla.filesystem.file');
jimport('joomla.html.pane');

/**
 * sportsmanagementViewResultsmatrix
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewResultsmatrix extends JView  
{

	/**
	 * sportsmanagementViewResultsmatrix::display()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	function display($tpl = null)
	{
		JHTML::_('behavior.mootools');
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
		$params = $mainframe->getParams();
		// get a reference of the page instance in joomla
		$document = JFactory :: getDocument();
		$uri = JFactory :: getURI();
        
        $document->addScript ( JUri::root(true).'/components/'.$option.'/assets/js/smsportsmanagement.js' );
        
		/*
        // add the css files
		$version = urlencode(JoomleagueHelper::getVersion());
		$css		= 'components/com_sportsmanagement/assets/css/tabs.css?v='.$version;
		$document->addStyleSheet($css);
        */
		// add the matrix model
		$matrixmodel = new sportsmanagementModelMatrix();
		// add the matrix config file
		$matrixconfig = sportsmanagementModelProject::getTemplateConfig('matrix');

		// add the results model
		$resultsmodel	= new sportsmanagementModelResults();
		$project = sportsmanagementModelProject::getProject();
		
        /*
		// add some javascript
		$version = urlencode(JoomleagueHelper::getVersion());
		$document->addScript( JURI::base(true).'/components/com_sportsmanagement/assets/js/results.js?v='.$version );
        */
        
		// add the results config file
		$resultsconfig = sportsmanagementModelProject::getTemplateConfig('results');
		
		$mdlRound = JModel::getInstance("Round", "sportsmanagementModel");
		$roundcode = $mdlRound->getRoundcode($resultsmodel->roundid);
		$rounds = sportsmanagementHelper::getRoundsOptions($project->id, 'ASC', true);
		
		
		if (!isset($resultsconfig['switch_home_guest'])){$resultsconfig['switch_home_guest']=0;}
		if (!isset($resultsconfig['show_dnp_teams_icons'])){$resultsconfig['show_dnp_teams_icons']=0;}
		if (!isset($resultsconfig['show_results_ranking'])){$resultsconfig['show_results_ranking']=0;}
		$resultsconfig['show_matchday_dropdown']=0;
		// merge the 2 config files
		$config = array_merge($matrixconfig, $resultsconfig);

		$this->assign('project', 		sportsmanagementModelProject::getProject());
		$this->assign('overallconfig',	sportsmanagementModelProject::getOverallConfig());
		$this->assign('config',			array_merge($this->overallconfig, $config));
		$this->assignRef('tableconfig',		$matrixconfig);
		$this->assignRef('params', 			$params);
		$this->assign('showediticon',	$resultsmodel->getShowEditIcon());
		$this->assign('division',		$resultsmodel->getDivision());

		$this->assign( 'divisionid', $matrixmodel->getDivisionID() );
		$this->assign( 'division', $matrixmodel->getDivision() );
		$this->assign( 'teams', sportsmanagementModelProject::getTeamsIndexedByPtid( $matrixmodel->getDivisionID() ) );
		$this->assign( 'results', $matrixmodel->getMatrixResults( $project->id ) );
		$this->assign( 'favteams', sportsmanagementModelProject::getFavTeams() );

		$this->assign('matches',			$resultsmodel->getMatches());
		$this->assignRef('round',			$resultsmodel->roundid);
		$this->assignRef('roundid',			$resultsmodel->roundid);
		$this->assignRef('roundcode',		$roundcode);
		
		$options = $this->getRoundSelectNavigation($rounds);

		$this->assignRef('matchdaysoptions',$options);
		$this->assign('currenturl', 		sportsmanagementHelperRoute::getResultsMatrixRoute($project->slug, $this->roundid));
		$this->assign('rounds',			sportsmanagementModelProject::getRounds());
		$this->assign('favteams',		sportsmanagementModelProject::getFavTeams($project->id));
		$this->assign('projectevents',	sportsmanagementModelProject::getProjectEvents());
		$this->assignRef('model',			$resultsmodel);
		$this->assign('isAllowed',		$resultsmodel->isAllowed());

		$this->assign('action', $uri->toString());
        
        //$this->assign('show_debug_info', JComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info',0) );
        //$this->assign('use_joomlaworks', JComponentHelper::getParams('com_sportsmanagement')->get('use_joomlaworks',0) );

		// Set page title
		$pageTitle = ($this->params->get('what_to_show_first', 0) == 0)
		? JText::_('COM_SPORTSMANAGEMENT_RESULTS_PAGE_TITLE').' & ' . JText :: _('COM_SPORTSMANAGEMENT_MATRIX_PAGE_TITLE')
		: JText::_('COM_SPORTSMANAGEMENT_MATRIX_PAGE_TITLE').' & ' . JText :: _('COM_SPORTSMANAGEMENT_RESULTS_PAGE_TITLE');
		if ( isset( $this->project->name ) )
		{
			$pageTitle .= ' - ' . $this->project->name;
		}
		$document->setTitle($pageTitle);
        
        $view = JRequest::getVar( "view") ;
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
		/*
		 //build feed links
		 $feed = 'index.php?option=com_sportsmanagement&view=results&p='.$this->project->id.'&format=feed';
		 $rss = array('type' => 'application/rss+xml', 'title' => JText::_('COM_SPORTSMANAGEMENT_RESULTS_RSSFEED'));

		 // add the links
		 $document->addHeadLink(JRoute::_($feed.'&type=rss'), 'alternate', 'rel', $rss);
		 */
		JView::display($tpl);
	}

	/**
	 * sportsmanagementViewResultsmatrix::getRoundSelectNavigation()
	 * 
	 * @param mixed $rounds
	 * @return
	 */
	function getRoundSelectNavigation(&$rounds)
	{
		$options = array();
		foreach ($rounds as $r)
		{
			$link = sportsmanagementHelperRoute::getResultsMatrixRoute($this->project->slug, $r->value);
			$options[] = JHTML::_('select.option', $link, $r->text);
		}
		return $options;
	}
    

    
}
?>
