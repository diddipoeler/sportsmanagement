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

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );


/**
 * sportsmanagementViewteampersons
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewteampersons extends JViewLegacy
{

	function display( $tpl = null )
	{
		$mainframe	= JFactory::getApplication();
		$option = JRequest::getCmd('option');
        $uri		= JFactory::getURI();
		$document = JFactory::getDocument();
        $model	= $this->getModel();
        $starttime = microtime(); 
        
        $this->state = $this->get('State'); 
        $this->sortDirection = $this->state->get('list.direction');
        $this->sortColumn = $this->state->get('list.ordering');
        
        //$mainframe->enqueueMessage(__METHOD__.' '.__LINE__.' state<br><pre>'.print_r($this->state, true).'</pre><br>','Notice');

/*	
		$baseurl    = JURI::root();
		$document->addScript($baseurl.'administrator/components/com_sportsmanagement/assets/js/autocompleter/1_4/Autocompleter.js');
		$document->addScript($baseurl.'administrator/components/com_sportsmanagement/assets/js/autocompleter/1_4/Autocompleter.Request.js');
		$document->addScript($baseurl.'administrator/components/com_sportsmanagement/assets/js/autocompleter/1_4/Observer.js');
		$document->addScript($baseurl.'administrator/components/com_sportsmanagement/assets/js/autocompleter/1_4/quickaddperson.js');
		$document->addStyleSheet($baseurl.'administrator/components/com_sportsmanagement/assets/css/Autocompleter.css');			
*/


        
        $items = $this->get('Items');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
        $this->project_id	= $mainframe->getUserState( "$option.pid", '0' );
        
        $this->_persontype = JRequest::getVar('persontype');
        if ( empty($this->_persontype) )
        {
            $this->_persontype	= $mainframe->getUserState( "$option.persontype", '0' );
        }
        
        $mdlProject = JModelLegacy::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($this->project_id);
        
        $this->project_team_id	= JRequest::getVar('project_team_id');
        $this->team_id	= JRequest::getVar('team_id');
        
        
        if ( !$this->team_id )
        {
            $this->team_id	= $mainframe->getUserState( "$option.team_id", '0' );
        }
        if ( !$this->project_team_id )
        {
            $this->project_team_id	= $mainframe->getUserState( "$option.project_team_id", '0' );
        }
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(__METHOD__.' '.__LINE__.' _persontype<br><pre>'.print_r($this->_persontype, true).'</pre><br>','Notice');
        $mainframe->enqueueMessage(__METHOD__.' '.__LINE__.' project_team_id<br><pre>'.print_r($this->project_team_id, true).'</pre><br>','Notice');
        $mainframe->enqueueMessage(__METHOD__.' '.__LINE__.' team_id<br><pre>'.print_r($this->team_id, true).'</pre><br>','Notice');
        }
        
        $mdlProjectTeam = JModelLegacy::getInstance("ProjectTeam", "sportsmanagementModel");
	    $project_team = $mdlProjectTeam->getProjectTeam($this->team_id);
        
        //build the html options for position
		$position_id[]=JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PLAYER_FUNCTION'));
        $mdlPositions = JModelLegacy::getInstance("Positions", "sportsmanagementModel");
        
        if ( $this->_persontype == 1 )
        {
	    $project_ref_positions = $mdlPositions->getPlayerPositions($this->project_id);
        }
        elseif ( $this->_persontype == 2 )
        {
	    $project_ref_positions = $mdlPositions->getStaffPositions($this->project_id);
        }
        
        if ( $project_ref_positions )
        {
        $position_id = array_merge($position_id,$project_ref_positions);
        }
		$lists['project_position_id'] = $position_id;
		unset($position_id);


        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(get_class($this).' '.__FUNCTION__.' items<br><pre>'.print_r($items, true).'</pre><br>','Notice');
        }

		$this->assign('user',JFactory::getUser());
		$this->assign('config',JFactory::getConfig());
		$this->assignRef('lists',$lists);
		$this->assignRef('items',$items);
		$this->assignRef('pagination',$pagination);
		$this->assign('request_url',$uri->toString());
        $this->assignRef('project',$project);
        $this->assignRef('project_team',$project_team);
		$this->addToolbar();
		parent::display($tpl);
        
        
	}
    
	/**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
	// Get a refrence of the page instance in joomla
        $document = JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
		$mainframe	= JFactory::getApplication();
		$option = JRequest::getCmd('option');
        // store the variable that we would like to keep for next time
        // function syntax is setUserState( $key, $value );
        $mainframe->setUserState( "$option.project_team_id", $this->project_team_id );
        $mainframe->setUserState( "$option.team_id", $this->team_id );
        $mainframe->setUserState( "$option.persontype", $this->_persontype );
        
        // Set toolbar items for the page
        if ( $this->_persontype == 1 )
        {
		JToolBarHelper::title( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_TITLE' ),'teamplayers' );
        }
        elseif ( $this->_persontype == 2 )
        {
        JToolBarHelper::title( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TSTAFFS_TITLE' ),'teamstaffs' );
        }

		JToolBarHelper::publishList('teampersons.publish');
		JToolBarHelper::unpublishList('teampersons.unpublish');
		JToolBarHelper::apply( 'teampersons.saveshort', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_APPLY' ) );
		JToolBarHelper::divider();

		//JToolBarHelper::custom( 'teamplayer.assign', 'upload.png', 'upload_f2.png', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_ASSIGN' ), false );
        sportsmanagementHelper::ToolbarButton('assignplayers','upload',JText::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_ASSIGN'),'persons',0);
		//JToolBarHelper::custom( 'teamplayer.remove', 'cancel.png', 'cancel_f2.png', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_UNASSIGN' ), false );
        JToolBarHelper::deleteList('', 'teampersons.delete');
		JToolBarHelper::divider();

		JToolBarHelper::back( 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_BACK', 'index.php?option=com_sportsmanagement&view=projectteams' );
		JToolBarHelper::divider();

		sportsmanagementHelper::ToolbarButtonOnlineHelp();
		JToolBarHelper::preferences(JRequest::getCmd('option'));
	}
}
?>
