<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version    1.0.05
* @file       .php
* @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license    This file is part of SportsManagement.
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

defined('_JEXEC') or die;


/**
 * sportsmanagementViewTreetonode
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class sportsmanagementViewTreetonode extends sportsmanagementView
{

	/**
	 * sportsmanagementViewTreetonode::init()
	 * 
	 * @return void
	 */
	function init()
	{
		$config = sportsmanagementModelProject::getTemplateConfig('treetonode');
		
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
		
	}
}
