<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * SportsManagement View
 */
class sportsmanagementViewPosition extends JView
{
	/**
	 * display method of Hello view
	 * @return void
	 */
	public function display($tpl = null) 
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
		$uri = JFactory::getURI();
        $model = $this->getModel();
        $document = JFactory::getDocument();
        // get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');
		$script = $this->get('Script');
 
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign the Data
		$this->form = $form;
		$this->item = $item;
		$this->script = $script;
        
        //build the html options for parent position
		$parent_id[]=JHTML::_('select.option','',JText::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_IS_P_POSITION'));
		$mdlPositions = JModel::getInstance("Positions", "sportsmanagementModel");
	    
        if ($res = $mdlPositions->getParentsPositions())
		{
			foreach ($res as $re){$re->text=JText::_($re->text);}
			$parent_id=array_merge($parent_id,$res);
		}
		$lists['parents']=$parent_id;
        //$lists['parents']=$parent_id;
		unset($parent_id);
        
        $mdlEventtypes = JModel::getInstance("Eventtypes", "sportsmanagementModel");
        //build the html select list for events
		$res=array();
		$res1=array();
		$notusedevents=array();
		if ($res = $mdlEventtypes->getEventsPosition($this->item->id))
		{
			$lists['position_events']=JHTML::_(	'select.genericlist',$res,'position_eventslist[]',
								' style="width:250px; height:300px;" class="inputbox" multiple="true" size="'.max(10,count($res)).'"',
								'value','text');
		}
		else
		{
			$lists['position_events']='<select name="position_eventslist[]" id="position_eventslist" style="width:250px; height:300px;" class="inputbox" multiple="true" size="10"></select>';
		}
		$res1 = $mdlEventtypes->getEvents();
		if ($res = $mdlEventtypes->getEventsPosition($this->item->id))
		{
			if($res1!="")
			foreach ($res1 as $miores1)
			{
				$used=0;
				foreach ($res as $miores)
				{
					if ($miores1->text == $miores->text){$used=1;}
				}
				if ($used == 0){$notusedevents[]=$miores1;}
			}
		}
		else
		{
			$notusedevents=$res1;
		}

		//build the html select list for events
		if (($notusedevents) && (count($notusedevents) > 0))
		{
			$lists['events']=JHTML::_(	'select.genericlist',$notusedevents,'eventslist[]',
							' style="width:250px; height:300px;" class="inputbox" multiple="true" size="'.max(10,count($notusedevents)).'"',
							'value','text');
		}
		else
		{
			$lists['events']='<select name="eventslist[]" id="eventslist" style="width:250px; height:300px;" class="inputbox" multiple="true" size="10"></select>';
		}
		unset($res);
		unset($res1);
		unset($notusedevents);
        
        // position statistics
        $mdlStatistics = JModel::getInstance("Statistics", "sportsmanagementModel");
		$position_stats = $mdlStatistics->getPositionStatsOptions($this->item->id);
		$lists['position_statistic']=JHTML::_(	'select.genericlist',$position_stats,'position_statistic[]',
							' style="width:250px; height:300px;" class="inputbox" id="position_statistic" multiple="true" size="'.max(10,count($position_stats)).'"',
							'value','text');
        $available_stats = $mdlStatistics->getAvailablePositionStatsOptions($this->item->id);
		$lists['statistic']=JHTML::_(	'select.genericlist',$available_stats,'statistic[]',
						' style="width:250px; height:300px;" class="inputbox" id="statistic" multiple="true" size="'.max(10,count($available_stats)).'"',
						'value','text');
                        
                        
        $document->addScript(JURI::base().'components/com_sportsmanagement/assets/js/sm_functions.js');
        
        $this->assignRef('lists',$lists);
        //$this->assign('cfg_which_media_tool', JComponentHelper::getParams($option)->get('cfg_which_media_tool',0) );
 
		// Set the toolbar
		$this->addToolBar();
 
		// Display the template
		parent::display($tpl);
 
		// Set the document
		$this->setDocument();
	}
 
	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
		JRequest::setVar('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->id == 0;
		$canDo = sportsmanagementHelper::getActions($this->item->id);
		JToolBarHelper::title($isNew ? JText::_('COM_SPORTSMANAGEMENT_POSITION_NEW') : JText::_('COM_SPORTSMANAGEMENT_POSITION_EDIT'), 'helloworld');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::apply('position.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('position.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('position.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel('position.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				// We can save the new record
				JToolBarHelper::apply('position.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('position.save', 'JTOOLBAR_SAVE');
 
				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create')) 
				{
					JToolBarHelper::custom('position.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::custom('position.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('position.cancel', 'JTOOLBAR_CLOSE');
		}
	}
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
		$isNew = $this->item->id == 0;
		$document = JFactory::getDocument();
		$document->setTitle($isNew ? JText::_('COM_HELLOWORLD_HELLOWORLD_CREATING') : JText::_('COM_HELLOWORLD_HELLOWORLD_EDITING'));
		$document->addScript(JURI::root() . $this->script);
		$document->addScript(JURI::root() . "/administrator/components/com_sportsmanagement/views/sportsmanagement/submitbutton.js");
		JText::script('COM_HELLOWORLD_HELLOWORLD_ERROR_UNACCEPTABLE');
	}
}
