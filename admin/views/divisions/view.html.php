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
 * HTML View class for the Joomleague component
 *
 * @static
 * @package	Joomleague
 * @since	0.1
 */
class sportsmanagementViewDivisions extends JView
{

	function display( $tpl = null )
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		$db		= JFactory::getDBO();
		$uri	= JFactory::getURI();
        $model	= $this->getModel();

		$filter_state		= $mainframe->getUserStateFromRequest( $option .'.'.$model->_identifier.'.dv_filter_state','filter_state','','word');
		$filter_order		= $mainframe->getUserStateFromRequest( $option .'.'.$model->_identifier.'.dv_filter_order','filter_order','dv.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option .'.'.$model->_identifier.'.dv_filter_order_Dir','filter_order_Dir','','word');
		$search				= $mainframe->getUserStateFromRequest( $option .'.'.$model->_identifier.'.dv_search','search','','string');
		$search				= JString::strtolower( $search );
        
        $this->project_id	= $mainframe->getUserState( "$option.pid", '0' );
        $mdlProject = JModel::getInstance("Project", "sportsmanagementModel");
	    $project = $mdlProject->getProject($this->project_id);
        
/*
		$items		=& $this->get( 'Data' );
		$total		=& $this->get( 'Total' );
		$pagination =& $this->get( 'Pagination' );

		$projectws	=& $this->get( 'Data', 'projectws' );

		// state filter
		$lists['state']		= JHtml::_( 'grid.state',  $filter_state );

		// table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		// search filter
		$lists['search']	= $search;

		$this->assignRef( 'user',			JFactory::getUser() );
		$this->assignRef( 'lists',			$lists );
		$this->assignRef( 'items',			$items );
		$this->assignRef( 'projectws',		$projectws );
		$this->assignRef( 'pagination',		$pagination );
		$this->assignRef( 'request_url',	$uri->toString() );
		$this->addToolbar();
		parent::display( $tpl );
        */
        $items = $this->get('Items');
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');

		// table ordering
		$lists['order_Dir']=$filter_order_Dir;
		$lists['order']=$filter_order;
        // state filter
		$lists['state']	= JHtml::_('grid.state', $filter_state );

		// search filter
		$lists['search']=$search;

		$this->assign('user',JFactory::getUser());
        $this->assignRef('projectws',$project);
		$this->assignRef('lists',$lists);
		$this->assignRef('items',$items);
		$this->assignRef('pagination',$pagination);
		$this->assign('request_url',$uri->toString());
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
		$option = JRequest::getCmd('option');
        // Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
        // Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DIVS_TITLE' ),'divisions' );
        
        JToolBarHelper::publish('divisions.publish', 'JTOOLBAR_PUBLISH', true);
		JToolBarHelper::unpublish('divisions.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        JToolBarHelper::checkin('divisions.checkin');
		JToolBarHelper::divider();
		
		JToolBarHelper::addNew('division.add');
		JToolBarHelper::editList('division.edit');
        JToolBarHelper::deleteList('', 'divisions.delete', 'JTOOLBAR_DELETE');
		JToolBarHelper::divider();
        sportsmanagementHelper::ToolbarButtonOnlineHelp();
		JToolBarHelper::preferences($option);
	}
}
?>