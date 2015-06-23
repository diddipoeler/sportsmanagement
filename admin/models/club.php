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
class sportsmanagementModelclub extends JModelAdmin
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
	public function getTable($type = 'club', $prefix = 'sportsmanagementTable', $config = array()) 
	{
	$config['dbo'] = sportsmanagementHelper::getDBConnection(); 
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
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db		= sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true);
        $cfg_which_media_tool = JComponentHelper::getParams($option)->get('cfg_which_media_tool',0);
        $show_team_community = JComponentHelper::getParams($option)->get('show_team_community',0);
        
        $cfg_use_plz_table = JComponentHelper::getParams($option)->get('cfg_use_plz_table',0);
        
        //$app->enqueueMessage(JText::_('sportsmanagementModelagegroup getForm cfg_which_media_tool<br><pre>'.print_r($cfg_which_media_tool,true).'</pre>'),'Notice');
        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.club', 'club', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
        
        $row = $this->getTable();
        $row->load((int) $form->getValue('id'));
        $country = $row->country;
        
        /**
         * soll die postleitzahlendatentabelle genutzt werden ?
         */        
        if ( $cfg_use_plz_table )
        {
        /**
        * wenn es aber zu dem land keine einträge
        * in der plz tabelle gibt, dann die normale
        * eingabe dem user anbieten        
        */
        $query->select('count(*) as anzahl');
        $query->from('#__sportsmanagement_countries_plz as a');
        $query->join('INNER', '#__sportsmanagement_countries AS c ON c.alpha2 = a.country_code'); 
        $query->where('c.alpha3 LIKE ' . $db->Quote(''.$country.'') );
        $db->setQuery($query);
        $result = $db->loadResult();
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result<br><pre>'.print_r($result,true).'</pre>'),'');
        
        if ( $result )
        {    
        $form->setFieldAttribute('zipcode', 'type', 'dependsql', 'request');
        $form->setFieldAttribute('zipcode', 'size', '10', 'request');     
        $form->setFieldAttribute('location', 'type', 'dependsql', 'request');
        $form->setFieldAttribute('location', 'size', '10', 'request');
        }
        
        }
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' row<br><pre>'.print_r($row,true).'</pre>'),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' country<br><pre>'.print_r($country,true).'</pre>'),'');
        
        if ( !$show_team_community )
        {
            $form->setFieldAttribute('merge_teams', 'type', 'hidden');
        }
        
        // welche joomla version ?
        if(version_compare(JVERSION,'3.0.0','ge')) 
        {
        $form->setFieldAttribute('founded', 'type', 'calendar');
        $form->setFieldAttribute('dissolved', 'type', 'calendar');  
        }
        else
        {
        $form->setFieldAttribute('founded', 'type', 'customcalendar');  
        $form->setFieldAttribute('dissolved', 'type', 'customcalendar');
        }
        
        $form->setFieldAttribute('logo_small', 'default', JComponentHelper::getParams($option)->get('ph_logo_small',''));
        $form->setFieldAttribute('logo_small', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/clubs/small');
        $form->setFieldAttribute('logo_small', 'type', $cfg_which_media_tool);
        
        $form->setFieldAttribute('logo_middle', 'default', JComponentHelper::getParams($option)->get('ph_logo_medium',''));
        $form->setFieldAttribute('logo_middle', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/clubs/medium');
        $form->setFieldAttribute('logo_middle', 'type', $cfg_which_media_tool);
        
        $form->setFieldAttribute('logo_big', 'default', JComponentHelper::getParams($option)->get('ph_logo_big',''));
        $form->setFieldAttribute('logo_big', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/clubs/large');
        $form->setFieldAttribute('logo_big', 'type', $cfg_which_media_tool);
        
        $form->setFieldAttribute('trikot_home', 'default', JComponentHelper::getParams($option)->get('ph_logo_small',''));
        //$form->setFieldAttribute('trikot_home', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/clubs/trikot_home');
        $form->setFieldAttribute('trikot_home', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/clubs/trikot');
        $form->setFieldAttribute('trikot_home', 'type', $cfg_which_media_tool);
        
        $form->setFieldAttribute('trikot_away', 'default', JComponentHelper::getParams($option)->get('ph_logo_small',''));
        //$form->setFieldAttribute('trikot_away', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/clubs/trikot_away');
        $form->setFieldAttribute('trikot_away', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/clubs/trikot');
        $form->setFieldAttribute('trikot_away', 'type', $cfg_which_media_tool);
        
        $prefix = $app->getCfg('dbprefix');
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' prefix<br><pre>'.print_r($prefix,true).'</pre>'),'');
        //$whichtabel = $this->getTable();
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' whichtabel<br><pre>'.print_r($whichtabel,true).'</pre>'),'');
        
        $query->clear();
        $query->select('*');
			$query->from('information_schema.columns');
            $query->where("TABLE_NAME LIKE '".$prefix."sportsmanagement_club' ");
			
			$db->setQuery($query);
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' dump<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            
			$result = $db->loadObjectList();
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result<br><pre>'.print_r($result,true).'</pre>'),'');
            
            foreach($result as $field )
        {
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' COLUMN_NAME<br><pre>'.print_r($field->COLUMN_NAME,true).'</pre>'),'');
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' DATA_TYPE<br><pre>'.print_r($field->DATA_TYPE,true).'</pre>'),'');
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' CHARACTER_MAXIMUM_LENGTH<br><pre>'.print_r($field->CHARACTER_MAXIMUM_LENGTH,true).'</pre>'),'');
            
            switch ($field->COLUMN_NAME)
            {
                case 'country':
                case 'merge_teams':
                break;
                default:
            switch ($field->DATA_TYPE)
            {
                case 'varchar':
                $form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
                break;
            }
                break;
            }
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
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.club.data', array());
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
$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t');
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
	public function save($data)
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

       $data['country'] = $data['request']['country'];
       $data['zipcode'] = $data['request']['zipcode'];
       $data['location'] = $data['request']['location'];
       $data['address'] = $data['request']['address'];
       
/**
 *        wenn die plz tabelle genutzt werden soll
 *        dann können wir vorher schon einmal die latitude und longitude selektieren.
 */
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
       if ( !empty($data['founded']) )
       {
       $data['founded']	= sportsmanagementHelper::convertDate($data['founded'],0);
       }
       if ( !empty($data['dissolved']) )
       {
       $data['dissolved'] = sportsmanagementHelper::convertDate($data['dissolved'],0);
       }
        
       if ( $data['founded'] == '' )
        {
        $data['founded'] = '0000-00-00';   
        $data['founded_year'] = ''; 
        }
       if ( $data['founded'] != '0000-00-00'  )
        {
        $data['founded_year'] = date('Y',strtotime($data['founded']));
        //$post['founded_year'] = date('Y',strtotime($data['founded']));
        }
        else
        {
            $data['founded_year'] = $data['founded_year'];
        }
        
        if ( $data['dissolved'] == '' )
        {
        $data['dissolved'] = '0000-00-00';   
        $data['dissolved_year'] = ''; 
        }
        
        if ( $data['dissolved'] != '0000-00-00' )  
        {
        $data['dissolved_year'] = date('Y',strtotime($data['dissolved']));
        //$post['dissolved_year'] = date('Y',strtotime($data['dissolved']));
        }
        else
        {
            $data['dissolved_year'] = $data['dissolved_year'];
        }
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'');
        
        if (!empty($data['address']))
		{
			$address_parts[] = $data['address'];
		}
		if (!empty($data['state']))
		{
			$address_parts[] = $data['state'];
		}
		if (!empty($data['location']))
		{
			if (!empty($data['zipcode']))
			{
				$address_parts[] = $data['zipcode']. ' ' .$data['location'];
                $address_parts2[] = $data['zipcode']. ' ' .$data['location'];
			}
			else
			{
				$address_parts[] = $data['location'];
                $address_parts2[] = $data['location'];
			}
		}
		if (!empty($data['country']))
		{
			$address_parts[] = JSMCountries::getShortCountryName($data['country']);
            $address_parts2[] = JSMCountries::getShortCountryName($data['country']);
		}
		$address = implode(', ', $address_parts);
		$coords = sportsmanagementHelper::resolveLocation($address);
		
//		$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' address_parts<br><pre>'.print_r($address_parts,true).'</pre>' ),'');
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' coords<br><pre>'.print_r($coords,true).'</pre>' ),'');
        
        if ( !$coords )
        {
        $address = implode(', ', $address_parts2);
		$coords = sportsmanagementHelper::resolveLocation($address);
//		$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' address_parts2<br><pre>'.print_r($address_parts2,true).'</pre>' ),'');
//		$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' coords<br><pre>'.print_r($coords,true).'</pre>' ),'');    
        }    
        
        if ( $coords )
        {
        foreach( $coords as $key => $value )
		{
        $post['extended'][$key] = $value;
        }
		
		$data['latitude'] = $coords['latitude'];
		$data['longitude'] = $coords['longitude'];
        }
        else
        {
        $address_parts = array();
        if (!empty($data['address']))
		{
		$address_parts[] = $data['address'];
		}
        if (!empty($data['location']))
		{
		$address_parts[] = $data['location'];
		}
		if (!empty($data['country']))
		{
		$address_parts[] = JSMCountries::getShortCountryName($data['country']);
		}
        
        $address = implode(',', $address_parts);
        $coords = sportsmanagementHelper::getOSMGeoCoords($address);
		
		//$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' coords<br><pre>'.print_r($coords,true).'</pre>' ),'');
        
        $data['latitude'] = $coords['latitude'];
		$data['longitude'] = $coords['longitude'];
        
        foreach( $coords as $key => $value )
		{
        $post['extended'][$key] = $value;
        }
            
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
