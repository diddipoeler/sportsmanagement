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
 
// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');
 


/**
 * sportsmanagementControllertreetos
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class sportsmanagementControllertreetos extends JControllerAdmin
{

/**
 * sportsmanagementControllertreetos::__construct()
 * 
 * @param mixed $config
 * @return void
 */
public function __construct($config = array())
	{
		parent::__construct($config);
        // Reference global application object
        $this->jsmapp = JFactory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;
        $this->jsmoption = $this->jsmjinput->getCmd('option');
	}
      
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'treeto', $prefix = 'sportsmanagementModel', $config = Array() ) 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
    
public function genNode()
	{
		//$app = JFactory::getApplication();
//		$jinput = $app->input;
//		$cid = $jinput->get('cid',array(),'array');
//		JArrayHelper::toInteger($cid);
		$id = $this->jsmjinput->get->get('id');
		
		$this->setRedirect('index.php?option=com_sportsmanagement&view=treeto&layout=gennode&id=' . $id);
	}
        
/**
 * sportsmanagementControllertreetos::save()
 * 
 * @return void
 */
public function save()
	{
		// Check for token
		JSession::checkToken() or jexit(JText::_('COM_SPORTSMANAGEMENT_GLOBAL_INVALID_TOKEN'));
		
		//$app = JFactory::getApplication();
		//$jinput = $app->input;
		$cid = $this->jsmjinput->get('cid',array(),'array');
		JArrayHelper::toInteger($cid);
		
		$post = $this->jsmjinput->post->getArray();
		$data['project_id'] = $post['project_id'];

//        $this->jsmapp->enqueueMessage(__METHOD__.' '.__LINE__.' post <pre>'.print_r($post, true).'</pre><br>',''); 
//        $this->jsmapp->enqueueMessage(__METHOD__.' '.__LINE__.' data <pre>'.print_r($data, true).'</pre><br>',''); 
		
        $model = $this->getModel('treeto');
        $row = $model->getTable();
        
        
		//$table = JTable::getInstance('Treeto','sportsmanagementTable');
		if($row->save($data))
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETO_CTRL_SAVED');
		}
		else
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETO_CTRL_ERROR_SAVED') . $model->getError();
		}
        
        
		// Check the table in so it can be edited.... we are done with it anyway
		// $model->checkin();
		
		$task = $this->getTask();
		
		if($task == 'save')
		{
			$link = 'index.php?option=com_sportsmanagement&view=treetos';
		}
		else
		{
			$link = 'index.php?option=com_sportsmanagement&task=treeto.edit&id=' . $post['id'];
		}
		$this->setRedirect($link,$msg);
	}    
    
    
    
    
    
}