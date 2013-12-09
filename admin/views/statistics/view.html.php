<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
jimport('joomla.filesystem.file');


class sportsmanagementViewStatistics extends JView
{
	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$document = JFactory::getDocument();
		$user = JFactory::getUser();
		$uri = JFactory::getURI();
        $model	= $this->getModel();
		
		$filter_sports_type	= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.filter_sports_type',	'filter_sports_type','',	'int');
		$filter_state		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.filter_state',		'filter_state',		'',				'word');
		$filter_order		= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.filter_order',		'filter_order',		'obj.ordering',	'cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.filter_order_Dir',	'filter_order_Dir',	'',				'word');
		$search				= $mainframe->getUserStateFromRequest($option.'.'.$model->_identifier.'.search',			'search',			'',				'string');
		$search				= JString::strtolower($search);

		$items = $this->get('Items');
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');

		// state filter
		$lists['state']=JHtml::_('grid.state',$filter_state);

		// table ordering
		$lists['order_Dir']=$filter_order_Dir;
		$lists['order']=$filter_order;

		// search filter
		$lists['search']=$search;
		
				//build the html select list for sportstypes
		$sportstypes[]=JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_ADMIN_EVENTS_SPORTSTYPE_FILTER'),'id','name');
		//$allSportstypes =& JoomleagueModelSportsTypes::getSportsTypes();
		$allSportstypes = JModel::getInstance('SportsTypes','sportsmanagementmodel')->getSportsTypes();		
		
		$sportstypes=array_merge($sportstypes,$allSportstypes);
		$lists['sportstypes']=JHtml::_( 'select.genericList',
										$sportstypes,
										'filter_sports_type',
										'class="inputbox" onChange="this.form.submit();" style="width:120px"',
										'id',
										'name',
										$filter_sports_type);
		unset($sportstypes);

		$this->assignRef('user',$user);
		$this->assign('config',JFactory::getConfig());
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
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_STATISTICS_TITLE'),'statistics');
		
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::divider();
		JToolBarHelper::editList('statistic.edit');
		JToolBarHelper::addNew('statistic.add');
		JToolBarHelper::custom('statistic.import','upload','upload',JText::_('JTOOLBAR_UPLOAD'),false);
		JToolBarHelper::archiveList('statistic.export',JText::_('JTOOLBAR_EXPORT'));
		//JToolBarHelper::deleteList(JText::_('COM_SPORTSMANGEMENT_ADMIN_STATISTICS_DELETE_WARNING'),'statistic.fulldelete',JTEXT::_('COM_SPORTSMANGEMENT_ADMIN_STATISTICS_FULL_DELETE'));
		JToolBarHelper::divider();
		
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
        JToolBarHelper::preferences(JRequest::getCmd('option'));
	}
}
?>