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
* 
* https://docs.joomla.org/Supporting_SEF_URLs_in_your_component
* 
*/

defined('_JEXEC') or die();

/**
 * sportsmanagementControllerjsmgcalendarImport
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class sportsmanagementControllerjsmgcalendarImport extends JControllerLegacy 
{

/**
	 * Class Constructor
	 *
	 * @param	array	$config		An optional associative array of configuration settings.
	 * @return	void
	 * @since	1.5
	 */
	function __construct($config = array())
	{
	   // Initialise variables.
		$app = JFactory::getApplication();
		parent::__construct($config);

	//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getTask<br><pre>'.print_r($this->getTask(),true).'</pre>'),'Notice');
	}
    
	/**
	 * sportsmanagementControllerjsmgcalendarImport::import()
	 * 
	 * @return void
	 */
	public function import() 
    {

$option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
        $model = $this->getModel('jsmgcalendarImport');
        $result = $model->import();
        
        if ( $result )
        {
            $msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ADD_GOOGLE_EVENT');
        }
        else
        {
            $msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_NO_GOOGLECALENDAR_ID');
        }
        
//$link = $result;
        $link = 'index.php?option=com_sportsmanagement&view=jsmgcalendars';
//$link = 'index.php?option=com_sportsmanagement&tmpl=component&view=match&layout=editlineup&id=8534039&team=551305&prefill=';
//http://www.fussballineuropa.de/administrator/index.php?option=com_sportsmanagement&tmpl=component&view=match&layout=editlineup&id=8534039&team=551305&prefill=

		$this->setRedirect($link,$msg);

    
    }    
    
    
    //public function login() 
//    {
//    $model = $this->getModel('jsmgcalendarImport');
//    //$model->login();
//    $model->import();
//        
//        $msg = JText::_( 'COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_IMPORT' );
//	$this->setRedirect( 'index.php?option=com_sportsmanagement&view=jsmgcalendarimport&layout=login', $msg );
//    }

    
    //public function save() 
//    {
//		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
//
//		$model = $this->getModel('jsmgcalendarImport');
//
//		if ($model->store()) {
//			$msg = JText::_('JLIB_APPLICATION_SAVE_SUCCESS');
//		} else {
//			$msg = JText::_('JLIB_APPLICATION_ERROR_SAVE_FAILED');
//		}
//
//		$link = 'index.php?option=com_sportsmanagement&view=jsmgcalendars';
//		$this->setRedirect($link, $msg);
//	}

	//public function cancel() 
//    {
//		$msg = JText::_( 'Operation cancelled' );
//		$this->setRedirect( 'index.php?option=com_sportsmanagement&view=jsmgcalendars', $msg );
//	}
    
}