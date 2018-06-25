<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      ical.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage ical
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );


/**
 * sportsmanagementModelical
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2018
 * @version $Id$
 * @access public
 */
class sportsmanagementModelical extends JModelLegacy
{
	static $projectid = 0;
	static $divisionid = 0;
    static $cfg_which_database = 0;
	static $teamid = 0;
    static $projectteamid = 0;
	var $team = null;
	var $club = null;

	
	/**
	 * sportsmanagementModelical::__construct()
	 * 
	 * @return void
	 */
	function __construct( )
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        $jinput = $app->input;
		parent::__construct( );
        
        self::$teamid = (int) $jinput->get('tid',0, '');
        self::$projectteamid = (int) $jinput->get('ptid',0, '');
        
        self::$projectid = $jinput->request->get('p', 0, 'INT');
		self::$divisionid = $jinput->request->get('division', 0, 'INT');
        self::$cfg_which_database = $jinput->request->get('cfg_which_database',0, 'INT');
        sportsmanagementModelProject::$projectid = self::$projectid;
        sportsmanagementModelResults::$projectid = self::$projectid;  
        sportsmanagementModelNextMatch::$projectid = self::$projectid; 

	}

	function getResultsPlan($projectid = 0, $teamid = 0, $divisionid = 0, $playgroundid = 0, $ordering = 'ASC',$cfg_which_database = 0) 
 	{ 

	}	
	
	
}
?>
