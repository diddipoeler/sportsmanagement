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
//jimport('joomla.application.component.modeladmin');
 

/**
 * sportsmanagementModeltemplate
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModeltemplate extends JSMModelAdmin
{
    
    /**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function save($data)
	{
	   $app = JFactory::getApplication();
       $input = $app->input;
       $date = JFactory::getDate();
	   $user = JFactory::getUser();
       $post = JRequest::get('post');
       $option = JRequest::getCmd('option');
       // Set the values
	   $data['modified'] = $date->toSql();
	   $data['modified_by'] = $user->get('id');
       
//       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' task<br><pre>'.print_r($input->get('task'),true).'</pre>'),'Notice');
//       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'Notice');
//       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'Notice');
       
       if (isset($post['params']['colors_ranking']) && is_array($post['params']['colors_ranking'])) 
		{
		  $colors = array();
          foreach ( $post['params']['colors_ranking'] as $key => $value )
          {
            
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' value<br><pre>'.print_r($value,true).'</pre>'),'Notice');
            
            if ( !empty($value['von']) )
            {
                $colors[] = implode(",",$value);
                //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' value<br><pre>'.print_r($value,true).'</pre>'),'Notice');
            }
          }
          
          //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' colors<br><pre>'.print_r($colors,true).'</pre>'),'Notice');
        
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
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        
        // for save as copy
		if ( $input->get('task') == 'save2copy' )
		{
		//$origTable = clone $this->getTable();
        $origTable = $this->getTable();
		//$origTable->load($input->getInt('id'));
        $origTable->load( (int) $data['id'] );
        $data['project_id'] = $app->getUserState( "$option.pid", '0' );
        $data['title'] = $post['title'];      
        $data['template'] = $post['template'];
        }  
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        
        // zuerst sichern, damit wir bei einer neuanlage die id haben
       if ( parent::save($data) )
       {
			$id =  (int) $this->getState($this->getName().'.id');
            $isNew = $this->getState($this->getName() . '.new');
            $data['id'] = $id;
            
            if ( $isNew )
            {
                //Here you can do other tasks with your newly saved record...
                $app->enqueueMessage(JText::plural(strtoupper($option) . '_N_ITEMS_CREATED', $id),'');
            }
           
		}
        
        return true;   
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
		
        $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.
		$db	= $this->getDbo();
		$query1	= $db->getQuery(true);
        $query2	= $db->getQuery(true);
        $query3	= $db->getQuery(true);
        $result1 = array();
        $result2 = array();
                
        // Select some fields
		$query1->select('template');
        // From table
		$query1->from('#__sportsmanagement_template_config');
        $query1->where('project_id = '.$project_id);
        $db->setQuery($query1);

        if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Joomla! 3.0 code here
		$current = $db->loadColumn();
}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{
// Joomla! 2.5 code here
		$current = $db->loadResultArray();
}

//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' current<br><pre>'.print_r($current,true).'</pre>'),'Notice');
        if ( $current )
        {
        $current = implode("','",$current);
        }
        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' current<br><pre>'.print_r($current,true).'</pre>'),'Notice');
        
        
        // Select some fields
		$query2->select('id as value, title as text');
        // From table
		$query2->from('#__sportsmanagement_template_config');
        $query2->where('project_id = '.$master_id);
        if ( $current )
        {
        $query2->where('template NOT IN (\''.$current.'\') ');    
        }
        $db->setQuery($query2);
        $result1 = $db->loadObjectList();
        
        foreach ($result1 as $template)
        {
			$template->text = JText::_($template->text);
		}
        
        
        // Select some fields
		$query3->select('id as value, title as text');
        // From table
		$query3->from('#__sportsmanagement_template_config');
        $query3->where('project_id = '.$project_id);
        $query3->order('title');
        $db->setQuery($query3);
		$result2 = $db->loadObjectList();
        
        foreach ($result2 as $template)
        {
			$template->text = JText::_($template->text);
		}
        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result1<br><pre>'.print_r($result1,true).'</pre>'),'Notice');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result2<br><pre>'.print_r($result2,true).'</pre>'),'Notice');
        
        if ( $result1 )
        {
        return array_merge($result2,$result1);    
        }
        else
        {
        return ($result2);    
        }
		
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
$app = JFactory::getApplication();
    //$app->enqueueMessage(JText::_('delete pks<br><pre>'.print_r($pks,true).'</pre>'),'');
    
    return parent::delete($pks);
    
         
   } 



    
}
