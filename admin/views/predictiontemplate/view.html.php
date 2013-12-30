<?php
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );
jimport('joomla.form.form');


/**
 * sportsmanagementViewPredictionTemplate
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementViewPredictionTemplate extends JView
{
	function display( $tpl = null )
	{
		$mainframe	=& JFactory::getApplication();

		
        
        	// get the Data
		//$form = $this->get('Form');
		$item = $this->get('Item');
		$script = $this->get('Script');
 
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}
		// Assign the Data
        $templatepath=JPATH_COMPONENT_SITE.DS.'settings';
    $xmlfile=$templatepath.DS.'default'.DS.$item->template.'.xml';
    
    //$jRegistry = new JRegistry;
//		$jRegistry->loadString($item->params, 'ini');
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementViewPredictionTemplate params<br><pre>'.print_r($item->params ,true).'</pre>'),'Notice');
        
        //$jRegistry->loadJSON($item->params);
        //$item->params = $jRegistry->toArray($item->params);
        //$mainframe->enqueueMessage(JText::_('sportsmanagementViewPredictionTemplate params<br><pre>'.print_r($item->params ,true).'</pre>'),'Notice');
        
		$form = JForm::getInstance($item->template, $xmlfile,array('control'=> 'params'));
		//$form->bind($jRegistry);
        $form->bind($item->params);
		$this->form = $form;
		$this->item = $item;
		$this->script = $script;
        $this->assign('user',JFactory::getUser() );
$this->addToolbar();
		parent::display( $tpl );
	}

	

  /**
	* Add the page title and toolbar.
	*
	* @since	1.7
	*/
	protected function addToolbar()
	{
	// Get a refrence of the page instance in joomla
        $document = JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
		
        
        JRequest::setVar('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId = $user->id;
		$isNew = $this->item->id == 0;
		$canDo = sportsmanagementHelper::getActions($this->item->id);
		JToolBarHelper::title($isNew ? JText::_('COM_SPORTSMANAGEMENT_PREDICTIONTEMPLATE_NEW') : JText::_('COM_SPORTSMANAGEMENT_PREDICTIONTEMPLATE_EDIT'), 'predtemplate');
		// Built the actions for new and existing records.
		if ($isNew) 
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::apply('predictiontemplate.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('predictiontemplate.save', 'JTOOLBAR_SAVE');
				JToolBarHelper::custom('predictiontemplate.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
			}
			JToolBarHelper::cancel('predictiontemplate.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($canDo->get('core.edit'))
			{
				// We can save the new record
				JToolBarHelper::apply('predictiontemplate.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('predictiontemplate.save', 'JTOOLBAR_SAVE');
 
				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canDo->get('core.create')) 
				{
					JToolBarHelper::custom('predictiontemplate.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			if ($canDo->get('core.create')) 
			{
				JToolBarHelper::custom('predictiontemplate.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
			}
			JToolBarHelper::cancel('predictiontemplate.cancel', 'JTOOLBAR_CLOSE');
		}
        JToolBarHelper::divider();
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
        JToolBarHelper::preferences('com_sportsmanagement');
        
	}		
	
}
?>
