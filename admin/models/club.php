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
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $db		= $this->getDbo();
        $query = $db->getQuery(true);
        $cfg_which_media_tool = JComponentHelper::getParams($option)->get('cfg_which_media_tool',0);
        $show_team_community = JComponentHelper::getParams($option)->get('show_team_community',0);
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelagegroup getForm cfg_which_media_tool<br><pre>'.print_r($cfg_which_media_tool,true).'</pre>'),'Notice');
        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.club', 'club', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
        
        if ( !$show_team_community )
        {
            $form->setFieldAttribute('merge_teams', 'type', 'hidden');
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
        $form->setFieldAttribute('trikot_home', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/clubs/trikot_home');
        $form->setFieldAttribute('trikot_home', 'type', $cfg_which_media_tool);
        
        $form->setFieldAttribute('trikot_away', 'default', JComponentHelper::getParams($option)->get('ph_logo_small',''));
        $form->setFieldAttribute('trikot_away', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/clubs/trikot_away');
        $form->setFieldAttribute('trikot_away', 'type', $cfg_which_media_tool);
        
        $prefix = $mainframe->getCfg('dbprefix');
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' prefix<br><pre>'.print_r($prefix,true).'</pre>'),'');
        //$whichtabel = $this->getTable();
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' whichtabel<br><pre>'.print_r($whichtabel,true).'</pre>'),'');
        
        $query->select('*');
			$query->from('information_schema.columns');
            $query->where("TABLE_NAME LIKE '".$prefix."sportsmanagement_club' ");
			
			$db->setQuery($query);
            
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' dump<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            
			$result = $db->loadObjectList();
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result<br><pre>'.print_r($result,true).'</pre>'),'');
            
            foreach($result as $field )
        {
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' COLUMN_NAME<br><pre>'.print_r($field->COLUMN_NAME,true).'</pre>'),'');
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' DATA_TYPE<br><pre>'.print_r($field->DATA_TYPE,true).'</pre>'),'');
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' CHARACTER_MAXIMUM_LENGTH<br><pre>'.print_r($field->CHARACTER_MAXIMUM_LENGTH,true).'</pre>'),'');
            
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
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function save($data)
	{
	   $mainframe = JFactory::getApplication();
       $address_parts = array();
       $post = JRequest::get('post');
       
       //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'');
       //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'');
       
       // wurden jahre mitgegeben ?
       if ( $data['founded'] != '0000-00-00' )
        {
        $founded_year = date('Y',strtotime($data['founded']));
        }
        else
        {
            $founded_year = NULL;
        }
        
        if ( $data['dissolved'] != '0000-00-00' )
        {
        $dissolved_year = date('Y',strtotime($data['dissolved']));
        }
        else
        {
            $dissolved_year = NULL;
        }
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' founded_year<br><pre>'.print_r($founded_year,true).'</pre>'),'');
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' dissolved_year<br><pre>'.print_r($dissolved_year,true).'</pre>'),'');
        
        if ( $founded_year != '0000' )
        {
            $data['founded_year'] = $founded_year;
            $post['founded_year'] = $founded_year;
        }
        if ( $dissolved_year != '0000' )
        {
            $data['dissolved_year'] = $dissolved_year;
            $post['dissolved_year'] = $dissolved_year;
        }
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'');
        
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
			}
			else
			{
				$address_parts[] = $data['location'];
			}
		}
		if (!empty($data['country']))
		{
			$address_parts[] = JSMCountries::getShortCountryName($data['country']);
		}
		$address = implode(', ', $address_parts);
		$coords = sportsmanagementHelper::resolveLocation($address);
		
		//$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' coords<br><pre>'.print_r($coords,true).'</pre>' ),'');
        
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
		
		//$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' coords<br><pre>'.print_r($coords,true).'</pre>' ),'');
        
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
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelclub save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        
        //-------extra fields-----------//
        sportsmanagementHelper::saveExtraFields($post,$data['id']);
        
        // Proceed with the save
		return parent::save($data);   
    }
    
    
    
    
}
