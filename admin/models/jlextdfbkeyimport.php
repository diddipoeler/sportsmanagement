<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       jlextdfbkeyimport.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

$maxImportTime=480;

if ((int)ini_get('max_execution_time') < $maxImportTime) {@set_time_limit($maxImportTime);
}

/**
 * sportsmanagementModeljlextDfbkeyimport
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModeljlextDfbkeyimport extends JSMModelLegacy
{

    /**
 * sportsmanagementModeljlextDfbkeyimport::_loadData()
 * 
 * @return void
 */
    function _loadData()
    {
  
    }

    /**
 * sportsmanagementModeljlextDfbkeyimport::_initData()
 * 
 * @return void
 */
    function _initData()
    {

    }

    /**
 * sportsmanagementModeljlextDfbkeyimport::getProjectType()
 * 
 * @param  integer $project_id
 * @return
 */
    function getProjectType($project_id = 0)
    {
        $this->jsmquery->clear();
        $this->jsmquery->select('project_type');    
        $this->jsmquery->from('#__sportsmanagement_project');
        $this->jsmquery->where('id = ' . $project_id);    
        try {
            $this->jsmdb->setQuery($this->jsmquery);
            $project_type = $this->jsmdb->loadResult();

            return $project_type;
        } catch (Exception $e) {
            $msg = $e->getMessage(); // Returns "Normally you would have other code...
            $code = $e->getCode(); // Returns '500';
            $this->jsmapp->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
            return false;
        }

    }
    

    /**
 * sportsmanagementModeljlextDfbkeyimport::getCountry()
 * 
 * @param  integer $project_id
 * @return
 */
    function getCountry($project_id = 0)
    {
        $this->jsmquery->clear();
        $this->jsmquery->select('l.country');    
        $this->jsmquery->from('#__sportsmanagement_league as l');
        $this->jsmquery->join('LEFT', '#__sportsmanagement_project as p on p.league_id = l.id');
        $this->jsmquery->where('p.id = ' . $project_id);    
        try {
            $this->jsmdb->setQuery($this->jsmquery);
            $country = $this->jsmdb->loadResult();
            return $country;
        } catch (Exception $e) {
            $msg = $e->getMessage(); // Returns "Normally you would have other code...
            $code = $e->getCode(); // Returns '500';
            $this->jsmapp->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
            return false;
        }

    }



    /**
     * sportsmanagementModeljlextDfbkeyimport::getProjectteams()
     * 
     * @param  integer $project_id
     * @param  integer $division_id
     * @return
     */
    function getProjectteams($project_id = 0, $division_id = 0)
    {
        $this->jsmquery->clear();
          $this->jsmquery->select('pt.id AS value');
            $this->jsmquery->select('t.name AS text,t.notes');
            $this->jsmquery->from('#__sportsmanagement_team AS t');
            $this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
            $this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
        $this->jsmquery->where('pt.project_id = ' . $project_id);

        if ($division_id ) {
            $this->jsmquery->where('pt.division_id = ' . $division_id);    
        }
        
        try {
             $this->jsmdb->setQuery($this->jsmquery);
             $this->jsmdb->execute();
             $number = $this->jsmdb->getNumRows();
             $result = $this->jsmdb->loadObjectList();    
        
            if ($number > 0 ) {
                return $result;
            }
            else
             {
                return false;
            }

        } catch (Exception $e) {
            $msg = $e->getMessage(); // Returns "Normally you would have other code...
            $code = $e->getCode(); // Returns '500';
            $this->jsmapp->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
            return false;
        }    
        
    }

    /**
 * sportsmanagementModeljlextDfbkeyimport::getDFBKey()
 * 
 * @param  mixed $number
 * @param  mixed $matchdays
 * @return
 */
    function getDFBKey($number,$matchdays)
    {
        $project_id = $this->jsmapp->getUserState("$this->jsmoption.pid", '0');
         // gibt es zum land der liga schlüssel ?
        $country = $this->getCountry($project_id);
        $this->jsmquery->clear();

        if ($number % 2 == 0 ) {
        }
        else
          {
            $number = $number + 1;
        }

        $this->jsmquery->select('*');
        $this->jsmquery->from('#__sportsmanagement_dfbkey');
        $this->jsmquery->where('schluessel = ' . (int) $number);
        $this->jsmquery->where('country LIKE '.$this->jsmdb->Quote(''.$country.''));
    
        if ($matchdays == 'ALL' ) {
            $this->jsmquery->group('spieltag');
        }
        elseif ($matchdays == 'FIRST' ) {
            $this->jsmquery->where('spieltag = 1');
        }
    
        try{
            $this->jsmdb->setQuery($this->jsmquery);
              $result = $this->jsmdb->loadObjectList();
    
              return $result;
        } catch (Exception $e) {
              $msg = $e->getMessage(); // Returns "Normally you would have other code...
              $code = $e->getCode(); // Returns '500';
              $this->jsmapp->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
              return false;
        }
    
    }


  
    /**
   * sportsmanagementModeljlextDfbkeyimport::getMatchdays()
   * 
   * @param  integer $project_id
   * @return
   */
    function getMatchdays($project_id = 0)
    {
        $this->jsmquery->clear();
        $this->jsmquery->select('*');
        $this->jsmquery->from('#__sportsmanagement_round');
        $this->jsmquery->where('project_id = ' . (int)$project_id);

        try{
            $this->jsmdb->setQuery($this->jsmquery);
            $result = $this->jsmdb->loadObjectList();
            return $result;
        } catch (Exception $e) {
            $msg = $e->getMessage(); // Returns "Normally you would have other code...
            $code = $e->getCode(); // Returns '500';
            $this->jsmapp->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
            return false;
        }

    }
    
    
    /**
     * sportsmanagementModeljlextDfbkeyimport::getMatches()
     * 
     * @param  integer $project_id
     * @param  integer $division_id
     * @return
     */
    function getMatches($project_id = 0, $division_id = 0)
    {
        $this->jsmquery->clear();
        $this->jsmquery->select('*');
        $this->jsmquery->from('#__sportsmanagement_round');
        $this->jsmquery->where('project_id = ' . (int)$project_id);
    
        try{    
            $this->jsmdb->setQuery($this->jsmquery);
  
            if(version_compare(JVERSION, '3.0.0', 'ge')) {
                // Joomla! 3.0 code here
                $result = $this->jsmdb->loadColumn();
            }
            elseif(version_compare(JVERSION, '2.5.0', 'ge')) {
                // Joomla! 2.5 code here
                $result = $this->jsmdb->loadResultArray();
            }
             $rounds = implode(",", $result);
             $this->jsmquery->clear();
             $this->jsmquery->select('count(*)');
             $this->jsmquery->from('#__sportsmanagement_match');
             $this->jsmquery->where('round_id in (' . $rounds . ')');
             $this->jsmdb->setQuery($this->jsmquery);
             $count = $this->jsmdb->loadResult();
             return $count;

        } catch (Exception $e) {
            $msg = $e->getMessage(); // Returns "Normally you would have other code...
            $code = $e->getCode(); // Returns '500';
            $this->jsmapp->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
            return false;
        }

    }
    
    
    
    
    /**
     * sportsmanagementModeljlextDfbkeyimport::getSchedule()
     * 
     * @param  mixed   $post
     * @param  integer $project_id
     * @param  integer $division_id
     * @return
     */
    function getSchedule($post = array(), $project_id = 0, $division_id = 0 )
    {
        foreach($post as $key => $element)
        {
            if (substr($key, 0, 10)=="chooseteam") {
                $tempteams=explode("_", $key);
                $chooseteam[$tempteams[1]]['projectteamid'] = $element;

                $this->jsmquery->clear();
                $this->jsmquery->select('team.name');
                $this->jsmquery->from('#__sportsmanagement_team as team');
                $this->jsmquery->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = team.id');
                $this->jsmquery->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
                $this->jsmquery->where('pt.id = ' . (int)$element);
                if ($division_id ) {
                    $this->jsmquery->where('pt.division_id = ' . $division_id);    
                }
                  $this->jsmdb->setQuery($this->jsmquery);
                  $chooseteam[$tempteams[1]]['teamname'] = $this->jsmdb->loadResult();
  
            }

        }



        $number = count($chooseteam);

        if ($number % 2 == 0 ) {
        }
        else
        {
            $number = $number + 1;
        }

        $this->jsmquery->clear();
        $this->jsmquery->select('dfb.*,jr.id, jr.round_date_first');
        $this->jsmquery->from('#__sportsmanagement_dfbkey as dfb');
        $this->jsmquery->join('INNER', '#__sportsmanagement_round as jr on dfb.spieltag = jr.roundcode');
        $this->jsmquery->where('dfb.schluessel = ' . (int)$number);
        $this->jsmquery->where('jr.project_id = ' . (int)$project_id);
        $this->jsmquery->order('dfb.spielnummer');
        $this->jsmdb->setQuery($this->jsmquery);
        $dfbresult = $this->jsmdb->loadObjectList();

        $result = array();

        foreach($dfbresult as $row) 
        {

             $teile = explode(",", $row->paarung);

            if ($chooseteam[$teile[0]]['projectteamid'] != 0 && $chooseteam[$teile[1]]['projectteamid'] != 0 ) {
                $temp = new stdClass();
                $temp->spieltag = $row->spieltag;
                $temp->round_id = $row->id;
                $temp->spielnummer = $row->spielnummer;
                $temp->match_date = $row->round_date_first;
                $temp->division_id = $division_id;    
                $temp->projectteam1_id = $chooseteam[$teile[0]]['projectteamid'];
                $temp->projectteam2_id = $chooseteam[$teile[1]]['projectteamid'];
                $temp->projectteam1_name = $chooseteam[$teile[0]]['teamname'];
                $temp->projectteam2_name = $chooseteam[$teile[1]]['teamname'];

                $result[] = $temp;
                $result = array_merge($result);

            }

        }

        $this->savedfb = $result ;
        return $result;
    }
    
    
    
    /**
     * sportsmanagementModeljlextDfbkeyimport::checkTable()
     * 
     * @return void
     */
    function checkTable()
    {
        //$app = Factory::getApplication();
          //$option = Factory::getApplication()->input->getCmd('option');
          include_once JPATH_ADMINISTRATOR.'/components/'.$this->jsmoption.'/'. 'helpers' .DIRECTORY_SEPARATOR. 'jinstallationhelper.php';    
          //$db = sportsmanagementHelper::getDBConnection();
          $db_table = JPATH_ADMINISTRATOR.'/components/'.$this->jsmoption.'/sql/dfbkeys.sql';

        $this->jsmquery->clear();
        $this->jsmquery->select('count(*) AS count');
        $this->jsmquery->from('#__sportsmanagement_dfbkey');

        $this->jsmdb->setQuery($this->jsmquery);
        $result = $this->jsmdb->loadResult();
        
        if (!$result ) {
            $result = JInstallationHelper::populateDatabase($this->jsmdb, $db_table, $errors);    
            
        }
        


    }
  
  
}

?>
