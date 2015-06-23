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
 * SportsManagement Controller
 */
class sportsmanagementControllerclub extends JControllerForm
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
		parent::__construct($config);

		// Map the apply task to the save method.
		$this->registerTask('apply', 'save');
	}
    
    /**
     * sportsmanagementControllerclub::save()
     * 
     * @return
     */
    function save()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection();
        $id	= JRequest::getInt('id');
        $tmpl = JRequest::getVar('tmpl');
		$model = $this->getModel('club');
        $data = JRequest::getVar('jform', array(), 'post', 'array');
        $createTeam = JRequest::getVar('createTeam');
        $return = $model->save($data);
        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' insertid<br><pre>'.print_r($db->insertid(),true).'</pre>'),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' createTeam<br><pre>'.print_r($createTeam,true).'</pre>'),'');
        
        if ( empty($id) )
        {
            $id = $db->insertid();
        }
        
        if ($createTeam)
		{
			 $mdlTeam = JModelLegacy::getInstance("team", "sportsmanagementModel");
             $team_name = $data['name'];
		     $team_short_name = strtoupper(substr(ereg_replace("[^a-zA-Z]","",$team_name),0,3));
			
				$tpost['id']= "0";
				$tpost['name']= $team_name;
				$tpost['short_name']= $team_short_name ;
				$tpost['club_id']= $id;
				$mdlTeam->save($tpost);
        }
             
        // Set the redirect based on the task.
		switch ($this->getTask())
		{
			case 'apply':
				$message = JText::_('JLIB_APPLICATION_SAVE_SUCCESS');
                $message = '';
                if ( $tmpl )
                {
				$this->setRedirect('index.php?option=com_sportsmanagement&view=club&layout=edit&tmpl=component&id='.$id, $message);
                }
                else
                {
                $this->setRedirect('index.php?option=com_sportsmanagement&view=club&layout=edit&id='.$id, $message);    
                }
				break;

			case 'save':
			default:
            if ( $tmpl )
                {
				$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
                }
                else
                {
                $this->setRedirect('index.php?option=com_sportsmanagement&view=clubs');    
                }
				break;
		}

		return true;
        
   }     

}
