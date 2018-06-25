<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      person.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage person
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

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
    
	}	
  
  /**
   * sportsmanagementModelperson::getAgeGroupID()
   * 
   * @param mixed $age
   * @return
   */
  public function getAgeGroupID($age) 
	{
    
  if ( is_numeric($age) )
  {
$this->jsmquery->clear();
        $this->jsmquery->select('id');
        $this->jsmquery->from('#__sportsmanagement_agegroup ');
        $this->jsmquery->where($age." >= age_from and ".$age." <= age_to");
		try{
        $this->jsmdb->setQuery($this->jsmquery);
		$person_range = $this->jsmdb->loadResult();
        }
        catch (Exception $e)
        {
        $this->jsmapp->enqueueMessage(JText::_($e->getMessage()), 'error');
        return false;
        }    
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
	   $this->jsmquery->clear();
        $this->jsmquery->select('p.*');
        $this->jsmquery->from('#__sportsmanagement_person as p');
        if ( $person_id )
        {
        $this->jsmquery->where('p.id = '.$person_id);
        }
        if ( $season_person_id )
        {
        $this->jsmquery->join('INNER','#__sportsmanagement_season_person_id AS tp on tp.person_id = p.id');
        $this->jsmquery->where('tp.id = '.$season_person_id);
        }

		try{
        $this->jsmdb->setQuery($this->jsmquery);
		return $this->jsmdb->loadObject();
        }
        catch (Exception $e)
        {
        $this->jsmapp->enqueueMessage(JText::_($e->getMessage()), 'error');
        return false;
        }
        
        
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
        $pks = $this->jsmjinput->getVar('cid', null, 'post', 'array');
        $post = $this->jsmjinput->post->getArray(array());
                        
        $result = true;
		for ($x=0; $x < count($pks); $x++)
		{
			$tblPerson = & $this->getTable();
			$tblPerson->id			= $pks[$x];
			$tblPerson->firstname	= $post['firstname'.$pks[$x]];
			$tblPerson->lastname	= $post['lastname'.$pks[$x]];
			$tblPerson->nickname	= $post['nickname'.$pks[$x]];
			$tblPerson->birthday	= sportsmanagementHelper::convertDate($post['birthday'.$pks[$x]],0);
            $tblPerson->deathday	= sportsmanagementHelper::convertDate($post['deathday'.$pks[$x]],0);
            
            $tblPerson->birthday_timestamp = sportsmanagementHelper::getTimestamp($tblPerson->birthday);
            $tblPerson->deathday_timestamp = sportsmanagementHelper::getTimestamp($tblPerson->deathday);
            
			$tblPerson->country		= $post['country'.$pks[$x]];
			$tblPerson->position_id	= $post['position'.$pks[$x]];
            $tblPerson->agegroup_id	= $post['agegroup'.$pks[$x]];
			if(!$tblPerson->store()) 
            {
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
				$result = false;
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
        $date = JFactory::getDate();
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
    
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _team_id<br><pre>'.print_r($this->_team_id,true).'</pre>'),'');    
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _project_team_id<br><pre>'.print_r($this->_project_team_id,true).'</pre>'),'');
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _season_id<br><pre>'.print_r($this->_season_id,true).'</pre>'),'');

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
                
                $mdlTable->modified = $date->toSql();
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
                
                $mdlTable->modified = $date->toSql();
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
         //$query->where('team_id = 0');
         $query->where('season_id = '.$this->_season_id);
         //$query->where('persontype = 3');
         $db->setQuery($query); 
	 $season_person_id = $db->loadResult(); 
          
                $mdlPersonTable->load($cid[$x]);   

$mdlTable = new stdClass();
$mdlTable->id = $season_person_id;
              $mdlTable->person_id = $cid[$x];
                $mdlTable->team_id = 0;
                $mdlTable->season_id = $this->_season_id;
                $mdlTable->modified = $date->toSql();
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
	
}
