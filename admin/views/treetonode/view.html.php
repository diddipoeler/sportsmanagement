<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file               
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

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

//jimport( 'joomla.application.component.view' );


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
     * @return
     */
    function init(  )
	{
		if ( $this->getLayout() == 'edit' || $this->getLayout() == 'edit_3' || $this->getLayout() == 'edit_4' )
		{
			$this->_displayForm(  );
			return;
		}

		//parent::display( $tpl );
	}

	/**
	 * sportsmanagementViewTreetonode::_displayForm()
	 * 
	 * @return void
	 */
	function _displayForm(  )
	{
		//$option = JFactory::getApplication()->input->getCmd('option');

		//$app	= JFactory::getApplication();
		//$project_id = $this->jinput->get('pid');
        $pid = $this->app->getUserState( $this->option . '.pid' );
        $tid = $this->app->getUserState( $this->option . '.tid' );
        
//        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' pid<br><pre>'.print_r($pid,true).'</pre>'),'Notice');
//        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' tid<br><pre>'.print_r($tid,true).'</pre>'),'Notice');
//        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' item<br><pre>'.print_r($this->item,true).'</pre>'),'Notice');

		//$uri 	= JFactory::getURI();
//		$user 	= JFactory::getUser();
		//$model	= $this->getModel();

		$lists = array();
		
	//	$node = $this->get('data');
		$match = $this->model->getNodeMatch();
		
        //$total = $this->get('Total');
		//$pagination = $this->get('Pagination');
		//$projectws = $this->get( 'Data', 'project' );
        $mdlProject = JModelLegacy::getInstance('Project', 'sportsmanagementModel');
		$projectws = $mdlProject->getProject($pid);
		
		$model = $this->getModel('project');
		$mdlTreetonodes = JModelLegacy::getInstance("Treetonodes", "sportsmanagementModel");
		$team_id[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM'));
		if( $projectteams = $mdlTreetonodes->getProjectTeamsOptions($pid) )
		{
			$team_id = array_merge($team_id,$projectteams);
		}
		$lists['team'] = $team_id;
		unset($team_id);

		//$this->assignRef( 'user',		JFactory::getUser() );
		$this->projectws = $projectws;
		$this->lists = $lists;
		//$this->assignRef( 'division',		$division );
//		$this->assignRef( 'division_id',	$division_id );
		$this->node = $this->item;
		$this->match = $match;
		//$this->pagination = $this->pagination;
		//parent::display( $tpl );
	}

}
?>