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
 * sportsmanagementControllerprojectteams
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerprojectteams extends JControllerAdmin
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
	 * Method to assign persons or teams
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
  function assign()
	{
	   $app = JFactory::getApplication();
       // JInput object
        $jinput = $app->input;
		//$post = JFactory::getApplication()->input->post->getArray(array());
        $post = $jinput->post->getArray();
        $option = $jinput->getCmd('option');

        $model = $this->getModel();
       $msg = $model->storeAssign($post);
       $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component',$msg);
	}
  
  
  /**
   * sportsmanagementControllerprojectteams::matchgroups()
   * 
   * @return void
   */
  function matchgroups()
	{
	   $model = $this->getModel();
       $model->matchgroups();
       $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
    } 
    
    
    /**
     * sportsmanagementControllerprojectteams::setseasonid()
     * 
     * @return void
     */
    function setseasonid()
	{
	   $model = $this->getModel();
       $model->setseasonid();
       $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
    } 
    
    
    /**
     * sportsmanagementControllerprojectteams::use_table_yes()
     * 
     * @return void
     */
    function use_table_yes()
    {
       $model = $this->getModel();
       $model->setusetable(1);
       $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
    }
    
    /**
     * sportsmanagementControllerprojectteams::use_table_no()
     * 
     * @return void
     */
    function use_table_no()
    {
       $model = $this->getModel();
       $model->setusetable(0);
       $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
    }
    
    /**
     * sportsmanagementControllerprojectteams::use_table_points_yes()
     * 
     * @return void
     */
    function use_table_points_yes()
    {
       $model = $this->getModel();
       $model->setusetablepoints(1);
       $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
    }
    
    /**
     * sportsmanagementControllerprojectteams::use_table_points_no()
     * 
     * @return void
     */
    function use_table_points_no()
    {
       $model = $this->getModel();
       $model->setusetablepoints(0);
       $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
    }
    
  /**
	 * Method to update checked projectteams
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
	public function getModel($name = 'Projectteam', $prefix = 'sportsmanagementModel', $config = Array() ) 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	


	
}