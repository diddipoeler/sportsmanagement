<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * SportsManagement View
 */
class sportsmanagementViewPerson extends JView
{
	/**
	 * display method of Hello view
	 * @return void
	 */
	public function display($tpl = null) 
	{
		$mainframe = JFactory::getApplication();
        $model = $this->getModel();
        $option = JRequest::getCmd('option');
        // Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
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
        
        if ( $this->item->latitude == 255 )
        {
            $mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_NO_GEOCODE'),'Error');
            $this->map = false;
        }
        else
        {
            $this->map = true;
        }
		
        $extended = sportsmanagementHelper::getExtended($item->extended, 'person');
		$this->assignRef( 'extended', $extended );

		//$this->assign('cfg_which_media_tool', JComponentHelper::getParams($option)->get('cfg_which_media_tool',0) );
 
    $this->assign( 'checkextrafields', sportsmanagementHelper::checkUserExtraFields() );
        if ( $this->checkextrafields )
        {
            $lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields($item->id);
            //$mainframe->enqueueMessage(JText::_('view -> '.'<pre>'.print_r($lists['ext_fields'],true).'</pre>' ),'');
        }
        
    $this->assignRef('lists',		$lists);
    
    
    $person_age = sportsmanagementHelper::getAge($this->form->getValue('birthday'),$this->form->getValue('deathday'));
//    $mainframe->enqueueMessage(JText::_('personagegroup person_age<br><pre>'.print_r($person_age,true).'</pre>'   ),'');
    $person_range = $model->getAgeGroupID($person_age);
//    $mainframe->enqueueMessage(JText::_('personagegroup person_range<br><pre>'.print_r($person_range,true).'</pre>'   ),'');
    if ( $person_range )
    {
        $this->form->setValue('agegroup_id', null, $person_range);
    }
    
    $document->addScript(JURI::base().'components/'.$option.'/assets/js/sm_functions.js');
    
    $javascript = "\n";
    $javascript .= "window.addEvent('domready', function() {";   
    $javascript .= 'StartEditshowPersons('.$form->getValue('person_art').');' . "\n"; 
    $javascript .= '});' . "\n"; 
    $document->addScriptDeclaration( $javascript );
    
    
    
    
    
    //echo 'ext_fields<br><pre>'.print_r($this->ext_fields, true).'</pre><br>';

    
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
  		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        $option = JRequest::getCmd('option');
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
		JRequest::setVar('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->id == 0;
		$canDo = sportsmanagementHelper::getActions($this->item->id);
		JToolBarHelper::title($isNew ? JText::_('COM_SPORTSMANAGEMENT_PERSON_NEW') : JText::_('COM_SPORTSMANAGEMENT_PERSON_EDIT'), 'person');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::apply('person.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('person.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('person.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel('person.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				// We can save the new record
				JToolBarHelper::apply('person.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('person.save', 'JTOOLBAR_SAVE');
 
				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create')) 
				{
					JToolBarHelper::custom('person.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::custom('person.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('person.cancel', 'JTOOLBAR_CLOSE');
		}
        
        JToolBarHelper::divider();
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
        JToolBarHelper::preferences(JRequest::getCmd($option));
        
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
