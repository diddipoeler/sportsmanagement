<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      teams.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage teams
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );

/**
 * sportsmanagementModelTeams
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementModelTeams extends JModelLegacy
{
	static $projectid = 0;
	static $divisionid = 0;
    static $cfg_which_database = 0;
	var $teamid = 0;
	var $team = null;
	var $club = null;

	/**
	 * sportsmanagementModelTeams::__construct()
	 * 
	 * @return void
	 */
	function __construct( )
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        $jinput = $app->input;
		parent::__construct( );
        
self::$projectid = $jinput->request->get('p', 0, 'INT');
		self::$divisionid = $jinput->request->get('division', 0, 'INT');
        self::$cfg_which_database = $jinput->request->get('cfg_which_database',0, 'INT');
        sportsmanagementModelProject::$projectid = self::$projectid; 


	}

}
?>