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

defined( '_JEXEC' ) or die( 'Restricted access' );

//require_once(JPATH_COMPONENT . DS . 'helpers' . DS . 'pagination.php');

jimport( 'joomla.application.component.view');

/**
 * sportsmanagementViewallprojectrounds
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewallprojectrounds extends JViewLegacy
{
	/**
	 * sportsmanagementViewallprojectrounds::display()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	function display( $tpl = null )
	{
		$option = JRequest::getCmd('option');
		$app = JFactory::getApplication();
    // Get a refrence of the page instance in joomla
		$document = JFactory::getDocument();
		$uri = JFactory::getURI();	
        
        $starttime = microtime(); 	
				
		$model = $this->getModel();
		//$config	= sportsmanagementModelProject::getTemplateConfig($this->getName());
		//$config	= $model->getAllRoundsParams();
    $project = sportsmanagementModelProject::getProject();
		
		$this->assignRef('project', $project);
		
//     $mdlProject = JModelLegacy::getInstance("Project", "JoomleagueModel");
		
    $this->assignRef('projectid', $this->project->id );
    
    $this->assign('projectmatches', $model->getProjectMatches() );
    
    $this->assign('rounds',			sportsmanagementModelProject::getRounds());
    
    $this->assign('overallconfig',	sportsmanagementModelProject::getOverallConfig());
 		$this->assign('config',			array_merge($this->overallconfig, $model->_params));
		//$this->assignRef('config', $model->_params);
    	
//     echo '<br />getRounds<pre>~'.print_r($this->rounds,true).'~</pre><br />';
    
    $this->assign('favteams',		sportsmanagementModelProject::getFavTeams($this->projectid));
//     echo '<br />getFavTeams<pre>~'.print_r($this->favteams,true).'~</pre><br />';
    
		$this->assign('projectteamid',		$model->getProjectTeamID($this->favteams));
		
    $this->assign('content',			$model->getRoundsColumn($this->rounds,$this->config));
        
		//$this->assign('show_debug_info', JComponentHelper::getParams('com_joomleague')->get('show_debug_info', 0));
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
		    
    

		parent::display( $tpl );
	}
}
?>