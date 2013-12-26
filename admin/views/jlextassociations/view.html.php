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
class sportsmanagementViewjlextassociations extends JView
{
	function display($tpl=null)
	{
		
		$mainframe = JFactory::getApplication();
    //$db = JFactory::getDBO();
		$uri = JFactory::getURI();
		$document	= JFactory::getDocument();
    $option = JRequest::getCmd('option');
   
    $model	= $this->getModel();
   
		
        
        //$mainframe->enqueueMessage(JText::_('Viewjlextassociations identifier<br><pre>'.print_r($model->_identifier,true).'</pre>'   ),'');
        $search_nation		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.search_nation','search_nation','','word');

		

		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.filter_order','filter_order','objassoc.ordering','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.filter_order_Dir','filter_order_Dir','','word');
		$search				= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.search','search','','string');
		$search=JString::strtolower($search);

		$items = $this->get('Items');
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');
        
        //build the html options for nation
		$nation[]=JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_NATION'));
		if ($res = Countries::getCountryOptions()){$nation=array_merge($nation,$res);}
		
        $lists['nation']=$nation;
        $lists['nation2']= JHtmlSelect::genericlist(	$nation,
																'search_nation',
																$inputappend.'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
																'value',
																'text',
																$search_nation);

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
	{  		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
	// Set toolbar items for the page
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_ASSOCIATIONS_TITLE'),'associations');
// 		JToolBarHelper::addNewX();
// 		JToolBarHelper::editListX();
		JToolBarHelper::addNew('jlextassociation.add');
		JToolBarHelper::editList('jlextassociation.edit');
		JToolBarHelper::custom('jlextassociation.import','upload','upload',JText::_('JTOOLBAR_UPLOAD'),false);
		JToolBarHelper::archiveList('jlextassociation.export',JText::_('JTOOLBAR_EXPORT'));
		//JToolBarHelper::deleteList();
		JToolBarHelper::deleteList('', 'jlextassociations.delete', 'JTOOLBAR_DELETE');
		JToolBarHelper::divider();

		sportsmanagementHelper::ToolbarButtonOnlineHelp();
	}
    
    

}
?>
