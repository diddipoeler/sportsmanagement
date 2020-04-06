<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       playground.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage playground
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
 
/**
 * sportsmanagementModelPlayground
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelPlayground extends JSMModelAdmin
{
    
    static $playground = null;
    static $cfg_which_database = 0;
    
    /**
     * sportsmanagementModelplayground::getAddressString()
     * 
     * @return
     */
    function getAddressString()
    {
        $playground = self::getPlayground();
        if (!isset($playground) ) { return null; 
        }
        $address_parts = array();
        if (!empty($playground->address)) {
               $address_parts[] = $playground->address;
        }
        if (!empty($playground->state)) {
               $address_parts[] = $playground->state;
        }
        if (!empty($playground->location)) {
            if (!empty($playground->zipcode)) {
                $address_parts[] = $playground->zipcode. ' ' .$playground->location;
            }
            else
               {
                $address_parts[] = $playground->location;
            }
        }
        if (!empty($playground->country)) {
               $address_parts[] = JSMCountries::getShortCountryName($playground->country);
        }
        $address = implode(', ', $address_parts);
        return $address;
    }
    
    
    
    /**
     * sportsmanagementModelPlayground::getNextGames()
     * 
     * @param  integer $project
     * @param  integer $pgid
     * @param  integer $played
     * @param  integer $allproject
     * @return
     */
    function getNextGames( $project = 0, $pgid = 0, $played = 0, $allproject = 0 )
    {
        $option = Factory::getApplication()->input->getCmd('option');
        $app = Factory::getApplication();
        // Get a db connection.
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        
        $result = array();
        $starttime = microtime(); 
        $d2 = new Datetime("now");

        $playground = self::getPlayground($pgid);
        if ($playground->id > 0 ) {
            $query->select('m.match_date,m.projectteam1_id,m.projectteam2_id,m.team1_result,m.team2_result');
            $query->select('DATE_FORMAT(m.time_present, \'%H:%i\') time_present');
            $query->select('p.name AS project_name');
            $query->select('st1.team_id AS team1');
            $query->select('st2.team_id AS team2');
            $query->from('#__sportsmanagement_match AS m ');
            $query->join('INNER', ' #__sportsmanagement_project_team tj ON tj.id = m.projectteam1_id  ');
            $query->join('INNER', ' #__sportsmanagement_project_team tj2 ON tj2.id = m.projectteam2_id  ');
            $query->join('INNER', ' #__sportsmanagement_project AS p ON p.id = tj.project_id ');
            $query->join('INNER', ' #__sportsmanagement_season_team_id as st1 ON st1.id = tj.team_id ');
            $query->join('INNER', ' #__sportsmanagement_season_team_id as st2 ON st2.id = tj2.team_id ');
            $query->where('m.playground_id = '. (int)$playground->id);
            if ($played ) {
                      $query->where('m.match_timestamp < '.$d2->format('U'));
            }
            else
            {
                      $query->where('m.match_timestamp > '.$d2->format('U'));
            }
            $query->where('m.published = 1');
            $query->where('p.published = 1');

            if ($project && !$allproject ) {
                $query->where('p.id = '. (int)$project);
            }
            
            $query->group('m.id');
            $query->order('match_date ASC');
            
            $db->setQuery($query);
            try{
                      $result = $db->loadObjectList();
                     $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect		
            }
            catch (Exception $e){
                         $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
                $msg = $e->getMessage(); // Returns "Normally you would have other code...
                        $code = $e->getCode(); // Returns '500';
                        $app->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
                         $result = false;
            }
        
        }
        
        return $result;
    }
    
    
    /**
     * sportsmanagementModelPlayground::updateHits()
     * 
     * @param  integer $pgid
     * @param  integer $inserthits
     * @return void
     */
    public static function updateHits($pgid=0,$inserthits=0)
    {
        $option = Factory::getApplication()->input->getCmd('option');
        $app = Factory::getApplication();
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
 
        if ($inserthits ) {
               $query->update($db->quoteName('#__sportsmanagement_playground'))->set('hits = hits + 1')->where('id = '.$pgid);
 
              $db->setQuery($query);
 
              $result = $db->execute();
              $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	 
        }  
     
    }
    
    /**
     * sportsmanagementModelPlayground::getPlayground()
     * 
     * @param  integer $pgid
     * @param  integer $inserthits
     * @return
     */
    public static function getPlayground( $pgid = 0,$inserthits=0 )
    {
        $option = Factory::getApplication()->input->getCmd('option');
        $app = Factory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(true, $app->getUserState("com_sportsmanagement.cfg_which_database", false));
        $query = $db->getQuery(true);
        
        self::updateHits($pgid, $inserthits); 
        
        if (is_null(self::$playground) ) {
            if ($pgid < 1 ) {
                $pgid = Factory::getApplication()->input->getInt("pgid", 0);
            }    
            
            if ($pgid > 0 ) {
                $query->select('*');
                $query->from('#__sportsmanagement_playground');
                $query->where('id = '. $pgid);
                $db->setQuery($query);
                
                self::$playground = $db->loadObject();

            }
        }
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        return self::$playground;
    }
    
    
    
    
}
