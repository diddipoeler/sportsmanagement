<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');


class sportsmanagementViewjlextfederations extends JView
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
		$nation[]=JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
		if ($res = JSMCountries::getCountryOptions()){$nation=array_merge($nation,$res);}
		
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
	{
  		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        $option = JRequest::getCmd('option');
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
	// Set toolbar items for the page
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_FEDERATIONS_TITLE'),'federations');
// 		JToolBarHelper::addNewX();
// 		JToolBarHelper::editListX();
		JToolBarHelper::addNew('jlextfederation.add');
		JToolBarHelper::editList('jlextfederation.edit');
		JToolBarHelper::custom('jlextfederation.import','upload','upload',JText::_('JTOOLBAR_UPLOAD'),false);
		JToolBarHelper::archiveList('jlextfederation.export',JText::_('JTOOLBAR_EXPORT'));
		//JToolBarHelper::deleteList();
		JToolBarHelper::deleteList('', 'jlextfederation.delete', 'JTOOLBAR_DELETE');
		
        JToolBarHelper::divider();
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
        JToolBarHelper::preferences(JRequest::getCmd($option));
	}
    
    

}
?>