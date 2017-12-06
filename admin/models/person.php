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
 * sportsmanagementModelperson
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelperson extends JSMModelAdmin
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
   * sportsmanagementModelperson::getAgeGroupID()
   * 
   * @param mixed $age
   * @return
   */
  public function getAgeGroupID($age) 
	{
  // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
  
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
		
    //$app->enqueueMessage('getAgeGroupID<br><pre>'.print_r($query, true).'</pre><br>','Notice');
		
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
	 * sportsmanagementModelperson::getPerson()
	 * 
	 * @param integer $person_id
	 * @param integer $season_person_id
	 * @param integer $inserthits
	 * @return
	 */
	function getPerson($person_id=0,$season_person_id=0,$inserthits=0)
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
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
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db = JFactory::getDbo();
        // Get the input
        $pks = $jinput->getVar('cid', null, 'post', 'array');
        $post = JFactory::getApplication()->input->get('post');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
//        $app->enqueueMessage(get_class($this).' '.__FUNCTION__.'pks<pre>'.print_r($pks, true).'</pre><br>','Notice');
//        $app->enqueueMessage(get_class($this).' '.__FUNCTION__.'post<pre>'.print_r($post, true).'</pre><br>','Notice');
        $my_text = 'pks <pre>'.print_r($pks,true).'</pre>';    
        $my_text .= 'post <pre>'.print_r($post,true).'</pre>';
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        }
        
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
            $tblPerson->agegroup_id	= $post['agegroup'.$pks[$x]];
			if(!$tblPerson->store()) 
            {
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
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
    // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db = JFactory::getDbo();
        //$date = JFactory::getDate();
	   $user = JFactory::getUser();
       $modified = JFactory::getDate();
	   $modified_by = $user->get('id');
       
    $this->_project_id	= $app->getUserState( "$option.pid", '0' );
    $this->_team_id = $app->getUserState( "$option.team_id", '0' );
    $this->_project_team_id = $app->getUserState( "$option.project_team_id", '0' );
    $this->_season_id = $app->getUserState( "$option.season_id", '0' );
    $cid = $post['cid'];
          
    $mdlPerson = JModelLegacy::getInstance("person", "sportsmanagementModel");
    $mdlPersonTable = $mdlPerson->getTable();
    
    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _team_id<br><pre>'.print_r($this->_team_id,true).'</pre>'),'');    
    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _project_team_id<br><pre>'.print_r($this->_project_team_id,true).'</pre>'),'');
    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _season_id<br><pre>'.print_r($this->_season_id,true).'</pre>'),'');

    switch ($post['persontype'])
            {
                /**
                 * spieler
                 */
                case 1:
                $mdl = JModelLegacy::getInstance("seasonteamperson", "sportsmanagementModel");
                $mdlTable = $mdl->getTable();
                for ($x=0; $x < count($cid); $x++)
                {
                $mdlPersonTable->load($cid[$x]);    
                $mdlTable = $mdl->getTable();
                $mdlTable->team_id = $this->_team_id;
                $mdlTable->season_id = $this->_season_id;
                $mdlTable->persontype = 1;
                
                $mdlTable->modified = $db->Quote(''.$modified.'');
                $mdlTable->modified_by = $modified_by;
                
                $mdlTable->picture = $mdlPersonTable->picture;
                $mdlTable->active = 1;
                $mdlTable->published = 1;
                $mdlTable->person_id = $cid[$x]; 
			try{
                if ( $mdlTable->store() === false )
				{
				    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
				}
				else
				{
				}
} catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
}				
                // projekt position eintragen
                // zuerst die positions id zum projekt ermitteln.
                $this->jsmquery->clear();  
                $this->jsmquery->select('id');
                $this->jsmquery->from('#__sportsmanagement_project_position');
                $this->jsmquery->where('project_id = '. $this->_project_id );
                $this->jsmquery->where('position_id ='. $mdlPersonTable->position_id );
                $this->jsmdb->setQuery($this->jsmquery);
			try{
		        $res = $this->jsmdb->loadResult();
                } catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
}
                 if ( !$res )
                {
                $res = 0;
                }

			// Create a new query object.
                $insertquery = $db->getQuery(true);
                // Insert columns.
                $columns = array('person_id','project_id','project_position_id','persontype','modified','modified_by');
                // Insert values.
                $values = array($cid[$x],$this->_project_id,$res,1,$db->Quote(''.$modified.''),$modified_by);
                // Prepare the insert query.
                $insertquery
                ->insert($db->quoteName('#__sportsmanagement_person_project_position'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
                // Set the query using our newly populated query object and execute it.
                $db->setQuery($insertquery);
                try {
                sportsmanagementModeldatabasetool::runJoomlaQuery();
                }
                catch (Exception $e){
                $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error   
                }
                 
		        }
                break;
                /**
                 * trainer
                 */
                case 2:
                $mdl = JModelLegacy::getInstance("seasonteamperson", "sportsmanagementModel");
                $mdlTable = $mdl->getTable();
                for ($x=0; $x < count($cid); $x++)
                {
                $mdlPersonTable->load($cid[$x]); 
                $mdlTable = $mdl->getTable();
                $mdlTable->team_id = $this->_team_id;
                $mdlTable->season_id = $this->_season_id;
                $mdlTable->persontype = 2;
                
                $mdlTable->modified = $db->Quote(''.$modified.'');
                $mdlTable->modified_by = $modified_by;
                
                $mdlTable->picture = $mdlPersonTable->picture;
                $mdlTable->active = 1;
                $mdlTable->published = 1;
                $mdlTable->person_id = $cid[$x];  
			try{
                if ( $mdlTable->store() === false )
				{
				    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
				}
				else
				{
				}
} catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
}			
                // projekt position eintragen
                // zuerst die positions id zum projekt ermitteln.
                $this->jsmquery->clear();  
                $this->jsmquery->select('id');
                $this->jsmquery->from('#__sportsmanagement_project_position');
                $this->jsmquery->where('project_id = '. $this->_project_id );
                $this->jsmquery->where('position_id ='. $mdlPersonTable->position_id );
                $this->jsmdb->setQuery($this->jsmquery);
		 try{
			$res = $this->jsmdb->loadResult();
			}
                catch (Exception $e){
                $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error   
                }
			
		 if ( !$res )
                {
                $res = 0;
                }

                // Create a new query object.
                $insertquery = $db->getQuery(true);
                // Insert columns.
                $columns = array('person_id','project_id','project_position_id','persontype','modified','modified_by');
                // Insert values.
                $values = array($cid[$x],$this->_project_id,$res,2,$db->Quote(''.$modified.''),$modified_by);
                // Prepare the insert query.
                $insertquery
                ->insert($db->quoteName('#__sportsmanagement_person_project_position'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
                // Set the query using our newly populated query object and execute it.
                $db->setQuery($insertquery);
                try {
                sportsmanagementModeldatabasetool::runJoomlaQuery();
                }
                catch (Exception $e){
                $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error   
                }
                 
		        }
                break;
                /**
                 * schiedsrichter
                 */                
                case 3:
                $mdl = JModelLegacy::getInstance("seasonperson", "sportsmanagementModel");
                $mdlTable = $mdl->getTable();
                for ($x=0; $x < count($cid); $x++)
                {
                $query = $db->getQuery(true);
                $query->select('id'); 
         $query->from('#__sportsmanagement_season_person_id'); 
         $query->where('person_id = '.$cid[$x]); 
         $query->where('team_id = 0');
         $query->where('season_id = '.$this->_season_id);
         $query->where('persontype = 3');
         $db->setQuery($query); 
	 $season_person_id = $db->loadResult(); 
          
                $mdlPersonTable->load($cid[$x]);   

$mdlTable = new stdClass();
$mdlTable->id = $season_person_id;
              $mdlTable->person_id = $cid[$x];
                $mdlTable->team_id = 0;
                $mdlTable->season_id = $this->_season_id;
                $mdlTable->modified = $db->Quote(''.$modified.'');
                $mdlTable->modified_by = $modified_by;
                $mdlTable->picture = $mdlPersonTable->picture;
                $mdlTable->persontype = 3;
                $mdlTable->published = 1;

try {
    $result = $db->insertObject('#__sportsmanagement_season_person_id', $mdlTable);
    $season_person_id = $db->insertid();
}
catch (Exception $e){
    $result = $db->updateObject('#__sportsmanagement_season_person_id', $mdlTable, 'id');
}





                 
//                $mdlTable = $mdl->getTable();
  
                   
                //if ($mdlTable->store()===false)
//				{
//				    sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
//				}
//				else
//				{
				// Create and populate an object.
                $profile = new stdClass();
                $profile->project_id = $this->_project_id;
                $profile->person_id = $season_person_id;
                $profile->published = 1;
                $profile->modified = $db->Quote(''.$modified.'');
                $profile->modified_by = $modified_by;
 try{
			// Insert the object into the user profile table.
                $result = JFactory::getDbo()->insertObject('#__sportsmanagement_project_referee', $profile);
			}
catch (Exception $e){
    $result = $db->updateObject('#__sportsmanagement_season_person_id', $mdlTable, 'id');
}
               // }
                
                }
                break;
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
	public function savenichtmehr($data)
	{
	   // Reference global application object
 //       $app = JFactory::getApplication();
//        // JInput object
//        $jinput = $app->input;
//        $option = $jinput->getCmd('option');
      // $date = JFactory::getDate();
	 //  $user = JFactory::getUser();
       $post = $this->jsmjinput->post->getArray();
       // Set the values
	   $data['modified'] = $this->jsmdate->toSql();
	   $data['modified_by'] = $this->jsmuser->get('id');
       
       $address_parts = array();
       $person_double = array();
       // Create a new query object.
       // $query = JFactory::getDBO()->getQuery(true);
        
       
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
        
       // hat der user die bildfelder geleert, werden die standards gesichert.
       if ( empty($data['picture']) )
       {
       $data['picture'] = JComponentHelper::getParams($this->jsmoption)->get('ph_player','');
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
        
        $data['birthday'] = sportsmanagementHelper::convertDate($data['birthday'],0);
        $data['deathday'] = sportsmanagementHelper::convertDate($data['deathday'],0);
        $data['injury_date_start'] = sportsmanagementHelper::convertDate($data['injury_date_start'],0);
        $data['injury_date_end'] = sportsmanagementHelper::convertDate($data['injury_date_end'],0);
        $data['susp_date_start'] = sportsmanagementHelper::convertDate($data['susp_date_start'],0);
        $data['susp_date_end'] = sportsmanagementHelper::convertDate($data['susp_date_end'],0);
        $data['away_date_start'] = sportsmanagementHelper::convertDate($data['away_date_start'],0);
        $data['away_date_end'] = sportsmanagementHelper::convertDate($data['away_date_end'],0);
       
       
       // Alter the title for Save as Copy
		if ($this->jsmjinput->get('task') == 'save2copy')
		{
			$orig_table = $this->getTable();
			$orig_table->load((int) $this->jsmjinput->getInt('id'));
            $data['id'] = 0;

			if ($data['lastname'] == $orig_table->lastname)
			{
				$data['lastname'] .= ' ' . JText::_('JGLOBAL_COPY');
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
                $this->jsmapp->enqueueMessage(JText::plural(strtoupper($this->jsmoption) . '_N_ITEMS_CREATED', $id),'');
            }
           
		}

        if (isset($data['season_ids']) && is_array($data['season_ids'])) 
		{
		  foreach( $data['season_ids'] as $key => $value )
          {
          
        $this->jsmquery->clear();  
        $this->jsmquery->select('id');
        $this->jsmquery->from('#__sportsmanagement_season_person_id');
        $this->jsmquery->where('person_id ='. $data['id'] );
        $this->jsmquery->where('season_id ='. $value );
        $this->jsmdb->setQuery($this->jsmquery);
		$res = $this->jsmdb->loadResult();
        
        if ( !$res )
        {
        $this->jsmquery->clear();
        // Insert columns.
        $columns = array('person_id','season_id');
        // Insert values.
        $values = array($data['id'],$value);
        // Prepare the insert query.
        $this->jsmquery
            ->insert($this->jsmdb->quoteName('#__sportsmanagement_season_person_id'))
            ->columns($this->jsmdb->quoteName($columns))
            ->values(implode(',', $values));
        // Set the query using our newly populated query object and execute it.
        $this->jsmdb->setQuery($this->jsmquery);

		if (!sportsmanagementModeldatabasetool::runJoomlaQuery())
		{
        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->jsmdb->getErrorMsg(),true).'</pre>'),'Error');
		}  
        
        }
          
          }

		}
        
        //-------extra fields-----------//
        sportsmanagementHelper::saveExtraFields($post,$data['id']);

        return true;
           
    }
    
    
    
    
	
}
