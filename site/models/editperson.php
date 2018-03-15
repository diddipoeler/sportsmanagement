<?php
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

//jimport('joomla.application.component.model');
//jimport('joomla.application.component.modelitem');
//jimport('joomla.application.component.modelform');

jimport('joomla.application.component.modeladmin');

require_once(JPATH_ROOT.DS.'components'.DS.'com_sportsmanagement'.DS. 'helpers' . DS . 'imageselect.php');


/**
 * sportsmanagementModelEditPerson
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelEditPerson extends JModelAdmin
{
  
  /* interfaces */
	var $latitude	= null;
	var $longitude	= null;
	
	
    /**
     * sportsmanagementModelEditPerson::updItem()
     * 
     * @param mixed $data
     * @return void
     */
    function updItem($data)
    {
        $app = JFactory::getApplication();
        
        foreach( $data['request'] as $key => $value)
        {
            $data[$key] = $value;
        }
        
        // Specify which columns are to be ignored. This can be a string or an array.
        //$ignore = 'id';
        $ignore = '';
        // Get the table object from the model.
        $table = $this->getTable( 'person' );
        // Bind the array to the table object.
        $table->bind( $data, $ignore );
        
        if ( !$table->store() )
        {
            JError::raiseError(500, $db->getErrorMsg());
        }
        else
        {
            
        }
        
/*
        // set the data into a query to update the record
        $db = $this->getDbo();
        $query  = $db->getQuery(true);
        $query->clear();
        $query->update(' #__sportsmanagement_person ');
        
        foreach( $data as $key => $value)
        {
            switch ($key)
            {
                case 'id':
                $id	= $value;
                break;
                default:
                $query->set($key.' = '.$db->Quote($value) );
                break;
            }
        }
        
        $query->where(' id = ' . (int) $id );
        $db->setQuery((string)$query);
*/        
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' table<br><pre>'.print_r($table,true).'</pre>'),'Notice');
        
    }
    
    
    /**
	 * Method to load content person data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	function getData()
	{
	   $this->_id = JFactory::getApplication()->input->getInt('id',0);
//		// Lets load the content if it doesn't already exist
//		if (empty($this->_data))
//		{
			$query='SELECT * FROM #__sportsmanagement_person WHERE id='.(int) $this->_id;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			return $this->_data;
//		}
//		return true;
	}


	/**
	 * Returns a Table object, always creating it
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'person', $prefix = 'sportsmanagementTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.7
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$cfg_which_media_tool = JComponentHelper::getParams(JFactory::getApplication()->input->getCmd('option'))->get('cfg_which_media_tool',0);
        $app = JFactory::getApplication('site');
        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.'.$this->name, $this->name,array('load_data' => $loadData) );
		if (empty($form))
		{
			return false;
		}
        
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams(JFactory::getApplication()->input->getCmd('option'))->get('ph_player',''));
        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/persons');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
        
		return $form;
	}
	
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.7
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.'.$this->name.'.data', array());
		if (empty($data))
		{
			$data = $this->getData();
		}
		return $data;
	}
    
}
