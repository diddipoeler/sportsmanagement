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
 

/**
 * sportsmanagementViewClub
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewClub extends sportsmanagementView
{
	
	
	/**
	 * sportsmanagementViewClub::init()
	 * 
	 * @return
	 */
	public function init ()
	{
		$option = JRequest::getCmd('option');
		$app = JFactory::getApplication();
        $document = JFactory::getDocument();
		$uri = JFactory::getURI();
        $model = $this->getModel();
        $starttime = microtime(); 
        
        $this->option = $option;
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r(JRequest::getVar('tmpl'),true).'</pre>'),'Notice');
        $this->assign( 'tmpl', JRequest::getVar('tmpl') );
        
        // get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' item<br><pre>'.print_r($item,true).'</pre>'),'');
        
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
        
        if ( $item->latitude != 255 )
        {
            $this->googlemap = true;
        }
        else
        {
            $this->googlemap = false;
        }
		// Assign the Data
		$this->form = $form;
		$this->item = $item;
		$this->script = $script;
        
        $this->form->setValue('country', 'request', $this->item->country);
        $this->form->setValue('zipcode', 'request', $this->item->zipcode);
        $this->form->setValue('location', 'request', $this->item->location);
        $this->form->setValue('address', 'request', $this->item->address);
        
        if ( $this->item->id )
        {
            // alles ok
        }
        else
        {
//            $this->item->founded = '';
//            $this->item->dissolved = '';
            $this->form->setValue('founded', null, '0000-00-00');
            $this->form->setValue('dissolved', null, '0000-00-00');
        }
        
        if ( $this->item->latitude == 255 )
        {
            $app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_NO_GEOCODE'),'Error');
            $this->map = false;
        }
        else
        {
            $this->map = true;
        }
		
		$extended = sportsmanagementHelper::getExtended($item->extended, 'club');
		$this->assignRef( 'extended', $extended );
        $extendeduser = sportsmanagementHelper::getExtendedUser($this->item->extendeduser, 'club');		
		$this->assignRef( 'extendeduser', $extendeduser );
		//$this->assign('cfg_which_media_tool', JComponentHelper::getParams($option)->get('cfg_which_media_tool',0) );
        
        $this->assign( 'checkextrafields', sportsmanagementHelper::checkUserExtraFields() );
        if ( $this->checkextrafields )
        {
            $lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields($item->id);
            //$app->enqueueMessage(JText::_('sportsmanagementViewClub ext_fields'.'<pre>'.print_r($lists['ext_fields'],true).'</pre>' ),'');
        }
        
        // die mannschaften zum verein
        if ( $this->item->id )
        {
            $teamsofclub = $model->teamsofclub($this->item->id);
            $this->assignRef( 'teamsofclub', $teamsofclub );
        }
        
        $this->assignRef( 'lists', $lists );
        
        $document->addScript('http://maps.google.com/maps/api/js?&sensor=false&language=de');
        $document->addScript(JURI::root(true).'/administrator/components/com_sportsmanagement/assets/js/gmap3.min.js');


	}
 
	
	/**
	 * sportsmanagementViewClub::addToolBar()
	 * 
	 * @return void
	 */
	protected function addToolBar() 
	{
  		        
		JRequest::setVar('hidemainmenu', true);
		$isNew = $this->item->id ? $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_EDIT') : $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_ADD_NEW');
        $this->icon = 'club';
       
        parent::addToolbar();
	}
    
	
}
