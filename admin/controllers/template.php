<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
//jimport('joomla.application.component.controllerform');
 

/**
 * sportsmanagementControllertemplate
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllertemplate extends JSMControllerForm
{

/**
 * sportsmanagementControllertemplate::__construct()
 * 
 * @return void
 */
function __construct()
	{
		$app	= JFactory::getApplication();
		$option = JRequest::getCmd('option');
		parent::__construct();

	
		// Register Extra tasks
		$this->registerTask('reset','remove');
	}
    
/**
 * sportsmanagementControllertemplate::remove()
 * 
 * @return
 */
function remove()
	{
		$cid = JRequest::getVar('cid',array(0),'post','array');
		JArrayHelper::toInteger($cid);
		$isMaster = JRequest::getVar('isMaster',array(),'post','array');
		JArrayHelper::toInteger($isMaster);
		if (count($cid) < 1){
			JError::raiseError(500,JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TO_DELETE'));
		}
		foreach ($cid AS $id)
		{
			if ($isMaster[$id])
			{
				echo "<script> alert('" . JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_CTRL_DELETE_WARNING') . "'); window.history.go(-1); </script>\n";
				return;
			}
		}
		$model = $this->getModel('template');
		if (!$model->delete($cid))
		{
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}
		$msg = JText::_("COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_RESET_SUCCESS");
		$this->setRedirect('index.php?option=com_sportsmanagement&view=templates&pid='.JRequest::getInt( "pid", 0 ), $msg);
	}
	
	/**
	 * sportsmanagementControllertemplate::masterimport()
	 * 
	 * @return void
	 */
	function masterimport()
{
$templateid = JRequest::getVar('templateid',0,'post','int');
$projectid = JRequest::getVar('pid',0,'post','int');
$model = $this->getModel('template');
if ( $templateid )
{
if ($model->import($templateid,$projectid))
{
$msg=JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_CTRL_IMPORTED_TEMPLATE');
}
else
{
$msg=JText::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_CTRL_ERROR_IMPORT_TEMPLATE').$model->getError();
}

}
$this->setRedirect('index.php?option=com_sportsmanagement&view=templates&pid='.$projectid,$msg);
}	

}
