<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * SportsManagement View
 */
class sportsmanagementViewTeam extends JView
{
	/**
	 * display method of Hello view
	 * @return void
	 */
	public function display($tpl = null) 
	{
		$mainframe	= JFactory::getApplication();
		$option = JRequest::getCmd('option');
        $model		= $this->getModel();
        $show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
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
        
        $this->item->club_id = $mainframe->getUserState( "$option.club_id", '0' );;
		
		$extended = sportsmanagementHelper::getExtended($item->extended, 'team');
		$this->assignRef( 'extended', $extended );
		//$this->assign('cfg_which_media_tool', JComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_media_tool',0) );
        
        $this->assignRef( 'checkextrafields', sportsmanagementHelper::checkUserExtraFields() );
        if ( $this->checkextrafields )
        {
            $lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields($item->id);
            //$mainframe->enqueueMessage(JText::_('view -> '.'<pre>'.print_r($lists['ext_fields'],true).'</pre>' ),'');
        }
        
        if ( $show_debug_info )
        {
            $mainframe->enqueueMessage(JText::_('sportsmanagementViewTeam club_id<br><pre>'.print_r($this->item->club_id,true).'</pre>'),'');
        }
        
        //build the html select list for days of week
		if ($trainingData = $model->getTrainigData($this->item->id))
		{
			$daysOfWeek=array(	0 => JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'),
			1 => JText::_('MONDAY'),
			2 => JText::_('TUESDAY'),
			3 => JText::_('WEDNESDAY'),
			4 => JText::_('THURSDAY'),
			5 => JText::_('FRIDAY'),
			6 => JText::_('SATURDAY'),
			7 => JText::_('SUNDAY'));
			$dwOptions=array();
			foreach($daysOfWeek AS $key => $value)
            {
                $dwOptions[]=JHTML::_('select.option',$key,$value);
            }
			foreach ($trainingData AS $td)
			{
				$lists['dayOfWeek'][$td->id]=JHTML::_('select.genericlist',$dwOptions,'dayofweek['.$td->id.']','class="inputbox"','value','text',$td->dayofweek);
			}
			unset($daysOfWeek);
			unset($dwOptions);
		}
        $this->assignRef('trainingData',	$trainingData);
        $this->assignRef('lists',	$lists);
 
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
		JToolBarHelper::title($isNew ? JText::_('COM_SPORTSMANAGEMENT_TEAM_EDIT') : JText::_('COM_SPORTSMANAGEMENT_TEAM_EDIT'), 'helloworld');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::apply('team.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('team.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('team.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel('team.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				// We can save the new record
				JToolBarHelper::apply('team.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('team.save', 'JTOOLBAR_SAVE');
 
				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create')) 
				{
					JToolBarHelper::custom('team.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::custom('team.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('team.cancel', 'JTOOLBAR_CLOSE');
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
