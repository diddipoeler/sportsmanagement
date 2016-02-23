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
 * sportsmanagementControllerseasons
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
/**
 * sportsmanagementControllerseasons
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerseasons extends JControllerAdmin
{
	/**
	 * sportsmanagementControllerseasons::applypersons()
	 * 
	 * @return void
	 */
	function applypersons()
    {
        $option = JRequest::getCmd('option');
		$app = JFactory::getApplication();
        $post = JRequest::get('post');
        $model = $this->getModel();
       $model->saveshortpersons();
       
       $msg = '';
        $this->setRedirect('index.php?option=com_sportsmanagement&tmpl=component&view=seasons&layout=assignpersons&id='.$post['season_id'],$msg);
        
    }
    
    /**
     * sportsmanagementControllerseasons::savepersons()
     * 
     * @return void
     */
    function savepersons()
    {
        $option = JRequest::getCmd('option');
		$app = JFactory::getApplication();
        $post = JRequest::get('post');
        $model = $this->getModel();
       $model->saveshortpersons();
        
        $msg = '';
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component',$msg);
        
    }
    
    /**
     * sportsmanagementControllerseasons::applyteams()
     * 
     * @return void
     */
    function applyteams()
    {
        $option = JRequest::getCmd('option');
		$app = JFactory::getApplication();
        $post = JRequest::get('post');
        $model = $this->getModel();
       $model->saveshortteams();
       
       $msg = '';
        $this->setRedirect('index.php?option=com_sportsmanagement&tmpl=component&view=seasons&layout=assignteams&id='.$post['season_id'],$msg);
        
    }
    
    /**
     * sportsmanagementControllerseasons::saveteams()
     * 
     * @return void
     */
    function saveteams()
    {
        $option = JRequest::getCmd('option');
		$app = JFactory::getApplication();
        $post = JRequest::get('post');
        $model = $this->getModel();
       $model->saveshortteams();
        
        $msg = '';
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component',$msg);
        
    }
  
  
  
  
  
  
//  /**
//	 * Save the manual order inputs from the categories list page.
//	 *
//	 * @return	void
//	 * @since	1.6
//	 */
//	public function saveorder()
//	{
//		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
//
//		// Get the arrays from the Request
//		$order	= JRequest::getVar('order',	null, 'post', 'array');
//		$originalOrder = explode(',', JRequest::getString('original_order_values'));
//
//		// Make sure something has changed
//		if (!($order === $originalOrder)) {
//			parent::saveorder();
//		} else {
//			// Nothing to reorder
//			$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
//			return true;
//		}
//	}
  
  
  /**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'Season', $prefix = 'sportsmanagementModel', $config = Array() ) 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	


	
}