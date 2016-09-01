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
jimport('joomla.application.component.modeladmin');
 

/**
 * sportsmanagementModelclub
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelclub extends JSMModelAdmin
{

	/**
	 * Override parent constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelLegacy
	 * @since   3.2
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	
//    $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' config<br><pre>'.print_r($config,true).'</pre>'),'');
//    $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getName<br><pre>'.print_r($this->getName(),true).'</pre>'),'');
    
	}	


   
   
    /**
	 * Method to update checked clubs
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function saveshort()
	{
		$app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        //$show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
        // Get the input
        $pks = JRequest::getVar('cid', null, 'post', 'array');
        $post = JRequest::get('post');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = 'pks <pre>'.print_r($pks,true).'</pre>';    
        $my_text .= 'post <pre>'.print_r($post,true).'</pre>';
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        }
        
        $result=true;
		for ($x=0; $x < count($pks); $x++)
		{
		  $address_parts = array();
          $address_parts2 = array();
			$tblClub = & $this->getTable();
            
            
			$tblClub->id	= $pks[$x];
            $tblClub->zipcode = $post['zipcode' . $pks[$x]];
			$tblClub->location = $post['location' .$pks[$x]];
            $tblClub->address = $post['address' .$pks[$x]];
            $tblClub->country = $post['country' .$pks[$x]];
            
            if (!empty($tblClub->address))
		{
			$address_parts[] = $tblClub->address;
		}
		
		if (!empty($tblClub->location))
		{
			if (!empty($tblClub->zipcode))
			{
				$address_parts[] = $tblClub->zipcode. ' ' .$tblClub->location;
                $address_parts2[] = $tblClub->zipcode. ' ' .$tblClub->location;
			}
			else
			{
				$address_parts[] = $tblClub->location;
                $address_parts2[] = $tblClub->location;
			}
		}
		if (!empty($tblClub->country))
		{
			$address_parts[] = JSMCountries::getShortCountryName($tblClub->country);
            $address_parts2[] = JSMCountries::getShortCountryName($tblClub->country);
		}
		$address = implode(', ', $address_parts);
		$coords = sportsmanagementHelper::resolveLocation($address);
        
//        $app->enqueueMessage(__METHOD__.' '.__LINE__.'coords <pre>'.print_r($coords, true).'</pre><br>','');
//        $app->enqueueMessage(__METHOD__.' '.__LINE__.'address_parts <pre>'.print_r($address_parts, true).'</pre><br>','');
//        $app->enqueueMessage(__METHOD__.' '.__LINE__.'address_parts2 <pre>'.print_r($address_parts2, true).'</pre><br>','');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text = 'coords <pre>'.print_r($coords,true).'</pre>';    
        $my_text .= 'address_parts <pre>'.print_r($address_parts,true).'</pre>';
        $my_text .= 'address_parts2 <pre>'.print_r($address_parts2,true).'</pre>';
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.'_success_text <pre>'.print_r(sportsmanagementHelper::$_success_text, true).'</pre><br>','');
        }
        
        if ( $coords )
        {
		$tblClub->latitude = $coords['latitude'];
		$tblClub->longitude = $coords['longitude'];
        }
        else
        {
            
        }

			if(!$tblClub->store()) 
            {
				//$this->setError($this->_db->getErrorMsg());
                $app->enqueueMessage(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->_db->getErrorMsg(), true).'</pre><br>','Error');
				$result = false;
			}
		}
		return $result;
	}
    
    
    /**
     * sportsmanagementModelclub::teamsofclub()
     * 
     * @param mixed $club_id
     * @return void
     */
    function teamsofclub($club_id)
    {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db	= sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true);
        
        $query->clear();
$query->select('t.id,t.name');
$query->from('#__sportsmanagement_team AS t');
$query->where('t.club_id = '.$club_id);
$db->setQuery( $query );
$teamsofclub = $db->loadObjectList();
return $teamsofclub; 
        
    }
    
    /**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function savenichtmehr($data)
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
       $date = JFactory::getDate();
	   $user = JFactory::getUser();
       $db = sportsmanagementHelper::getDBConnection();
       
       $query = $db->getQuery(true);
       $address_parts = array();
       $address_parts2 = array();
       $post = JRequest::get('post');
       
       $cfg_use_plz_table = JComponentHelper::getParams($option)->get('cfg_use_plz_table',0);
       
       
//       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'Notice');
//       $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'Notice');

//       $data['country'] = $data['request']['country'];
//       $data['zipcode'] = $data['request']['zipcode'];
//       $data['location'] = $data['request']['location'];
//       $data['address'] = $data['request']['address'];
//       $data['latitude'] = $data['request']['latitude'];
//       $data['longitude'] = $data['request']['longitude'];
       
       
/**
 *        wenn die plz tabelle genutzt werden soll
 *        dann können wir vorher schon einmal die latitude und longitude selektieren.
 */
/*
       if ( $cfg_use_plz_table )
       {
       $query->select('a.latitude,a.longitude ');
        $query->from('#__sportsmanagement_countries_plz as a');
        $query->join('INNER', '#__sportsmanagement_countries AS c ON c.alpha2 = a.country_code'); 
        if ( $data['zipcode'] )
        {
        $query->where('a.postal_code LIKE ' . $db->Quote(''.$data['zipcode'].'') );
        }
        if ( $data['country'] )
        {
        $query->where('c.alpha3 LIKE ' . $db->Quote(''.$data['country'].'') );
        } 
        if ( $data['location'] )
        {
        $query->where('a.place_name LIKE ' . $db->Quote(''.$data['location'].'') );
        }
       $db->setQuery($query);
        $result = $db->loadObject(); 
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' cfg_use_plz_table<br><pre>'.print_r($result,true).'</pre>'),'Notice');
        
        $data['latitude'] = $result->latitude;
		$data['longitude'] = $result->longitude;
        
       }
*/       
       
       
       // gibt es vereinsnamen zum ändern ?
       if (isset($post['team_id']) && is_array($post['team_id'])) 
       {
        foreach ( $post['team_id'] as $key => $value )
        {
        $team_id = $post['team_id'][$key];  
        $team_name = $post['team_value_id'][$key];  
        // Create an object for the record we are going to update.
        $object = new stdClass();
        // Must be a valid primary key value.
        $object->id = $team_id;
        $object->name = $team_name;
        $object->alias = JFilterOutput::stringURLSafe( $team_name );
        // Update their details in the table using id as the primary key.
        $result = JFactory::getDbo()->updateObject('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team', $object, 'id');
        }
        
       }
       
       // hat der user die bildfelder geleert, werden die standards gesichert.
       if ( empty($data['logo_big']) )
       {
       $data['logo_big'] = JComponentHelper::getParams($option)->get('ph_logo_big','');
       }
       if ( empty($data['logo_middle']) )
       {
       $data['logo_middle'] = JComponentHelper::getParams($option)->get('ph_logo_medium','');
       }
       if ( empty($data['logo_small']) )
       {
       $data['logo_small'] = JComponentHelper::getParams($option)->get('ph_logo_small','');
       }
 
       // wurden jahre mitgegeben ?
       $timestamp = strtotime($data['founded']);
       if ( $timestamp )
       {
       $data['founded']	= sportsmanagementHelper::convertDate($data['founded'],0);
       }
       $timestamp = strtotime($data['dissolved']);
       if ( $timestamp )
       {
       $data['dissolved'] = sportsmanagementHelper::convertDate($data['dissolved'],0);
       }
       
       $timestamp = strtotime($data['founded']); 
       if ( !$timestamp )
        {
        $data['founded'] = '0000-00-00';   
        $data['founded_year'] = ''; 
        }
       if ( $timestamp  )
        {
        $data['founded_year'] = date('Y',strtotime($data['founded']));
        }
        else
        {
            $data['founded_year'] = $data['founded_year'];
        }
        
        
        $timestamp = strtotime($data['dissolved']); 
        if ( !$timestamp )
        {
        $data['dissolved'] = '0000-00-00';   
        $data['dissolved_year'] = ''; 
        }
        
        if ( $timestamp )  
        {
        $data['dissolved_year'] = date('Y',strtotime($data['dissolved']));
        }
        else
        {
            $data['dissolved_year'] = $data['dissolved_year'];
        }

       if (isset($post['extended']) && is_array($post['extended'])) 
		{
			// Convert the extended field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string)$parameter;
		}
        if (isset($post['extendeduser']) && is_array($post['extendeduser'])) 
		{
			// Convert the extended field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($post['extendeduser']);
			$data['extendeduser'] = (string)$parameter;
		}
        
        // Set the values
		$data['modified'] = $date->toSql();
		$data['modified_by'] = $user->get('id');
        
         // Alter the title for Save as Copy
		if ($this->jsmjinput->get('task') == 'save2copy')
		{
			$orig_table = $this->getTable();
			$orig_table->load((int) $this->jsmjinput->getInt('id'));
            $data['id'] = 0;

			if ($data['name'] == $orig_table->name)
			{
				$data['name'] .= ' ' . JText::_('JGLOBAL_COPY');
			}
		}
        
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
        
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' nach bereinigung post<br><pre>'.print_r($post,true).'</pre>'),'Notice');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' nach bereinigung data<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        
        //-------extra fields-----------//
        sportsmanagementHelper::saveExtraFields($post,$data['id']);


		return true;   
    }
    
    
    
    
}
