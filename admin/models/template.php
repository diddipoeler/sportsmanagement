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
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 

/**
 * sportsmanagementModeltemplate
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModeltemplate extends JModelAdmin
{
	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param	array	$data	An array of input data.
	 * @param	string	$key	The name of the key for the primary key.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Check specific edit permission then general edit permission.
		return JFactory::getUser()->authorise('core.edit', 'com_sportsmanagement.message.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
	}
    
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'template', $prefix = 'sportsmanagementTable', $config = array()) 
	{
		return JTable::getInstance($type, $prefix, $config);
	}
    
	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_sportsmanagement.template', 'template', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
		return $form;
	}
    
	/**
	 * Method to get the script that have to be included on the form
	 *
	 * @return string	Script files
	 */
	public function getScript() 
	{
		return 'administrator/components/com_sportsmanagement/models/forms/sportsmanagement.js';
	}
    
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.template.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
		}
		return $data;
	}
	
	/**
	 * Method to save item order
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function saveorder($pks = NULL, $order = NULL)
	{
		$row =& $this->getTable();
		
		// update ordering values
		for ($i=0; $i < count($pks); $i++)
		{
			$row->load((int) $pks[$i]);
			if ($row->ordering != $order[$i])
			{
				$row->ordering=$order[$i];
				if (!$row->store())
				{
					sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
					return false;
				}
			}
		}
		return true;
	}
    
    /**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function save($data)
	{
	   $mainframe = JFactory::getApplication();
       $post=JRequest::get('post');
       
//       $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'Notice');
//       $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'Notice');
       
       if (isset($post['params']['colors_ranking']) && is_array($post['params']['colors_ranking'])) 
		{
		  $colors = array();
          foreach ( $post['params']['colors_ranking'] as $key => $value )
          {
            
//            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' value<br><pre>'.print_r($value,true).'</pre>'),'Notice');
            
            if ( !empty($value['von']) )
            {
                $colors[] = implode(",",$value);
                //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' value<br><pre>'.print_r($value,true).'</pre>'),'Notice');
            }
          }
          
          //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' colors<br><pre>'.print_r($colors,true).'</pre>'),'Notice');
        
        $post['params']['colors'] = implode(";",$colors); 
        }  
        
       if (isset($post['params']) && is_array($post['params'])) 
		{
			// Convert the params field to a string.
			//$parameter = new JRegistry;
			//$parameter->loadArray($post['params']);
            $paramsString = json_encode( $post['params'] );
			//$data['params'] = (string)$parameter;
            $data['params'] = $paramsString;
		}
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModeltemplate save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        
        // Proceed with the save
		return parent::save($data);   
    }
    
    /**
     * sportsmanagementModeltemplate::getAllTemplatesList()
     * 
     * @param mixed $project_id
     * @param mixed $master_id
     * @return
     */
    function getAllTemplatesList($project_id,$master_id)
	{
		
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.
		$db		= $this->getDbo();
		$query1	= $db->getQuery(true);
        $query2	= $db->getQuery(true);
        $query3	= $db->getQuery(true);
        
        
        // Select some fields
		$query1->select('template');
        // From table
		$query1->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_template_config');
        $query1->where('project_id='.$project_id);
        $db->setQuery($query1);
		$current = $db->loadColumn();
        $current = implode("','",$current);
        // Select some fields
		$query2->select('id as value, title as text');
        // From table
		$query2->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_template_config');
        $query2->where('project_id='.$master_id.' and template NOT IN (\''.$current.'\') ');
        $db->setQuery($query2);
        $result1 = $db->loadObjectList();
        // Select some fields
		$query3->select('id as value, title as text');
        // From table
		$query3->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_template_config');
        $query3->where('project_id='.$project_id);
        $query3->order('title');
        $db->setQuery($query3);
		$result2 = $db->loadObjectList();
        
		return array_merge($result2,$result1);
	}
    
    
	/**
	 * sportsmanagementModeltemplate::import()
	 * 
	 * @param mixed $templateid
	 * @param mixed $projectid
	 * @return
	 */
	function import($templateid,$projectid)
{
$row =& $this->getTable();

// load record to copy
if (!$row->load($templateid))
{
$this->setError($this->_db->getErrorMsg());
return false;
}

//copy to new element
$row->id=null;
$row->project_id=(int) $projectid;

// Make sure the item is valid
if (!$row->check())
{
$this->setError($this->_db->getErrorMsg());
return false;
}

// Store the item to the database
if (!$row->store())
{
$this->setError($this->_db->getErrorMsg());
return false;
}
return true;
}    


	/**
	 * sportsmanagementModeltemplate::delete()
	 * 
	 * @param mixed $pks
	 * @return
	 */
	public function delete(&$pks)
{
$mainframe = JFactory::getApplication();
    //$mainframe->enqueueMessage(JText::_('delete pks<br><pre>'.print_r($pks,true).'</pre>'),'');
    
    return parent::delete($pks);
    
         
   } 



    
}
