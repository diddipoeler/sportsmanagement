<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage predictionproject
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * sportsmanagementViewpredictionproject
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewpredictionproject extends sportsmanagementView
{
	/**
	 * sportsmanagementViewpredictionproject::init()
	 * 
	 * @return
	 */
	public function init ()
	{
        
        // get the Data
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->script = $this->get('Script');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		$this->item->name = '';
		
		$this->app->setUserState( "$this->option.pid", $this->item->project_id );
        
        //$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__  .' item<br><pre>'.print_r($this->item,true).'</pre>'),'');
		 
		// Set the document
		$this->setDocument();
        
        switch ( $this->getLayout() )
        {
        case 'edit';
        case 'edit_3';
        case 'edit_4';
        $this->setLayout('edit_3'); 
        break;
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
        //$this->name = $this->item->name;
		$document = JFactory::getDocument();
		$document->setTitle($isNew ? JText::_('COM_HELLOWORLD_HELLOWORLD_CREATING') : JText::_('COM_HELLOWORLD_HELLOWORLD_EDITING'));
		$document->addScript(JURI::root() . $this->script);
		$document->addScript(JURI::root() . "/administrator/components/com_sportsmanagement/views/sportsmanagement/submitbutton.js");
		JText::script('COM_HELLOWORLD_HELLOWORLD_ERROR_UNACCEPTABLE');
	}
    
}
?>
