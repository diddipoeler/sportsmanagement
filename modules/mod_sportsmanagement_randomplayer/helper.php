<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      helper.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage mod_sportsmanagement_randomplayer
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * modJSMRandomplayerHelper
 * 
 * @package 
 * @author diddi
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class modJSMRandomplayerHelper
{

	/**
	 * Method to get the list
	 *
	 * @access public
	 * @return array
	 */
	public static function getData(&$params)
	{
		$mainframe = JFactory::getApplication();
        $usedp = $params->get('p');
		$usedtid = $params->get('teams', '0');
		$season_id = (int) $params->get('s', '0');
		$usedp = array_map ('intval', $usedp );
//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'projectstring <pre>'.print_r($usedp ,true).'</pre>' ),'Error');		
		$projectstring = (is_array($usedp)) ? implode(",", $usedp)  : (int) $usedp;
		$teamstring = (is_array($usedtid)) ? implode(",", $usedtid) : (int) $usedtid;


//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'projectstring <pre>'.print_r($projectstring ,true).'</pre>' ),'Error');
//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'teamstring <pre>'.print_r($teamstring ,true).'</pre>' ),'Error');

		$db = sportsmanagementHelper::getDBConnection();
        $query = $db->getQuery(true);
        
        $query->select('tt.id');
        $query->from('#__sportsmanagement_project_team tt ');
        $query->where('tt.project_id > 0');
                    
		if( $projectstring != "" && $projectstring > 0 ) 
        {
            $query->where('tt.project_id IN ('.$projectstring.')' );
		}
		if( $teamstring != "" && $teamstring > 0 ) 
        {
            $query->join('INNER',' #__sportsmanagement_season_team_id as st1 ON st1.id = tt.team_id ');
            $query->where('st1.team_id IN ('.$teamstring.')' );
		}

        $query->order('rand()');
        $query->setLimit('1');
        
		$db->setQuery( $query );
		$projectteamid = $db->loadResult();
       
       if ( $params['debug_modus'] )
	{		
        $mainframe->enqueueMessage(JText::_(__FILE__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
	}

       
        $query = $db->getQuery(true);
        
                
        $query->clear();

	$query->select('stp1.person_id');
        $query->select('pt.project_id');
	$query->select('stp1.id');
        $query->from('#__sportsmanagement_season_team_person_id as stp1 ');
        $query->join('INNER',' #__sportsmanagement_season_team_id as st1 ON st1.team_id = stp1.team_id ');
        $query->join('INNER',' #__sportsmanagement_project_team AS pt ON pt.team_id = st1.id ');
        $query->where('pt.id = ' . $projectteamid);
        
        $query->where('stp1.season_id = ' . $season_id);
        $query->where('st1.season_id = ' . $season_id);
        
        $query->order('rand()');
        
        $db->setQuery( $query,0,1 );
	$res = $db->loadRow();
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($params,true).'</pre>' ),'Error');
        
        if ( !$res )
        {
        JError::raiseWarning(0, 'Keine Spieler vorhanden');      
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($db->getErrorMsg(),true).'</pre>' ),'Error');    
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($query->dump(),true).'</pre>' ),'Error');
        }
        else
        {
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($res,true).'</pre>' ),'Error');
        }

if ( $params['debug_modus'] )
	{		
        $mainframe->enqueueMessage(JText::_(__FILE__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
	}
		
		JRequest::setVar( 'p', $res[1] );
		JRequest::setVar( 'pid', $res[0]);
		JRequest::setVar( 'pt', $projectteamid);

		if (!class_exists('sportsmanagementModelPlayer')) {
            require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'player.php');
		}
        if (!class_exists('sportsmanagementModelPerson')) {
            require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'person.php');
		}

		$person 	= sportsmanagementModelPerson::getPerson();
		$project	= sportsmanagementModelProject::getProject();
		$info		= sportsmanagementModelPlayer::getTeamPlayer($res[1],$res[0],$res[2]);
		$infoteam	= sportsmanagementModelProject::getTeaminfo($projectteamid);

		return array('project' => $project,
			'player' => $person, 
			'inprojectinfo'	=> is_array($info) && count($info) ? $info[0] : $info,
			'infoteam' => $infoteam);
      
      $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
      
	}
}
