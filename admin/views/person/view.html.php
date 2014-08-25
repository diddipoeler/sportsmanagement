<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 

/**
 * sportsmanagementViewPerson
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewPerson extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewPerson::display()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	public function init ()
	{
		$mainframe = JFactory::getApplication();
        $model = $this->getModel();
        $option = JRequest::getCmd('option');
        // Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        $starttime = microtime(); 
        
    // get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');
        
        
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' item<br><pre>'.print_r($item,true).'</pre>'),'');
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' form<br><pre>'.print_r($form,true).'</pre>'),'');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
        }
        
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
        
        $this->form->setValue('sports_type_id', 'request', $this->item->sports_type_id);
        $this->form->setValue('position_id', 'request', $this->item->position_id);
        $this->form->setValue('agegroup_id', 'request', $this->item->agegroup_id);
        
        $this->form->setValue('person_art', 'request', $this->item->person_art);
        $this->form->setValue('person_id1', 'request', $this->item->person_id1);
        $this->form->setValue('person_id2', 'request', $this->item->person_id2);
        
        if ( $this->item->latitude == 255 )
        {
            $mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_NO_GEOCODE'),'Error');
            $this->map = false;
        }
        else
        {
            $this->map = true;
        }
        
		$isNew = $this->item->id == 0;
        if ( $isNew )
        {
            $this->form->setValue('person_art', null, '1');
        }
        
        $extended = sportsmanagementHelper::getExtended($item->extended, 'person');
		$this->assignRef( 'extended', $extended );
        $extendeduser = sportsmanagementHelper::getExtendedUser($this->item->extendeduser, 'person');		
		$this->assignRef( 'extendeduser', $extendeduser );

		//$this->assign('cfg_which_media_tool', JComponentHelper::getParams($option)->get('cfg_which_media_tool',0) );
 
    $this->assign( 'checkextrafields', sportsmanagementHelper::checkUserExtraFields() );
        if ( $this->checkextrafields )
        {
            $lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields($item->id);
            //$mainframe->enqueueMessage(JText::_('view -> '.'<pre>'.print_r($lists['ext_fields'],true).'</pre>' ),'');
        }
        
    $this->assignRef('lists',$lists);
    
    
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
    $javascript .= 'StartEditshowPersons('.$form->getValue('request_person_art').');' . "\n"; 
    $javascript .= '});' . "\n"; 
    $document->addScriptDeclaration( $javascript );
    
    //$mainframe->enqueueMessage(JText::_(' person_art<br><pre>'.print_r($form->getValue('person_art'),true).'</pre>'),'');
    //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' form<br><pre>'.print_r($form,true).'</pre>'),'');
    
    //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' item<br><pre>'.print_r($this->item,true).'</pre>'),'');
    
    //echo 'ext_fields<br><pre>'.print_r($this->ext_fields, true).'</pre><br>';
    
    //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' REQUEST<br><pre>'.print_r($_REQUEST,true).'</pre>'),'Notice');

    
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
        JToolBarHelper::preferences($option);
        
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
		$document->setTitle($isNew ? JText::_('COM_SPORTSMANAGEMENT_PERSON_NEW') : JText::_('COM_SPORTSMANAGEMENT_PERSON_EDIT'));
		$document->addScript(JURI::root() . $this->script);
		$document->addScript(JURI::root() . "/administrator/components/com_sportsmanagement/views/sportsmanagement/submitbutton.js");
		JText::script('COM_HELLOWORLD_HELLOWORLD_ERROR_UNACCEPTABLE');
	}
}
