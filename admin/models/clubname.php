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
 
// import Joomla modelform library
//jimport('joomla.application.component.modeladmin');
 


/**
 * sportsmanagementModelclubname
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class sportsmanagementModelclubname extends JSMModelAdmin
{
//   /**
//	 * Method override to check if you can edit an existing record.
//	 *
//	 * @param	array	$data	An array of input data.
//	 * @param	string	$key	The name of the key for the primary key.
//	 *
//	 * @return	boolean
//	 * @since	1.6
//	 */
//	protected function allowEdit($data = array(), $key = 'id')
//	{
//		// Check specific edit permission then general edit permission.
//		return JFactory::getUser()->authorise('core.edit', 'com_sportsmanagement.message.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
//	}
    
//	/**
//	 * Returns a reference to the a Table object, always creating it.
//	 *
//	 * @param	type	The table type to instantiate
//	 * @param	string	A prefix for the table class name. Optional.
//	 * @param	array	Configuration array for model. Optional.
//	 * @return	JTable	A database object
//	 * @since	1.6
//	 */
//	public function getTable($type = 'clubname', $prefix = 'sportsmanagementTable', $config = array()) 
//	{
//	$config['dbo'] = sportsmanagementHelper::getDBConnection(); 
//		return JTable::getInstance($type, $prefix, $config);
//	}
    
//    	/**
//	 * Method to get the script that have to be included on the form
//	 *
//	 * @return string	Script files
//	 */
//	public function getScript() 
//	{
//		return 'administrator/components/com_sportsmanagement/models/forms/sportsmanagement.js';
//	}
    
//	/**
//	 * Method to get the data that should be injected in the form.
//	 *
//	 * @return	mixed	The data for the form.
//	 * @since	1.6
//	 */
//	protected function loadFormData() 
//	{
//		// Check the session for previously entered form data.
//		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.clubname.data', array());
//		if (empty($data)) 
//		{
//			$data = $this->getItem();
//		}
//		return $data;
//	}
    
//    /**
//	 * Method to get the record form.
//	 *
//	 * @param	array	$data		Data for the form.
//	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
//	 * @return	mixed	A JForm object on success, false on failure
//	 * @since	1.6
//	 */
//	public function getForm($data = array(), $loadData = true) 
//	{
//		// Reference global application object
//        $app = JFactory::getApplication();
//        // JInput object
//        $jinput = $app->input;
//        $option = $jinput->getCmd('option');
//        $db		= $this->getDbo();
//        $query = $db->getQuery(true);
//        $cfg_which_media_tool = JComponentHelper::getParams($option)->get('cfg_which_media_tool',0);
//        //$app->enqueueMessage(JText::_('sportsmanagementModelagegroup getForm cfg_which_media_tool<br><pre>'.print_r($cfg_which_media_tool,true).'</pre>'),'Notice');
//        // Get the form.
//		$form = $this->loadForm('com_sportsmanagement.clubname', 'clubname', array('control' => 'jform', 'load_data' => $loadData));
//		if (empty($form)) 
//		{
//			return false;
//		}
//                
//           
//		return $form;
//	}
    
//    /**
//	 * Method to save the form data.
//	 *
//	 * @param	array	The form data.
//	 * @return	boolean	True on success.
//	 * @since	1.6
//	 */
//	public function save($data)
//	{
//	   // Reference global application object
//        $app = JFactory::getApplication();
//        // JInput object
//        $jinput = $app->input;
//       $date = JFactory::getDate();
//	   $user = JFactory::getUser();
//       $post = $jinput->post->getArray(array());
//       // Set the values
//	   $data['modified'] = $date->toSql();
//	   $data['modified_by'] = $user->get('id');
//       
//       $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'');
//        
//       // zuerst sichern, damit wir bei einer neuanlage die id haben
//       if ( parent::save($data) )
//       {
//			$id =  (int) $this->getState($this->getName().'.id');
//            $isNew = $this->getState($this->getName() . '.new');
//            $data['id'] = $id;
//            
//            if ( $isNew )
//            {
//                //Here you can do other tasks with your newly saved record...
//                $app->enqueueMessage(JText::plural(strtoupper($option) . '_N_ITEMS_CREATED', $id),'');
//            }
//           
//		}
//        
//        return true;  
//    }   
    
    
    /**
     * sportsmanagementModelclubname::import()
     * 
     * @return void
     */
    public function import()
    {
    // Reference global application object
        $app = JFactory::getApplication();
        // Create a new query object.		 
	$db = sportsmanagementHelper::getDBConnection(); 
$query = $db->getQuery(true);

        $option = JFactory::getApplication()->input->getCmd('option');
        // JInput object
        $jinput = $app->input;    

$xml = JFactory::getXML(JPATH_ADMINISTRATOR.'/components/'.$option.'/helpers/xml_files/clubnames.xml',true);
        
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' xml <br><pre>'.print_r($xml ,true).'</pre>'),'');    
     
     foreach( $xml->children() as $quote )  
             { 
              
             $country = (string)$quote->clubname->attributes()->country; 
             $name = (string)$quote->clubname->attributes()->name; 
             $clubname = (string)$quote->clubname;
             
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' country <br><pre>'.print_r($country ,true).'</pre>'),'Notice');             
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' name <br><pre>'.print_r($name ,true).'</pre>'),'Notice');             
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' clubname<br><pre>'.print_r($clubname,true).'</pre>'),'Notice');                          
  
$query->clear();  
$query->select('id');  
$query->from('#__sportsmanagement_club_names');
$query->where('country LIKE '.$db->Quote(''.$country.'') );
$query->where('name LIKE '.$db->Quote(''.$name.'') );
$db->setQuery($query);  
  
$result = $db->loadResult();  
  
if ( !$result )
{
$insertquery = $db->getQuery(true); 
// Insert columns. 
$columns = array('country','name','name_long'); 
// Insert values. 
$values = array('\''.$country.'\'','\''.$name.'\'','\''.$clubname.'\''); 
// Prepare the insert query. 
$insertquery 
->insert($db->quoteName('#__sportsmanagement_club_names')) 
->columns($db->quoteName($columns)) 
->values(implode(',', $values)); 
// Set the query using our newly populated query object and execute it. 
$db->setQuery($insertquery); 

sportsmanagementModeldatabasetool::runJoomlaQuery();

} 
		}
        
        
    } 
    
}
