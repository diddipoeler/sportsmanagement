<?php
/**
 * @copyright	Copyright (C) 2006-2011 JoomLeague.net. All rights reserved.
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
 * @since	1.5.0a
 */
class sportsmanagementViewrosterpositions extends JView
{
	function display($tpl=null)
	{
		$mainframe = JFactory::getApplication();
    $db = JFactory::getDBO();
		$uri = JFactory::getURI();
		$document	= JFactory::getDocument();
    $option = JRequest::getCmd('option');
    $model = $this->getModel();
    	
    

		

		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'l_filter_order','filter_order','obj.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'l_filter_order_Dir','filter_order_Dir','','word');
		$search				= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'l_search','search','','string');
		$search=JString::strtolower($search);

		$items = $this->get('Items');
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
        

		// table ordering
		$lists['order_Dir']=$filter_order_Dir;
		$lists['order']=$filter_order;

		// search filter
		$lists['search']=$search;

		$this->assign('user',JFactory::getUser());
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
	// Get a refrence of the page instance in joomla
        $document = JFactory::getDocument();
        $option = JRequest::getCmd('option');
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
// Set toolbar items for the page
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROSTERPOSITIONS_TITLE'),'rosterpositions');
        	JToolBarHelper::custom('rosterposition.addhome','new','new',JText::_('COM_SPORTSMAMAGEMENT_ADMIN_ROSTERPOSITIONS_HOME'),false);
		JToolBarHelper::custom('rosterposition.addaway','new','new',JText::_('COM_SPORTSMAMAGEMENT_ADMIN_ROSTERPOSITIONS_AWAY'),false);
		JToolBarHelper::editList('rosterposition.edit');
		JToolBarHelper::custom('rosterposition.import','upload','upload',JText::_('COM_SPORTSMAMAGEMENT_GLOBAL_CSV_IMPORT'),false);
		JToolBarHelper::archiveList('rosterposition.export',JText::_('COM_SPORTSMAMAGEMENT_GLOBAL_XML_EXPORT'));
		//JToolBarHelper::deleteList();
		JToolBarHelper::deleteList('', 'rosterposition.remove');
		JToolBarHelper::divider();
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
        JToolBarHelper::preferences(JRequest::getCmd($option));
       
       
       
    }   

}
?>
