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
 * sportsmanagementModelperson
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelperson extends JModelAdmin
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
	public function getTable($type = 'person', $prefix = 'sportsmanagementTable', $config = array()) 
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
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $db		= $this->getDbo();
        $query = $db->getQuery(true);
        $cfg_which_media_tool = JComponentHelper::getParams($option)->get('cfg_which_media_tool',0);
        
        
        
        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.person', 'person', array('control' => 'jform', 'load_data' => $loadData));
		
        //$item = $this->getItem();
        //$mainframe->set( 'person_art', 2 );
        //JRequest::set('person_art', 2);
        //$form->setValue('request_sports_type_id',$item->sports_type_id);
        //JRequest::setVar('sports_type_id', $item->sports_type_id);
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' data<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' form<br><pre>'.print_r($form,true).'</pre>'),'Notice');
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' item<br><pre>'.print_r($item,true).'</pre>'),'Notice');
        
        if (empty($form)) 
		{
			return false;
		}
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.'<br><pre>'.print_r($form->getValue('person_art'),true).'</pre>'),'Notice');
        
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
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($option)->get('ph_player',''));
        $form->setFieldAttribute('picture', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/persons');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
        
        // welche joomla version ?
        if(version_compare(JVERSION,'3.0.0','ge')) 
        {
        $form->setFieldAttribute('contact_id', 'type', 'modal_contact');
        }
        else
        {
        $form->setFieldAttribute('contact_id', 'type', 'modal_contacts');    
        }

        $prefix = $mainframe->getCfg('dbprefix');
        
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' prefix<br><pre>'.print_r($prefix,true).'</pre>'),'');
        //$whichtabel = $this->getTable();
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' whichtabel<br><pre>'.print_r($whichtabel,true).'</pre>'),'');
        
        $query->select('*');
			$query->from('information_schema.columns');
            $query->where("TABLE_NAME LIKE '".$prefix."sportsmanagement_person' ");
			
			$db->setQuery($query);
            
            //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' dump<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            
			$result = $db->loadObjectList();
            //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' result<br><pre>'.print_r($result,true).'</pre>'),'');
            
            foreach($result as $field )
        {
            //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' COLUMN_NAME<br><pre>'.print_r($field->COLUMN_NAME,true).'</pre>'),'');
            //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' DATA_TYPE<br><pre>'.print_r($field->DATA_TYPE,true).'</pre>'),'');
            //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' CHARACTER_MAXIMUM_LENGTH<br><pre>'.print_r($field->CHARACTER_MAXIMUM_LENGTH,true).'</pre>'),'');
            
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
   * sportsmanagementModelperson::getAgeGroupID()
   * 
   * @param mixed $age
   * @return
   */
  public function getAgeGroupID($age) 
	{
  $mainframe = JFactory::getApplication();
  $option = JRequest::getCmd('option');
  
  if ( is_numeric($age) )
  {
//        // Create a new query object.
//		$db		= $this->getDbo();
		$query	= JFactory::getDbo()->getQuery(true);
        $query->select('id');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_agegroup ');
        $query->where($age." >= age_from and ".$age." <= age_to");
  
//		$query = "SELECT id
//    FROM #__sportsmanagement_agegroup 
//    WHERE ".$age." >= age_from and ".$age." <= age_to";
		
    //$mainframe->enqueueMessage('getAgeGroupID<br><pre>'.print_r($query, true).'</pre><br>','Notice');
		
		JFactory::getDbo()->setQuery($query);
			$person_range = JFactory::getDbo()->loadResult();
            }
            else
            {
            $person_range = 0;    
            }
            return $person_range;
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
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.person.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
		}
		return $data;
	}
	
    /**
	 * return 
	 *
	 * @param int person_id
	 * @return int
	 */
	function getPerson($person_id=0,$season_person_id=0)
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        //// Create a new query object.
//		$db		= $this->getDbo();
		$query	= JFactory::getDbo()->getQuery(true);
        $query->select('p.*');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person as p');
        if ( $person_id )
        {
        $query->where('p.id = '.$person_id);
        }
        if ( $season_person_id )
        {
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_person_id AS tp on tp.person_id = p.id');
        $query->where('tp.id = '.$season_person_id);
        }
        
        
//		$query='SELECT *
//				  FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_person
//				  WHERE id='.$person_id;
		
        JFactory::getDbo()->setQuery($query);
		return JFactory::getDbo()->loadObject();
	}
    
    
    /**
	 * Method to update checked persons
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function saveshort()
	{
		// Get the input
        $pks = JRequest::getVar('cid', null, 'post', 'array');
        $post = JRequest::get('post');
        $result=true;
		for ($x=0; $x < count($pks); $x++)
		{
			$tblPerson = & $this->getTable();
			$tblPerson->id			= $pks[$x];
			$tblPerson->firstname	= $post['firstname'.$pks[$x]];
			$tblPerson->lastname	= $post['lastname'.$pks[$x]];
			$tblPerson->nickname	= $post['nickname'.$pks[$x]];
			$tblPerson->birthday	= sportsmanagementHelper::convertDate($post['birthday'.$pks[$x]],0);
			//$tblPerson->deathday	= $post['deathday'.$pks[$x]];
            $tblPerson->deathday	= sportsmanagementHelper::convertDate($post['deathday'.$pks[$x]],0);
			$tblPerson->country		= $post['country'.$pks[$x]];
			$tblPerson->position_id	= $post['position'.$pks[$x]];
			if(!$tblPerson->store()) 
            {
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
				$result=false;
			}
		}
		return $result;
	}
    
    
    /**
	 * Method to save assign persons
	 *
	 * @access	
	 * @return	
	 * @since	
	 */
    function storeAssign($post)
    {
    $option = JRequest::getCmd('option');
	$mainframe	= JFactory::getApplication();  
    $this->_project_id	= $mainframe->getUserState( "$option.pid", '0' );
    $this->_team_id = $mainframe->getUserState( "$option.team_id", '0' );
    $this->_project_team_id = $mainframe->getUserState( "$option.project_team_id", '0' );
    $this->_season_id = $mainframe->getUserState( "$option.season_id", '0' );
    $cid = $post['cid'];
          
    $mdlPerson = JModel::getInstance("person", "sportsmanagementModel");
    $mdlPersonTable = $mdlPerson->getTable();
    //$mainframe->enqueueMessage(JText::_('sportsmanagementModelPersons storeAssign post<br><pre>'.print_r($post,true).'</pre>'),'');    
    
    switch ($post['type'])
            {
                case 0:
                $mdl = JModel::getInstance("seasonteamperson", "sportsmanagementModel");
                $mdlTable = $mdl->getTable();
                for ($x=0; $x < count($cid); $x++)
                {
                $mdlPersonTable->load($cid[$x]);    
                $mdlTable = $mdl->getTable();
                $mdlTable->team_id = $this->_team_id;
                $mdlTable->season_id = $this->_season_id;
                $mdlTable->persontype = 1;
                $mdlTable->picture = $mdlPersonTable->picture;
                $mdlTable->active = 1;
                $mdlTable->published = 1;
                $mdlTable->person_id = $cid[$x];   
                if ($mdlTable->store()===false)
				{
				    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
				}
				else
				{
				}
                 
		        }
                break;
                case 1:
                $mdl = JModel::getInstance("seasonteamperson", "sportsmanagementModel");
                $mdlTable = $mdl->getTable();
                for ($x=0; $x < count($cid); $x++)
                {
                $mdlPersonTable->load($cid[$x]); 
                $mdlTable = $mdl->getTable();
                $mdlTable->team_id = $this->_team_id;
                $mdlTable->season_id = $this->_season_id;
                $mdlTable->persontype = 2;
                $mdlTable->picture = $mdlPersonTable->picture;
                $mdlTable->active = 1;
                $mdlTable->published = 1;
                $mdlTable->person_id = $cid[$x];   
                if ($mdlTable->store()===false)
				{
				    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
				}
				else
				{
				}
                 
		        }
                break;
                /*
                case 2:
                $mdl = JModel::getInstance("seasonteamperson", "sportsmanagementModel");
                $mdlTable = $mdl->getTable();
                for ($x=0; $x < count($cid); $x++)
                {
                $mdlPersonTable->load($cid[$x]); 
                $mdlTable = $mdl->getTable();
                $mdlTable->project_id = $this->_project_id;
                $mdlTable->picture = $mdlPersonTable->picture;
                $mdlTable->person_id = $cid[$x];   
                if ($mdlTable->store()===false)
				{
				    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
				}
				else
				{
				}
                 
		        }
                break;
                */
                
            }
    
    
    
    return true;    
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
       $option = JRequest::getCmd('option');
       $date = JFactory::getDate();
	   $user = JFactory::getUser();
       $post = JRequest::get('post');
       // Set the values
	   $data['modified'] = $date->toSql();
	   $data['modified_by'] = $user->get('id');
       
       $address_parts = array();
       $person_double = array();
       // Create a new query object.
        $query = JFactory::getDBO()->getQuery(true);
        
       //// Get a db connection.
//        $db = JFactory::getDbo();
       
       //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'Notice');
       //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'Notice');
       
       // request variablen umbauen
       $data['person_art'] = $data['request']['person_art'];
       $data['person_id1'] = $data['request']['person_id1'];
       $data['person_id2'] = $data['request']['person_id2'];
       
       $data['sports_type_id'] = $data['request']['sports_type_id'];
       $data['position_id'] = $data['request']['position_id'];
       $data['agegroup_id'] = $data['request']['agegroup_id'];

       
       switch($data['person_art'])
        {
            case 1:
            break;
            case 2:
            if ( $data['person_id1'] && $data['person_id2'] )
            {
            $person_1 = $data['person_id1'];
            $person_2 = $data['person_id2'];
            $table = 'person';
            $row = JTable::getInstance( $table, 'sportsmanagementTable' );
            $row->load((int) $person_1);
            $person_double[] = $row->firstname.' '.$row->lastname;
            $row->load((int) $person_2);
            $person_double[] = $row->firstname.' '.$row->lastname;
            $data['lastname'] = implode(" - ",$person_double);
            $data['firstname'] = '';
            }
            break;
            
        }
       
       
       
       
        if (isset($data['season_ids']) && is_array($data['season_ids'])) 
		{
		  foreach( $data['season_ids'] as $key => $value )
          {
          
        $query->clear();  
        $query->select('id');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_person_id');
        $query->where('person_id ='. $data['id'] );
        $query->where('season_id ='. $value );
        JFactory::getDbo()->setQuery($query);
		$res = JFactory::getDbo()->loadResult();
        
        if ( !$res )
        {
        $query->clear();
        // Insert columns.
        $columns = array('person_id','season_id');
        // Insert values.
        $values = array($data['id'],$value);
        // Prepare the insert query.
        $query
            ->insert(JFactory::getDBO()->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_person_id'))
            ->columns(JFactory::getDBO()->quoteName($columns))
            ->values(implode(',', $values));
        // Set the query using our newly populated query object and execute it.
        JFactory::getDBO()->setQuery($query);

		if (!sportsmanagementModeldatabasetool::runJoomlaQuery())
		{
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r(JFactory::getDBO()->getErrorMsg(),true).'</pre>'),'Error');
		}  
        
        }
          
          }
		//$mdl = JModel::getInstance("seasonperson", "sportsmanagementModel");
		}
       
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
		
		//$mainframe->enqueueMessage(JText::_('sportsmanagementModelperson coords -> '.'<pre>'.print_r($coords,true).'</pre>' ),'');
        
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
        if (isset($post['extendeduser']) && is_array($post['extendeduser'])) 
		{
			// Convert the extended field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($post['extendeduser']);
			$data['extendeduser'] = (string)$parameter;
		}
        
        $data['birthday']	= sportsmanagementHelper::convertDate($data['birthday'],0);
        $data['deathday']	= sportsmanagementHelper::convertDate($data['deathday'],0);
        
        $data['injury_date_start']	= sportsmanagementHelper::convertDate($data['injury_date_start'],0);
        $data['injury_date_end']	= sportsmanagementHelper::convertDate($data['injury_date_end'],0);
        $data['susp_date_start']	= sportsmanagementHelper::convertDate($data['susp_date_start'],0);
        $data['susp_date_end']	= sportsmanagementHelper::convertDate($data['susp_date_end'],0);
        $data['away_date_start']	= sportsmanagementHelper::convertDate($data['away_date_start'],0);
        $data['away_date_end']	= sportsmanagementHelper::convertDate($data['away_date_end'],0);

            
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelperson save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        
        //-------extra fields-----------//
        sportsmanagementHelper::saveExtraFields($post,$data['id']);
    
        // Proceed with the save
		return parent::save($data);   
    }
    
    
    
    
	
}
