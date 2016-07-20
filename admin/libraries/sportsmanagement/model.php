<?PHP        
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');


/**
 * JSMModelAdmin
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class JSMModelAdmin extends JModelAdmin
{
    

/**
 * JSMModelAdmin::__construct()
 * 
 * @param mixed $config
 * @return void
 */
public function __construct($config = array())
        {   
 
        parent::__construct($config);
        $getDBConnection = sportsmanagementHelper::getDBConnection();
        parent::setDbo($getDBConnection);
        $this->jsmdb = sportsmanagementHelper::getDBConnection();
        parent::setDbo($this->jsmdb);
        $this->jsmquery = $this->jsmdb->getQuery(true);
        $this->jsmsubquery1 = $this->jsmdb->getQuery(true); 
        $this->jsmsubquery2 = $this->jsmdb->getQuery(true); 
        $this->jsmsubquery3 = $this->jsmdb->getQuery(true);  
        // Reference global application object
        $this->jsmapp = JFactory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;
        $this->jsmoption = $this->jsmjinput->getCmd('option');
        $this->jsmdocument = JFactory::getDocument();
        $this->jsmuser = JFactory::getUser(); 
        $this->jsmdate = JFactory::getDate();

/**
 * abfrage nach backend und frontend  
 */ 
if ( $this->jsmapp->isAdmin() )
{
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' isAdmin<br><pre>'.print_r($this->jsmapp->isAdmin(),true).'</pre>'),'');    
}  
if( $this->jsmapp->isSite() )
{
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' isSite<br><pre>'.print_r($this->jsmapp->isSite(),true).'</pre>'),'');    
}    
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
		$cfg_which_media_tool = JComponentHelper::getParams($this->jsmoption)->get('cfg_which_media_tool',0);
        $show_team_community = JComponentHelper::getParams($this->jsmoption)->get('show_team_community',0);
        $cfg_use_plz_table = JComponentHelper::getParams($this->jsmoption)->get('cfg_use_plz_table',0);
        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.'.$this->getName(), $this->getName(), array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
        
        switch ($this->getName())
		{
		case 'club':
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
        $this->jsmquery->clear();
        $this->jsmquery->select('count(*) as anzahl');
        $this->jsmquery->from('#__sportsmanagement_countries_plz as a');
        $this->jsmquery->join('INNER', '#__sportsmanagement_countries AS c ON c.alpha2 = a.country_code'); 
        $this->jsmquery->where('c.alpha3 LIKE ' . $this->jsmdb->Quote(''.$country.'') );
        $this->jsmdb->setQuery($this->jsmquery);
        $result = $this->jsmdb->loadResult();

        if ( $result )
        {    
        $form->setFieldAttribute('zipcode', 'type', 'dependsql', 'request');
        $form->setFieldAttribute('zipcode', 'size', '10', 'request');     
        $form->setFieldAttribute('location', 'type', 'dependsql', 'request');
        $form->setFieldAttribute('location', 'size', '10', 'request');
        }
        
        }

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
        
        $form->setFieldAttribute('logo_small', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_logo_small',''));
        $form->setFieldAttribute('logo_small', 'directory', 'com_sportsmanagement/database/clubs/small');
        $form->setFieldAttribute('logo_small', 'type', $cfg_which_media_tool);
        
        $form->setFieldAttribute('logo_middle', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_logo_medium',''));
        $form->setFieldAttribute('logo_middle', 'directory', 'com_sportsmanagement/database/clubs/medium');
        $form->setFieldAttribute('logo_middle', 'type', $cfg_which_media_tool);
        
        $form->setFieldAttribute('logo_big', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_logo_big',''));
        $form->setFieldAttribute('logo_big', 'directory', 'com_sportsmanagement/database/clubs/large');
        $form->setFieldAttribute('logo_big', 'type', $cfg_which_media_tool);
        
        $form->setFieldAttribute('trikot_home', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_logo_small',''));
        //$form->setFieldAttribute('trikot_home', 'directory', 'com_sportsmanagement/database/clubs/trikot_home');
        $form->setFieldAttribute('trikot_home', 'directory', 'com_sportsmanagement/database/clubs/trikot');
        $form->setFieldAttribute('trikot_home', 'type', $cfg_which_media_tool);
        
        $form->setFieldAttribute('trikot_away', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_logo_small',''));
        //$form->setFieldAttribute('trikot_away', 'directory', 'com_sportsmanagement/database/clubs/trikot_away');
        $form->setFieldAttribute('trikot_away', 'directory', 'com_sportsmanagement/database/clubs/trikot');
        $form->setFieldAttribute('trikot_away', 'type', $cfg_which_media_tool);
        
        $prefix = $app->getCfg('dbprefix');

        $this->jsmquery->clear();
        $this->jsmquery->select('*');
			$this->jsmquery->from('information_schema.columns');
            $this->jsmquery->where("TABLE_NAME LIKE '".$prefix."sportsmanagement_club' ");
			
			$this->jsmdb->setQuery($this->jsmquery);

			$result = $this->jsmdb->loadObjectList();

            foreach($result as $field )
        {

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
        break;
        case 'agegroup':
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_icon',''));
        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/agegroups');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
        break;
        case 'league':
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_icon',''));
        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/leagues');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);  
        break;
        case 'person':
                switch($form->getValue('person_art'))
        {
            case 1:
//            $form->setFieldAttribute('person_id1', 'type', 'hidden');
//            $form->setFieldAttribute('person_id2', 'type', 'hidden');            
            break;
            case 2:
//            $form->setFieldAttribute('person_id1', 'type', 'personlist');
//            $form->setFieldAttribute('person_id2', 'type', 'personlist');
            break;
            
        }
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_player',''));
        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/persons');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
        
        // welche joomla version ?
        if(version_compare(JVERSION,'3.0.0','ge')) 
        {
        $form->setFieldAttribute('contact_id', 'type', 'modal_contact');
        $form->setFieldAttribute('birthday', 'type', 'calendar');
        $form->setFieldAttribute('deathday', 'type', 'calendar'); 
        }
        else
        {
        $form->setFieldAttribute('contact_id', 'type', 'modal_contacts');  
        $form->setFieldAttribute('birthday', 'type', 'customcalendar');
        $form->setFieldAttribute('deathday', 'type', 'customcalendar');  
        }

        $prefix = $this->jsmapp->getCfg('dbprefix');
        
        $this->jsmquery->clear();
        $this->jsmquery->select('*');
			$this->jsmquery->from('information_schema.columns');
            $this->jsmquery->where("TABLE_NAME LIKE '".$prefix."sportsmanagement_person' ");
			
			$this->jsmdb->setQuery($this->jsmquery);
            
			$result = $this->jsmdb->loadObjectList();
            
            foreach($result as $field )
        {
            
            switch ($field->DATA_TYPE)
            {
                case 'varchar':
                $form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
                break;
            }
            
           } 
        
        break;
        }
		return $form;
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
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.'.$this->getName().'.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
		}
		return $data;
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
	public function getTable($type = '', $prefix = 'sportsmanagementTable', $config = array()) 
	{
       $config['dbo'] = sportsmanagementHelper::getDBConnection();
       if ( empty($type) )
       {
       $type = $this->getName();
       } 
		return JTable::getInstance($type, $prefix, $config);
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

        $row = $this->getTable();
		
		// update ordering values
		for ($i=0; $i < count($pks); $i++)
		{
			$row->load((int) $pks[$i]);
			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];
                $row->modified = $this->jsmdate->toSql();
                $row->modified_by = $this->jsmuser->get('id');
				if (!$row->store())
				{
					sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
					return false;
				}
			}
		}
		return true;
	}
                
}

/**
 * JSMModelList
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class JSMModelList extends JModelList
{
    
/**
 * JSMModelList::__construct()
 * 
 * @param mixed $config
 * @return void
 */
public function __construct($config = array())
        {   
 
        parent::__construct($config);
        $getDBConnection = sportsmanagementHelper::getDBConnection();
        parent::setDbo($getDBConnection);
        $this->jsmdb = sportsmanagementHelper::getDBConnection();
        parent::setDbo($this->jsmdb);
        $this->jsmquery = $this->jsmdb->getQuery(true);
        $this->jsmsubquery1 = $this->jsmdb->getQuery(true); 
        $this->jsmsubquery2 = $this->jsmdb->getQuery(true); 
        $this->jsmsubquery3 = $this->jsmdb->getQuery(true);  
        // Reference global application object
        $this->jsmapp = JFactory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;
        $this->jsmoption = $this->jsmjinput->getCmd('option');
        $this->jsmdocument = JFactory::getDocument();
        $this->jsmuser = JFactory::getUser(); 

/**
 * abfrage nach backend und frontend  
 */ 
if ( $this->jsmapp->isAdmin() )
{
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' isAdmin<br><pre>'.print_r($this->jsmapp->isAdmin(),true).'</pre>'),'');    
}  
if( $this->jsmapp->isSite() )
{
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' isSite<br><pre>'.print_r($this->jsmapp->isSite(),true).'</pre>'),'');    
}    
        }    
    
}


/**
 * JSMModelLegacy
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class JSMModelLegacy extends JModelLegacy
{
    
/**
 * JSMModelLegacy::__construct()
 * 
 * @param mixed $config
 * @return void
 */
public function __construct($config = array())
        {   
 
        parent::__construct($config);
        $getDBConnection = sportsmanagementHelper::getDBConnection();
        parent::setDbo($getDBConnection);
        $this->jsmdb = sportsmanagementHelper::getDBConnection();
        parent::setDbo($this->jsmdb);
        $this->jsmquery = $this->jsmdb->getQuery(true);
        $this->jsmsubquery1 = $this->jsmdb->getQuery(true); 
        $this->jsmsubquery2 = $this->jsmdb->getQuery(true); 
        $this->jsmsubquery3 = $this->jsmdb->getQuery(true);  
        // Reference global application object
        $this->jsmapp = JFactory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;
        $this->jsmoption = $this->jsmjinput->getCmd('option');
        $this->jsmdocument = JFactory::getDocument();
        $this->jsmuser = JFactory::getUser(); 
        
/**
 * abfrage nach backend und frontend  
 */        
if ( $this->jsmapp->isAdmin() )
{
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' isAdmin<br><pre>'.print_r($this->jsmapp->isAdmin(),true).'</pre>'),'');    
}  
if( $this->jsmapp->isSite() )
{
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' isSite<br><pre>'.print_r($this->jsmapp->isSite(),true).'</pre>'),'');    
} 
        
        }    
    
}


?>    