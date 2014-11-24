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
 
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');

/**
 * sportsmanagementModeljlextcountry
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModeljlextcountry extends JModelAdmin
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
	public function getTable($type = 'jlextcountry', $prefix = 'sportsmanagementTable', $config = array()) 
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
		$app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $db		= $this->getDbo();
        $query = $db->getQuery(true);
        $cfg_which_media_tool = JComponentHelper::getParams($option)->get('cfg_which_media_tool',0);
        //$app->enqueueMessage(JText::_('sportsmanagementModelagegroup getForm cfg_which_media_tool<br><pre>'.print_r($cfg_which_media_tool,true).'</pre>'),'Notice');
        
        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.jlextcountry', 'jlextcountry', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
        
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($option)->get('ph_flags',''));
        $form->setFieldAttribute('picture', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/flags');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
        
        $prefix = $app->getCfg('dbprefix');
        
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' prefix<br><pre>'.print_r($prefix,true).'</pre>'),'');
        //$whichtabel = $this->getTable();
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' whichtabel<br><pre>'.print_r($whichtabel,true).'</pre>'),'');
        
        $query->select('*');
			$query->from('information_schema.columns');
            $query->where("TABLE_NAME LIKE '".$prefix."sportsmanagement_countries' ");
			
			$db->setQuery($query);
            
            //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' dump<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            
			$result = $db->loadObjectList();
            //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' result<br><pre>'.print_r($result,true).'</pre>'),'');
            
            foreach($result as $field )
        {
            //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' COLUMN_NAME<br><pre>'.print_r($field->COLUMN_NAME,true).'</pre>'),'');
            //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' DATA_TYPE<br><pre>'.print_r($field->DATA_TYPE,true).'</pre>'),'');
            //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' CHARACTER_MAXIMUM_LENGTH<br><pre>'.print_r($field->CHARACTER_MAXIMUM_LENGTH,true).'</pre>'),'');
            
            switch ($field->DATA_TYPE)
            {
                case 'varchar':
                $form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
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
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.jlextcountry.data', array());
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
	   $app = JFactory::getApplication();
       $date = JFactory::getDate();
	   $user = JFactory::getUser();
       $post = JRequest::get('post');
       // Set the values
	   $data['modified'] = $date->toSql();
	   $data['modified_by'] = $user->get('id');
        
       // Proceed with the save
	   return parent::save($data); 
    } 
    
    
    /**
     * sportsmanagementModeljlextcountry::importplz()
     * 
     * @return void
     */
    function importplz()
    {
    $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);    
        // Get the input
        $pks = JRequest::getVar('cid', null, 'post', 'array');
        $base_Dir = JPATH_SITE . DS . 'tmp' . DS ;
        $cfg_plz_server = JComponentHelper::getParams($option)->get('cfg_plz_server','');
        
        //$app->enqueueMessage(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
        
        for ($x=0; $x < count($pks); $x++)
		{
			$tblCountry = $this->getTable();
			$tblCountry->load($pks[$x]);
            
            $alpha2 = $tblCountry->alpha2;
            //$app->enqueueMessage(__METHOD__.' '.__LINE__.' alpha2<br><pre>'.print_r($alpha2, true).'</pre><br>','Notice');
            
            $filename = $alpha2.'.zip';
            $linkaddress = $cfg_plz_server.$filename;
            
            $filepath = $base_Dir . $filename;

if ( !copy($linkaddress,$filepath) )
{
$app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_COPY_PLZ_ERROR'),'Error');
}
else
{
$app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_COPY_PLZ_SUCCESS'),'Notice'); 
$result = JArchive::extract($filepath,$base_Dir);  

if ( $result )
{
$app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_COPY_PLZ_ZIP_SUCCESS'),'Notice'); 

$file = $base_Dir.$alpha2.'.txt';

//$app->enqueueMessage(__METHOD__.' '.__LINE__.' file<br><pre>'.print_r($file, true).'</pre><br>','Notice');

$source	= JFile::read($file);

//$app->enqueueMessage(JText::_('source <br><pre>'.print_r($source,true).'</pre>'   ),'');

//# tab delimited, and encoding conversion
	$csv = new parseCSV();
	//$csv->encoding('UTF-16', 'UTF-8');
	$csv->delimiter = "\t";
    $csv->heading = false;
    $csv->parse($source);
    
    $diddipoeler = 0;
    
    foreach ($csv->data as $key => $row)
	{
	   $temp = new stdClass();
		$temp->id = $key;
	    
        if ( !$diddipoeler )
        {
        //$app->enqueueMessage(JText::_('row <br><pre>'.print_r($row,true).'</pre>'   ),'');
        }
		
        for ($a=0; $a < count($row); $a++)
		{
		switch ($a)
        {
            case 0:
            $temp->country_code = $row[$a];
            break;
            case 1:
            $temp->postal_code = $row[$a];
            break;
            case 2:
            $temp->place_name = $row[$a];
            break;
            
            case 3:
            $temp->admin_name1 = $row[$a];
            break;
            case 4:
            $temp->admin_code1 = $row[$a];
            break;
            case 5:
            $temp->admin_name2 = $row[$a];
            break;
            
            case 9:
            $temp->latitude = $row[$a];
            break;
            case 10:
            $temp->longitude = $row[$a];
            break;
            case 11:
            $temp->accuracy = $row[$a];
            break;
        }  
        }  

        /*
        foreach ($row as $value)
		{
//		$temp = new stdClass();
//		$temp->value = $value;
//        $exportplayer[] = $temp;
        //    $app->enqueueMessage(JText::_('value <br><pre>'.print_r($value,true).'</pre>'   ),'');
		}
        */
	    $exportplayer[] = $temp;
        
        $diddipoeler++;
	}
    
    //# auto-detect delimiter character
	//$csv = new parseCSV();
	//$csv->auto($file);
	//print_r($csv->data);
    
	//$app->enqueueMessage(JText::_('daten <br><pre>'.print_r($csv->data,true).'</pre>'   ),'');
    /*
    // anfang schleife csv file
	for($a=0; $a < sizeof($csv->data); $a++  )
	{
		$temp = new stdClass();
		$temp->id = 0;
		$temp->knvbnr = $csv->data[$a];
        $exportplayer[] = $temp;

//        $app->enqueueMessage(JText::_('daten <br><pre>'.print_r($csv->data[$a],true).'</pre>'   ),'');
    }
    */
    
    //$app->enqueueMessage(JText::_('daten <br><pre>'.print_r($exportplayer,true).'</pre>'   ),'');  
    
    foreach ($exportplayer as $value)
		{
		// Create and populate an object.
        $profile = new stdClass();
        $profile->country_code = $value->country_code;
            $profile->postal_code = $value->postal_code;
            $profile->place_name = $value->place_name;
            $profile->admin_name1 = $value->admin_name1;
            $profile->admin_code1 = $value->admin_code1;
            $profile->admin_name2 = $value->admin_name2;
            $profile->latitude = $value->latitude;
            $profile->longitude = $value->longitude;
            $profile->accuracy = $value->accuracy;
        // Insert the object into the table.
        $result = JFactory::getDbo()->insertObject('#__sportsmanagement_countries_plz', $profile);  
        }  
}
else
{
$app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_COPY_PLZ_ZIP_ERROR'),'Error');    
}


 
}         
		}
        
    }  
	
}
