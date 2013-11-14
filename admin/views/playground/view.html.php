<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * SportsManagement View
 */
class sportsmanagementViewPlayground extends JView
{
	/**
	 * display method of Hello view
	 * @return void
	 */
	public function display($tpl = null) 
	{
		$mainframe = JFactory::getApplication();
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
		
		$extended = sportsmanagementHelper::getExtended($item->extended, 'playground');
		$this->assignRef( 'extended', $extended );
        //$mainframe->enqueueMessage(JText::_('sportsmanagementViewPlayground display<br><pre>'.print_r($this->extended,true).'</pre>'),'Notice');
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementViewPlayground display form<br><pre>'.print_r($this->form,true).'</pre>'),'Notice');
        
/*        
$params = $item->extended;
$xmlfile = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sportsmanagement'.DS.'assets'.DS.'extended'.DS.  'playground.xml';  
$jRegistry = new JRegistry;
$jRegistry->loadString($params->toString('ini'), 'ini');
$form2 =& JForm::getInstance('extended', $xmlfile,array('control'=> ''), false, "/config");
$form2->bind($jRegistry);
*/

        // Convert the params field to an array.
			//$registry = new JRegistry;
//			$registry->loadString($item->extended);
//			$item->extended = $registry->toArray();
            
        // Convert the params field to a registry.
			//$params = new JRegistry;
			//$params->loadJSON($item->extended);
			//$params->toArray($this->extended);
            //$item->extended = $params->toArray($item->extended);
                
        //$mainframe->enqueueMessage(JText::_('sportsmanagementViewPlayground display<br><pre>'.print_r($item->extended,true).'</pre>'),'Notice');    
        //$this->assignRef( 'extended', $item->extended );
//		$this->assign('cfg_which_media_tool', JComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_media_tool',0) );
 
 
		// Set the toolbar
		$this->addToolBar();
		
//		echo '<pre>'.print_r($this->item,true).'</pre><br>'; 
 
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
		JToolBarHelper::title($isNew ? JText::_('COM_SPORTSMANAGEMENT_PLAYGROUND_NEW') : JText::_('COM_SPORTSMANAGEMENT_PLAYGROUND_EDIT'), 'helloworld');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::apply('playground.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('playground.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('playground.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel('playground.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				// We can save the new record
				JToolBarHelper::apply('playground.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('playground.save', 'JTOOLBAR_SAVE');
 
				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create')) 
				{
					JToolBarHelper::custom('playground.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::custom('playground.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('playground.cancel', 'JTOOLBAR_CLOSE');
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
