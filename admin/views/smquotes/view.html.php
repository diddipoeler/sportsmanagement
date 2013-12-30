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
 * @since	1.5.0a
 */
class sportsmanagementViewsmquotes extends JView
{
	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$uri = JFactory::getURI();
        $model	= $this->getModel();

		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.filter_order','filter_order','obj.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.filter_order_Dir','filter_order_Dir','','word');
		//$search				= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.search','search','','string');
//        $this->filter_state		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.filter_state','filter_state','','word');
//        $this->filter_catid		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.filter_catid','filter_catid','','word');
        
		//$search=JString::strtolower($search);
        $this->state = $this->get('State');

		$items = $this->get('Items');
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
        
        //$this->state		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.filter_state','filter_state','','word');;
//        $mainframe->enqueueMessage(JText::_('sportsmanagementViewsmquotes state<br><pre>'.print_r($this->state,true).'</pre>'),'Notice');
//        $mainframe->enqueueMessage(JText::_('sportsmanagementViewsmquotes filter_state<br><pre>'.print_r($this->filter_state,true).'</pre>'),'Notice');
//        $mainframe->enqueueMessage(JText::_('sportsmanagementViewsmquotes filter_catid<br><pre>'.print_r($this->filter_catid,true).'</pre>'),'Notice');
        
        
  
		// table ordering
		$lists['order_Dir']=$filter_order_Dir;
		$lists['order']=$filter_order;

		// search filter
		//$lists['search']=$search;

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
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_QUOTES_TITLE'),'quotes');
		JToolBarHelper::addNew('smquote.add');
		JToolBarHelper::editList('smquote.edit');
		JToolBarHelper::custom('smquote.import','upload','upload',JText::_('JTOOLBAR_UPLOAD'),false);
        
        $bar = JToolBar::getInstance('toolbar');
        $bar->appendButton('Link', 'article', 'Kategorie', 'index.php?option=com_categories&view=categories&extension=com_sportsmanagement');
        
		JToolBarHelper::archiveList('smquote.export',JText::_('JTOOLBAR_EXPORT'));
		JToolBarHelper::deleteList('', 'smquotes.delete', 'JTOOLBAR_DELETE');
		JToolBarHelper::divider();
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
        JToolBarHelper::preferences(JRequest::getCmd('option'));
	}
}
?>
