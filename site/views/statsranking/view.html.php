<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

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
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;

		// read the config-data from template file
		$model = $this->getModel();
		//$config = $model->getTemplateConfig($this->getName());
        sportsmanagementModelProject::setProjectID($jinput->getInt('p',0),$model::$cfg_which_database);
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName(),$model::$cfg_which_database,__METHOD__);
		
		$this->project = sportsmanagementModelProject::getProject($model::$cfg_which_database,__METHOD__);
		$this->division = sportsmanagementModelProject::getDivision(0,$model::$cfg_which_database);
		$this->teamid = $model->getTeamId();
		
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

		$this->teams = $teams;
		$this->overallconfig = sportsmanagementModelProject::getOverallConfig($model::$cfg_which_database);
		$this->config = $config;
		$this->favteams = sportsmanagementModelProject::getFavTeams($model::$cfg_which_database);
		$this->stats = $model->getProjectUniqueStats();
		$this->playersstats = $model->getPlayersStats();
		$this->limit = $model->getLimit();
		$this->limitstart = $model->getLimitStart();
		$this->multiple_stats = count($this->stats) > 1 ;
        
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
		$this->pagetitle = sportsmanagementHelper::formatTitle($titleInfo, $this->config["page_title_format"]);
		$document->setTitle($this->pagetitle);
		
        $this->headertitle = $this->pagetitle;
        
		parent::display( $tpl );
	}
}
?>
