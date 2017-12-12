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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');



// Include dependancy of the main controllerform class
jimport('joomla.application.component.controllerform');



/**
 * sportsmanagementControllerEditMatch
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class sportsmanagementControllerEditMatch extends JControllerForm
{


	/**
	 * sportsmanagementControllerEditMatch::__construct()
	 * 
	 * @param mixed $config
	 * @return void
	 */
	function __construct($config = array())
	{
		parent::__construct($config);

		// Map the apply task to the save method.
		$this->registerTask('apply', 'save');
	}


/**
 * sportsmanagementControllerEditMatch::getModel()
 * 
 * @param string $name
 * @param string $prefix
 * @param mixed $config
 * @return
 */
public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
        {
                return parent::getModel($name, $prefix, array('ignore_request' => false));
        }
        
  
	
	/**
	 * sportsmanagementControllerEditMatch::saveshort()
	 * 
	 * @return void
	 */
	function saveshort()
	{
	   $app = JFactory::getApplication();
       $date = JFactory::getDate();
	   $user = JFactory::getUser();
       $post = JFactory::getApplication()->input->post->getArray(array());
       $option = JFactory::getApplication()->input->getCmd('option');
       /* Ein Datenbankobjekt beziehen */
       $db = JFactory::getDbo();
       // Set the values
	   $data['modified'] = $date->toSql();
	   $data['modified_by'] = $user->get('id');
       $data['id'] = $post['matchid'];
       
       $data['cancel'] = $post['cancel'];
       $data['cancel_reason'] = $post['cancel_reason'];
       $data['playground_id'] = $post['playground_id'];
       $data['overtime'] = $post['overtime'];
       $data['count_result'] = $post['count_result'];
       $data['alt_decision'] = $post['alt_decision'];
       $data['team_won'] = $post['team_won'];
       $data['preview'] = $post['preview'];
       $data['team1_bonus'] = $post['team1_bonus'];
       $data['team2_bonus'] = $post['team2_bonus'];
       $data['match_result_detail'] = $post['match_result_detail'];
       
       $data['show_report'] = $post['show_report'];
       
       $data['summary'] = $post['summary'];
       $data['old_match_id'] = $post['old_match_id'];
       $data['new_match_id'] = $post['new_match_id'];
       
       if (isset($post['extended']) && is_array($post['extended'])) 
		{
			// Convert the extended field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string)$parameter;
		}
        
        $data['team1_result_decision'] = $post['team1_result_decision'];
        $data['team2_result_decision'] = $post['team2_result_decision'];
        $data['decision_info'] = $post['decision_info'];
        

//       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'Notice');
//       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'Notice');
       
       // Create an object for the record we are going to update.
       $object = new stdClass();
       
       foreach( $data as $key => $value )
       {
       //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' $key --> '.$key.''),'Notice');
       $object->$key = $value; 
       }
       
       //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' object<br><pre>'.print_r($object,true).'</pre>'),'Notice');
       // Update their details in the table using id as the primary key.
        $result_update = JFactory::getDbo()->updateObject('#__sportsmanagement_match', $object, 'id', true);
       
       $routeparameter = array();
$routeparameter['cfg_which_database'] = $post['cfg_which_database'];
$routeparameter['s'] = $post['s'];
$routeparameter['p'] = $post['p'];
$routeparameter['r'] = $post['r'];
$routeparameter['division'] = $post['division'];
$routeparameter['mode'] = $post['mode'];
$routeparameter['order'] = $post['order'];
$routeparameter['layout'] = $post['oldlayout'];
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('results',$routeparameter);
$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED');

       //$url = sportsmanagementHelperRoute::getEditMatchRoute($post['p'],$post['matchid']);
       $this->setRedirect($link,$msg);
       
	}

	
}
?>