<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      playground.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage playground
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
/**
 * sportsmanagementModelPlayground
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelPlayground extends JSMModelAdmin
{
    
    static $playground = NULL;
    static $cfg_which_database = 0;
    
    /**
     * sportsmanagementModelplayground::getAddressString()
     * 
     * @return
     */
    function getAddressString()
	{
		$playground = self::getPlayground();
		if ( !isset ( $playground ) ) { return null; }
 		$address_parts = array();
		if (!empty($playground->address))
		{
			$address_parts[] = $playground->address;
		}
		if (!empty($playground->state))
		{
			$address_parts[] = $playground->state;
		}
		if (!empty($playground->location))
		{
			if (!empty($playground->zipcode))
 			{
				$address_parts[] = $playground->zipcode. ' ' .$playground->location;
			}
			else
			{
				$address_parts[] = $playground->location;
			}
		}
		if (!empty($playground->country))
		{
			$address_parts[] = JSMCountries::getShortCountryName($playground->country);
		}
		$address = implode(', ', $address_parts);
		return $address;
	}
    
    
    
    /**
     * sportsmanagementModelPlayground::getNextGames()
     * 
     * @param integer $project
     * @param integer $pgid
     * @param integer $played
     * @param integer $allproject
     * @return
     */
    function getNextGames( $project = 0, $pgid = 0, $played = 0, $allproject = 0 )
    {
        $option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $result = array();
        $starttime = microtime(); 

        $playground = self::getPlayground($pgid);
        if ( $playground->id > 0 )
        {
            $query->select('m.match_date,m.projectteam1_id,m.projectteam2_id,m.team1_result,m.team2_result');
            $query->select('DATE_FORMAT(m.time_present, \'%H:%i\') time_present');
            $query->select('p.name AS project_name');
            $query->select('st1.team_id AS team1');
            $query->select('st2.team_id AS team2');
            $query->from('#__sportsmanagement_match AS m ');
            $query->join('INNER',' #__sportsmanagement_project_team tj ON tj.id = m.projectteam1_id  ');
            $query->join('INNER',' #__sportsmanagement_project_team tj2 ON tj2.id = m.projectteam2_id  ');
            $query->join('INNER',' #__sportsmanagement_project AS p ON p.id = tj.project_id ');
            $query->join('INNER',' #__sportsmanagement_season_team_id as st1 ON st1.id = tj.team_id ');
            $query->join('INNER',' #__sportsmanagement_season_team_id as st2 ON st2.id = tj2.team_id ');
            $query->where('m.playground_id = '. (int)$playground->id);
		if ( $played )
		{
		$query->where('m.match_date < NOW()');
		}
		else
		{
            $query->where('m.match_date > NOW()');
		}
            $query->where('m.published = 1');
            $query->where('p.published = 1');

            if ( $project && !$allproject )
            {
                $query->where('p.id = '. (int)$project);
            }
            
            $query->group('m.id');
            $query->order('match_date ASC');
            
            $db->setQuery( $query );
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
     * @param integer $pgid
     * @param integer $inserthits
     * @return void
     */
    public static function updateHits($pgid=0,$inserthits=0)
    {
        $option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
    $db = JFactory::getDbo();
 $query = $db->getQuery(true);
 
 if ( $inserthits )
 {
 $query->update($db->quoteName('#__sportsmanagement_playground'))->set('hits = hits + 1')->where('id = '.$pgid);
 
$db->setQuery($query);
 
$result = $db->execute();
$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect	 
}  
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');     
    }
    
    /**
     * sportsmanagementModelPlayground::getPlayground()
     * 
     * @param integer $pgid
     * @param integer $inserthits
     * @return
     */
    public static function getPlayground( $pgid = 0,$inserthits=0 )
    {
        $option = JFactory::getApplication()->input->getCmd('option');
	    $app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection(TRUE, $app->getUserState( "com_sportsmanagement.cfg_which_database", FALSE ) );
        $query = $db->getQuery(true);
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $my_text = 'playground <br><pre>'.print_r(self::$playground,true).'</pre>'; 
        
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);
        }
        /*
        if ( JComponentHelper::getParams($option)->get('show_debug_info_frontend') )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' playground<br><pre>'.print_r(self::$playground,true).'</pre>'),'');    
        }
        */
        self::updateHits($pgid,$inserthits); 
        
        if ( is_null( self::$playground ) )
        {
            if ( $pgid < 1 )
            {
            $pgid = JFactory::getApplication()->input->getInt( "pgid", 0 );
            }    
            
            if ( $pgid > 0 )
            {
                $query->select('*');
                $query->from('#__sportsmanagement_playground');
                $query->where('id = '. $pgid);
                $db->setQuery( $query );
                
                if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
            $my_text = 'query <br><pre>'.print_r($query->dump(),true).'</pre>'; 
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);
                }
                
                self::$playground = $db->loadObject();

            }
        }
        $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
        return self::$playground;
    }
    
    
    
    
}
