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
 * @since	1.5.0a
 */
class sportsmanagementViewjlextcountries extends JView
{
	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$uri = JFactory::getURI();

		$filter_order		= $mainframe->getUserStateFromRequest($option.'co_filter_order',		'filter_order',		'objcountry.ordering',	'cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'co_filter_order_Dir',	'filter_order_Dir',	'',				'word');
		$search				= $mainframe->getUserStateFromRequest($option.'co_search',			'search',			'',				'string');
		$search=JString::strtolower($search);

		$items =& $this->get('Items');
		$total =& $this->get('Total');
		$pagination =& $this->get('Pagination');

		// table ordering
		$lists['order_Dir']=$filter_order_Dir;
		$lists['order']=$filter_order;

		// search filter
		$lists['search']=$search;

		$this->assignRef('user',JFactory::getUser());
		$this->assignRef('lists',$lists);
		$this->assignRef('items',$items);
		$this->assignRef('pagination',$pagination);
		$this->assignRef('request_url',$uri->toString());
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
		// Set toolbar items for the page
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRIES_TITLE'),'countries');
		JToolBarHelper::addNew('jlextcountry.add');
		JToolBarHelper::editList('jlextcountry.edit');
		JToolBarHelper::custom('jlextcountry.import','upload','upload',JText::_('JTOOLBAR_UPLOAD'),false);
		JToolBarHelper::archiveList('jlextcountry.export',JText::_('JTOOLBAR_EXPORT'));
		JToolBarHelper::deleteList('', 'jlextcountries.delete', 'JTOOLBAR_DELETE');
		JToolBarHelper::divider();
		//JToolBarHelper::onlinehelp();
	}
}
?>