<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');



/**
 * sportsmanagementControllerjlextindividualsport
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementControllerjlextindividualsport extends JController
{
    
   
     
    
//    function apply($data)
//    {
//        $app = JFactory::getApplication();
//        $option = JFactory::getApplication()->input->getCmd('option');
//        $model = $this->getModel('jlextindividualsport');
//        $model->apply($data);
//    
//        
//    }
    
//    function save($data)
//    {
//        $app = JFactory::getApplication();
//        $option = JFactory::getApplication()->input->getCmd('option');
//        $model = $this->getModel('jlextindividualsport');
//        $model->save($data);
//    
//        
//    }
    


	/**
	 * sportsmanagementControllerjlextindividualsport::addmatch()
	 * 
	 * @return
	 */
	function addmatch()
	{
		$option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
        $db = JFactory::getDbo(); 
        
        //option=com_sportsmanagement&view=jlextindividualsportes&tmpl=component&id=241&team1=23&team2=31&rid=31
		$post = JFactory::getApplication()->input->post->getArray(array());
		$post['project_id'] = $app->getUserState( "$option.pid", '0' );
		$post['round_id'] = $app->getUserState( "$option.rid", '0' );
        //$post['match_id'] = $post['id'];
        
		$model = $this->getModel('jlextindividualsport');
        //$model->addmatch();
        
        $row = $model->getTable();
        // bind the form fields to the table
        if (!$row->bind($post)) 
        {
        $this->setError($this->_db->getErrorMsg());
        return false;
        }
         // make sure the record is valid
        if (!$row->check()) 
        {
        $this->setError($this->_db->getErrorMsg());
        return false;
        }
        // store to the database
		if ($row->store($post))
		{
			//$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ADD_MATCH'.$db->insertid());
            $msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ADD_MATCH'.$db->getErrorMsg());
		}
		else
		{
			$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_ADD_MATCH').$model->getError();
		}
		
        $link = 'index.php?option=com_sportsmanagement&view=jlextindividualsportes&tmpl=component&rid='.$post['round_id'].'&id='.$post['match_id'].'&team1='.$post['projectteam1_id'].'&team2='.$post['projectteam2_id'].'';
		$this->setRedirect($link,$msg);
        
        
        
        /*
        $option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
		$post=JFactory::getApplication()->input->post->getArray(array());
		
		$post['match_id']		= $app->getUserState( $option . 'match_id',0 );
		$post['project_id']=$app->getUserState($option.'project',0);
		$post['round_id']=$app->getUserState($option.'round_id',0);
		$model=$this->getModel('jlextindividualsport');
		if ($model->store($post))
		{
			$msg=JText::_('JL_ADMIN_MATCH_CTRL_ADD_SINGLE_MATCH');
		}
		else
		{
			$msg=JText::_('JL_ADMIN_MATCH_CTRL_ERROR_ADD_SINGLE_MATCH').$model->getError();
		}
		$link='index.php?option=com_sportsmanagement&view=jlextindividualsportes';
		$this->setRedirect($link,$msg);
        */
        
	}
    

}
?>