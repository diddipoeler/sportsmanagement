<?php
/**
 * @copyright	Copyright (C) 2013 fussballineuropa.de. All rights reserved.
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
 * HTML View class for the Sportsmanagement Component
 *
 * @static
 * @package	Sportsmanagement
 * @since	0.1
 */
class sportsmanagementViewSportsTypes extends JView
{

	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$uri = JFactory::getURI();
        $model	= $this->getModel();

		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.filter_order','filter_order','s.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.filter_order_Dir','filter_order_Dir','','word');
		$search				= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.search','search','','string');
		$search=JString::strtolower($search);

		$items = $this->get('Items');
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
        $myoptions = array();
		$myoptions[]		= JHtml::_( 'select.option', '0', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_SPORTSART_TEAM' ) );
		$myoptions[]		= JHtml::_( 'select.option', '1', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_SPORTSART_SINGLE' ) );

		// table ordering
		$lists['order_Dir']=$filter_order_Dir;
		$lists['order']=$filter_order;

		// search filter
		$lists['search']=$search;
        
        // sportart filter
		$lists['sportart']=$myoptions;

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
		$document	= JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
        // Set toolbar items for the page
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_SPORTSTYPES_TITLE'),'sporttypes');
		JToolBarHelper::addNewX('sportstype.add');
		JToolBarHelper::editListX('sportstype.edit');
		JToolBarHelper::custom('sportstype.import','upload','upload', JText::_('JTOOLBAR_UPLOAD'),false);
		JToolBarHelper::archiveList('sportstype.export', JText::_('JTOOLBAR_EXPORT'));
		JToolBarHelper::deleteList('','sportstypes.delete', 'JTOOLBAR_DELETE');
		JToolBarHelper::divider();
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
        JToolBarHelper::preferences(JRequest::getCmd('option'));
	}
}
?>