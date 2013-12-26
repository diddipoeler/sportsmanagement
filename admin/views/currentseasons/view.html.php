<?php
/**
 * @copyright	Copyright (C) 2006-2013 JoomLeague.net. All rights reserved.
 * @license		GNU/GPL,see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License,and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the Joomleague component
 *
 * @static
 * @package	JoomLeague
 * @since	0.1
 */
class sportsmanagementViewCurrentseasons extends JView
{
	function display($tpl=null)
	{
		$option 	= JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		$uri		= JFactory::getUri();
        
        $items = $this->get('Items');
        // Get data from the model
		//$items		= $this->get('Data');
        $this->assignRef('items', $items);
        
        foreach ($this->items as $item)
	{
	   $item->count_projectdivisions = 0;
		$mdlProjectDivisions = JModel::getInstance("divisions", "sportsmanagementModel");
		$item->count_projectdivisions = $mdlProjectDivisions->getProjectDivisionsCount($item->id);
		
		$item->count_projectpositions = 0;
		$mdlProjectPositions = JModel::getInstance("Projectposition", "sportsmanagementModel");
		$item->count_projectpositions = $mdlProjectPositions->getProjectPositionsCount($item->id);
		
		$item->count_projectreferees = 0;
		$mdlProjectReferees = JModel::getInstance("Projectreferees", "sportsmanagementModel");
		$item->count_projectreferees = $mdlProjectReferees->getProjectRefereesCount($item->id);
		
		$item->count_projectteams = 0;
		$mdlProjecteams = JModel::getInstance("Projectteams", "sportsmanagementModel");
		$item->count_projectteams = $mdlProjecteams->getProjectTeamsCount($item->id);
        
        $item->count_matchdays = 0;
		$mdlRounds = JModel::getInstance("Rounds", "sportsmanagementModel");
		$item->count_matchdays = $mdlRounds->getRoundsCount($item->id);
	   
       }

$this->addToolbar();
		parent::display($tpl);
	}

	/**
	* Add the page title and toolbar.
	*
	* @since	1.6
	*/
	protected function addToolbar()
	{  		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
	// Set toolbar items for the page
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_TITLE'),'projectsettings');
		JToolBarHelper::divider();
		
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
		JToolBarHelper::preferences(JRequest::getCmd('option'));
	}
}
?>
