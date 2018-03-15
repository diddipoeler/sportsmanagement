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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');
 

/**
 * sportsmanagementControllerjlextindividualsportes
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerjlextindividualsportes extends JControllerAdmin
{
  
   /**
	 * Method to update checked matches
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
    function saveshort()
	{
	   $option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
        $post = JFactory::getApplication()->input->post->getArray(array());
		$post['project_id'] = $app->getUserState( "$option.pid", '0' );
		$post['round_id'] = $app->getUserState( "$option.rid", '0' );
        
	   $model = $this->getModel();
       $model->saveshort();
       
//       $link = 'index.php?option=com_sportsmanagement&view=jlextindividualsportes&tmpl=component&rid='.$post['round_id'].'&id='.$post['match_id'].'&team1='.$post['projectteam1_id'].'&team2='.$post['projectteam2_id'].'';
//		$this->setRedirect($link,$msg);
        
        $msg = '';
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component',$msg);
    }
    
    /**
	 * Method to update checked matches
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
    function applyshort()
	{
	   $option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
        $post = JFactory::getApplication()->input->post->getArray(array());
		$post['project_id'] = $app->getUserState( "$option.pid", '0' );
		$post['round_id'] = $app->getUserState( "$option.rid", '0' );
        
       $model = $this->getModel();
       $model->saveshort();
       
       
       $link = 'index.php?option=com_sportsmanagement&view=jlextindividualsportes&tmpl=component&rid='.$post['round_id'].'&id='.$post['match_id'].'&team1='.$post['projectteam1_id'].'&team2='.$post['projectteam2_id'].'';
		$this->setRedirect($link,$msg);
        
    } 
    
    /**
     * sportsmanagementControllerjlextindividualsportes::publish()
     * 
     * @return void
     */
    function publish() 
    {
           $option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
        $pks = JFactory::getApplication()->input->getVar('cid', array(), 'post', 'array');
        $post = JFactory::getApplication()->input->post->getArray(array());
		$post['project_id'] = $app->getUserState( "$option.pid", '0' );
		$post['round_id'] = $app->getUserState( "$option.rid", '0' );
        
       
       parent::publish();
       $msg = JText::sprintf('COM_SPORTSMANAGEMENT_N_ITEMS_PUBLISHED',count($pks));;
       $link = 'index.php?option=com_sportsmanagement&view=jlextindividualsportes&tmpl=component&rid='.$post['round_id'].'&id='.$post['match_id'].'&team1='.$post['projectteam1_id'].'&team2='.$post['projectteam2_id'].'';
		$this->setRedirect($link,$msg);
        
    }
    
    /**
     * sportsmanagementControllerjlextindividualsportes::delete()
     * 
     * @return void
     */
    function delete()
	{
	   $option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
        $pks = JFactory::getApplication()->input->getVar('cid', array(), 'post', 'array');
        $post = JFactory::getApplication()->input->post->getArray(array());
		$post['project_id'] = $app->getUserState( "$option.pid", '0' );
		$post['round_id'] = $app->getUserState( "$option.rid", '0' );
        
       $model = $this->getModel();
       $model->delete($pks);
       
       $msg = JText::sprintf('COM_SPORTSMANAGEMENT_N_ITEMS_DELETED',count($pks));
       $link = 'index.php?option=com_sportsmanagement&view=jlextindividualsportes&tmpl=component&rid='.$post['round_id'].'&id='.$post['match_id'].'&team1='.$post['projectteam1_id'].'&team2='.$post['projectteam2_id'].'';
		$this->setRedirect($link,$msg);
        
    } 
    
    
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'jlextindividualsport', $prefix = 'sportsmanagementModel') 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}