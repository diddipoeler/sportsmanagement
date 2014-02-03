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
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');
 

/**
 * sportsmanagementControllertemplate
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllertemplate extends JControllerForm
{

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

}
