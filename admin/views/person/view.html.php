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
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
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
	 * sportsmanagementViewPerson::init()
	 * 
	 * @return
	 */
	public function init ()
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $model = $this->getModel();
        $option = $jinput->getCmd('option');
        // Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        $starttime = microtime(); 
        
    // get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' item<br><pre>'.print_r($item,true).'</pre>'),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' form<br><pre>'.print_r($form,true).'</pre>'),'');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' Ausfuehrungszeit query<br><pre>'.print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()),true).'</pre>'),'Notice');
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
                
        // name für den titel setzen
        $this->item->name = $this->item->lastname.' - '.$this->item->firstname;
        
        $this->form->setValue('address_country', 'request', $this->item->address_country);
        $this->form->setValue('zipcode', 'request', $this->item->zipcode);
        $this->form->setValue('location', 'request', $this->item->location);
        $this->form->setValue('address', 'request', $this->item->address);
        $this->form->setValue('state', 'request', $this->item->state);
        
        
        $this->form->setValue('sports_type_id', 'request', $this->item->sports_type_id);
        $this->form->setValue('position_id', 'request', $this->item->position_id);
        $this->form->setValue('agegroup_id', 'request', $this->item->agegroup_id);
        
        $this->form->setValue('person_art', 'request', $this->item->person_art);
        $this->form->setValue('person_id1', 'request', $this->item->person_id1);
        $this->form->setValue('person_id2', 'request', $this->item->person_id2);
        
        if ( $this->item->latitude == 255 )
        {
            $app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_NO_GEOCODE'),'Error');
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
            $this->form->setValue('birthday', null, '0000-00-00');
            $this->form->setValue('deathday', null, '0000-00-00');
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
            //$app->enqueueMessage(JText::_('view -> '.'<pre>'.print_r($lists['ext_fields'],true).'</pre>' ),'');
        }
        
    $this->assignRef('lists',$lists);
    
    
    $person_age = sportsmanagementHelper::getAge($this->form->getValue('birthday'),$this->form->getValue('deathday'));
//    $app->enqueueMessage(JText::_('personagegroup person_age<br><pre>'.print_r($person_age,true).'</pre>'   ),'');
    $person_range = $model->getAgeGroupID($person_age);
//    $app->enqueueMessage(JText::_('personagegroup person_range<br><pre>'.print_r($person_range,true).'</pre>'   ),'');
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
    
    // Load the language files for the contact integration
		$jlang = JFactory::getLanguage();
		$jlang->load('com_contact', JPATH_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('com_contact', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load('com_contact', JPATH_ADMINISTRATOR, null, true);
        
        $document->addScript('http://maps.google.com/maps/api/js?&sensor=false&language=de');
        $document->addScript(JURI::root(true).'/administrator/components/com_sportsmanagement/assets/js/gmap3.min.js');
    
	}
 
	
	/**
	 * sportsmanagementViewPerson::addToolBar()
	 * 
	 * @return void
	 */
	protected function addToolBar() 
	{
  	        
		JRequest::setVar('hidemainmenu', true);
	
        $isNew = $this->item->id ? $this->title = JText::_('COM_SPORTSMANAGEMENT_PERSON_EDIT') : $this->title = JText::_('COM_SPORTSMANAGEMENT_PERSON_NEW');
        $this->icon = 'person';
    
        parent::addToolbar();
        
	}
    
	
}
