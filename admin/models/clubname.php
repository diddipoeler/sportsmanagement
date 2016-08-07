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
    
    /**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function save($data)
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
       $date = JFactory::getDate();
	   $user = JFactory::getUser();
       $post = $jinput->get('post');
       // Set the values
	   $data['modified'] = $date->toSql();
	   $data['modified_by'] = $user->get('id');
        
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

        $option = JRequest::getCmd('option');
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
