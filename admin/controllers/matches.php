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
 * sportsmanagementControllermatches
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllermatches extends JControllerAdmin
{
  
    
    
    /**
     * sportsmanagementControllermatches::__construct()
     * 
     * @return void
     */
    function __construct()
	{
		parent::__construct();

	}

    /**
	 * Returns a reference to the global {@link JoomlaTuneAjaxResponse} object,
	 * only creating it if it doesn't already exist.
	 *
	 * @return JoomlaTuneAjaxResponse
	 */
	public static function getAjaxResponse()
	{
		static $instance = null;

		if (!is_object($instance)) {
			$instance = new JoomlaTuneAjaxResponse('utf-8');
		}

		return $instance;
	}
    
    
    /**
     * sportsmanagementControllermatches::removeEvent()
     * 
     * @return void
     */
    function removeEvent()
    {
        // Check for request forgeries
        //JRequest::checkToken('get') or jexit('JINVALID_TOKEN');
		//JSession::checkToken() or die('JINVALID_TOKEN');
        //JRequest::checkToken() or jexit('JINVALID_TOKEN');

		$event_id=JRequest::getInt('event_id');
		$model=$this->getModel();
		if (!$result=$model->deleteevent($event_id))
		{
			$result="0"."&".JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_EVENTS').': '.$model->getError();
		}
		else
		{
			$result="1"."&".JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_DELETE_EVENTS').'&'.$event_id;
		}
		echo json_encode($result);
		JFactory::getApplication()->close();
    }
    
    
    /**
     * sportsmanagementControllermatches::removeCommentary()
     * 
     * @return void
     */
    public function removeCommentary()
    {
        
        //$response = self::getAjaxResponse();
        //$result = $response;
        
        // Check for request forgeries
        //JRequest::checkToken('post') or jexit('JINVALID_TOKEN');
        //JRequest::checkToken('get') or jexit('JINVALID_TOKEN');
        
        //JRequest::checkToken('request') or jexit('JINVALID_TOKEN');
		//JSession::checkToken() or die('JINVALID_TOKEN');
        //JRequest::checkToken() or jexit('JINVALID_TOKEN');
        
        // Check for request forgeries
        //JRequest::checkToken( 'get' ) or jexit( 'JINVALID_TOKEN' );
        
 //       if (!JSession::checkToken('post')) 
//        {
			//$result='0'.'&'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_COMMENTARY').': '.JText::_('JINVALID_TOKEN');
		//echo json_encode($result);
        //}
//else
//{
		
        //if (JSession::checkToken('post')) 
        //{
        
        $event_id = JRequest::getInt('event_id');
		$model = $this->getModel();
		if (!$result = $model->deletecommentary($event_id))
		{
			$result='0'.'&'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_DELETE_COMMENTARY').': '.$model->getError();
		}
		else
		{
			$result='1'.'&'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_DELETE_COMMENTARY').'&'.$event_id;
		}
		echo json_encode($result);
 //}       
        // Close the application
		JFactory::getApplication()->close();
        //}
        
        //jexit();
		//JFactory::getApplication()->close();
    }
    
    
    /**
     * sportsmanagementControllermatches::removeSubst()
     * 
     * @return void
     */
    function removeSubst()
	{
		//JSession::checkToken() or die('JINVALID_TOKEN');
        //JRequest::checkToken() or jexit('JINVALID_TOKEN');
        //JRequest::checkToken('get') or jexit('JINVALID_TOKEN');
        
		$substid = JRequest::getInt('substid',0);
		$model = $this->getModel();
		if (!$result = $model->removeSubstitution($substid))
		{
			$result="0"."&".JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_REMOVE_SUBST').': '.$model->getError();
		}
		else
		{
			$result="1"."&".JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_REMOVE_SUBST').'&'.$substid;
		}
		echo json_encode($result);
		JFactory::getApplication()->close();
	}
    
    /**
     * sportsmanagementControllermatches::saveevent()
     * 
     * @return void
     */
    function saveevent()
    {
        $option = JRequest::getCmd('option');

		// Check for request forgeries
		//JSession::checkToken() or die('JINVALID_TOKEN');
        //JRequest::checkToken() or jexit('JINVALID_TOKEN');
        //JRequest::checkToken('get') or jexit('JINVALID_TOKEN');

		//$app = JFactory::getApplication();
		$data = array();
		$data['teamplayer_id']	= JRequest::getInt('teamplayer_id');
		$data['projectteam_id']	= JRequest::getInt('projectteam_id');
		$data['event_type_id']	= JRequest::getInt('event_type_id');
		$data['event_time']		= JRequest::getVar('event_time','');
		$data['match_id']		= JRequest::getInt('matchid');
		$data['event_sum']		= JRequest::getVar('event_sum', '');
		$data['notice']			= JRequest::getVar('notice', '');
		$data['notes']			= JRequest::getVar('notes', '');
        
        // diddipoeler
        $data['projecttime']			= JRequest::getVar('projecttime','');
        
        $model = $this->getModel();
		//$project_id = $app->getUserState( "$option.pid", '0' );;
		if (!$result = $model->saveevent($data)) {
			$result = "0"."&".JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_EVENT').': '.$model->getError();
        } else {
			$result = $model->getDbo()->insertid()."&".JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_EVENT');
		}    
 
		echo json_encode($result);
		JFactory::getApplication()->close();
    }
    
    /**
     * sportsmanagementControllermatches::savecomment()
     * 
     * @return void
     */
    function savecomment()
    {
        //$option = JRequest::getCmd('option');
        //JRequest::checkToken() or jexit('JINVALID_TOKEN');
        //JRequest::checkToken('get') or jexit('JINVALID_TOKEN');
        //$app = JFactory::getApplication();
        
        //$response = self::getAjaxResponse();
        //$result = $response;

		// Check for request forgeries
		//JSession::checkToken() or die('JINVALID_TOKEN');
        //JRequest::checkToken() or jexit('JINVALID_TOKEN');
        //JRequest::checkToken() or $result = '0&'.JText::_('JINVALID_TOKEN');

		
		$data = array();
		$data['event_time']		= JRequest::getVar('event_time','');
		$data['match_id']		= JRequest::getInt('matchid');
		$data['type']		= JRequest::getVar('type', '');
		$data['notes']			= JRequest::getVar('notes', '');
        
        // diddipoeler
        $data['projecttime']			= JRequest::getVar('projecttime','');
        
        $model = $this->getModel();
		//$project_id = $app->getUserState( "$option.pid", '0' );;
		if (!$result = $model->savecomment($data)) {
            $result = '0&'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_COMMENT').': '.$model->getError();
        } else {
            //$result = $model->getDbo()->insertid()."&".JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_COMMENT');
            $result = $result.'&'.JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_COMMENT');
		}    
 
		echo json_encode($result);
		JFactory::getApplication()->close();
        //$app->close();
    }
    
    /**
     * sportsmanagementControllermatches::savesubst()
     * 
     * @return void
     */
    function savesubst()
	{
		// Check for request forgeries
		//JSession::checkToken() or die('JINVALID_TOKEN');
        //JRequest::checkToken() or jexit('JINVALID_TOKEN');
        //JRequest::checkToken('get') or jexit('JINVALID_TOKEN');
        
		$data = array();
		$data['in'] 					= JRequest::getInt('in');
		$data['out'] 					= JRequest::getInt('out');
		$data['matchid'] 				= JRequest::getInt('matchid');
		$data['in_out_time'] 			= JRequest::getVar('in_out_time','');
		$data['project_position_id'] 	= JRequest::getInt('project_position_id');
        
        // diddipoeler
        $data['projecttime']			= JRequest::getVar('projecttime','');
		
        $model = $this->getModel();
		if (!$result = $model->savesubstitution($data)){
			$result = "0"."&".JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVED_SUBST').': '.$model->getError();
		} else {
            $result = $model->getDbo()->insertid()."&".JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_SUBST');
		}
        
		echo json_encode($result);
		JFactory::getApplication()->close();
	}
    
    
    function savestats()
    {
        $option = JRequest::getCmd('option');
        $post = JRequest::get('post');
        $model = $this->getModel();
        if ($model->savestats($post))
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_UPDATE_STATS');
		}
		else
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_UPDATE_STATS');
		}
        
        if ( JRequest::getString('close', 0) == 1 )
        {
            $link = 'index.php?option='.$option.'&view=close&tmpl=component';
        }
        else
        {
            $link = 'index.php?option='.$option.'&close='.JRequest::getString('close', 0).'&tmpl=component&view=match&layout=editstats&id='.$post['id'].'&team='.$post['team'];
        }
        
		//echo $link.'<br />';
		$this->setRedirect($link,$msg);
        
        
    }
        
    /**
     * sportsmanagementControllermatches::saveroster()
     * 
     * @return void
     */
    function saveroster()
    {
        $option = JRequest::getCmd('option');
        $post = JRequest::get('post');
        $model = $this->getModel();
        
        $positions = $model->getProjectPositionsOptions(0, 1,$post['project_id']);
        $staffpositions = $model->getProjectPositionsOptions(0, 2,$post['project_id']);
        $post['positions'] = $positions;
        $post['staffpositions'] = $staffpositions;
        
        if ($model->updateRoster($post))
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_PLAYER').'<br />';
            $msg .= JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_PLAYER_TRIKOT').'<br />';
		}
		else
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVE_MR_PLAYER').'<br />';
            $msg .= JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVE_MR_PLAYER_TRIKOT').'<br />';
		}
        
        if ($model->updateStaff($post))
		{
			$msg .= JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_STAFF').'<br />';
		}
		else
		{
			$msg .= JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVE_MR_STAFF').'<br />';
		}
        
//        if ($model->updateTrikotNumber($post))
//		{
//			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_PLAYER_TRIKOT').'<br />';
//		}
//		else
//		{
//			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_SAVE_MR_PLAYER_TRIKOT').'<br />';
//		}
        
        if ( JRequest::getString('close', 0) == 1 )
        {
            $link = 'index.php?option='.$option.'&view=close&tmpl=component';
        }
        else
        {
            $link = 'index.php?option='.$option.'&close='.JRequest::getString('close', 0).'&tmpl=component&view=match&layout=editlineup&id='.$post['id'].'&team='.$post['team'];
        }
        
		//echo $link.'<br />';
		$this->setRedirect($link,$msg);
    }    
    
    
    /**
     * sportsmanagementControllermatches::saveReferees()
     * 
     * @return void
     */
    function saveReferees()
    {
        $option = JRequest::getCmd('option');
        $post = JRequest::get('post');
        $model = $this->getModel();
        $positions = $model->getProjectPositionsOptions(0, 3,$post['project_id']);
        $post['positions'] = $positions;
        
        if ($model->updateReferees($post))
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_REFEREES');
		}
		else
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_REFEREES_ERROR').'<br />';
		}
        
        if ( JRequest::getString('close', 0) == 1 )
        {
            $link = 'index.php?option='.$option.'&view=close&tmpl=component';
        }
        else
        {
            $link = 'index.php?option='.$option.'&close='.JRequest::getString('close', 0).'&tmpl=component&view=match&layout=editreferees&id='.$post['id'];
        }
        
		//echo $link.'<br />';
		$this->setRedirect($link,$msg);
    }
    
    
    /**
	 * Method to update checked matches
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
    function saveshort()
	{
	   $model = $this->getModel();
       $model->saveshort();
       $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
    } 
    
    
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'match', $prefix = 'sportsmanagementModel', $config = Array() ) 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}