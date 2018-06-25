<?php 
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 * @version   1.0.05
 * @file      clubs.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage clubs
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );

/**
 * sportsmanagementModelClubs
 * 
 * @package 
 * @author abcde
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class sportsmanagementModelClubs extends JModelLegacy
{
	static $projectid = 0;
	static $divisionid = 0;
    static $cfg_which_database = 0;

	/**
	 * sportsmanagementModelClubs::__construct()
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

	/**
	 * sportsmanagementModelClubs::getClubs()
	 * 
	 * @param mixed $ordering
	 * @return
	 */
	function getClubs( $ordering = null)
	{
	$option = JFactory::getApplication()->input->getCmd('option');
	   $app = JFactory::getApplication();
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
        $query = $db->getQuery(true);	
                
        $teams = array();
        
        $query->select('c.*, \'\' as teams');
        $query->select('CONCAT_WS( \':\', c.id, c.alias ) AS club_slug');
        $query->from('#__sportsmanagement_club AS c ');
        $query->join('INNER','#__sportsmanagement_team AS t ON t.club_id = c.id ');
        $query->join('INNER','#__sportsmanagement_season_team_id as st ON st.team_id = t.id ');
        $query->join('INNER','#__sportsmanagement_project_team AS pt ON st.id = pt.team_id ');
                  
		if (self::$projectid > 0) {
			$query->where('pt.project_id = '.(int)self::$projectid);
            if (self::$divisionid > 0) {
                $query->where('pt.division_id = '.(int)self::$divisionid);
			}
		}
        
        $query->group('c.id');

		if ( $ordering )
		{
            $query->order($ordering);
		}
		else
		{
            $query->order('c.name');
		}
		$db->setQuery($query);
		if ( ! $clubs = $db->loadObjectList() )
		{
			echo $db->getErrorMsg();
		}
        
        $query->clear();
        
		for ($index = 0; $index < count($clubs); $index++) {
			$teams = array();
            $query->clear();
            $query->select('t.*, t.picture AS team_picture, pt.picture AS projectteam_picture');
        $query->select('CONCAT_WS( \':\', t.id, t.alias ) AS team_slug');
        $query->from('#__sportsmanagement_team AS t ');
        $query->join('INNER','#__sportsmanagement_season_team_id as st ON st.team_id = t.id ');
        $query->join('INNER','#__sportsmanagement_project_team AS pt ON st.id = pt.team_id ');
			
            $query->where('pt.project_id = '.(int)self::$projectid);
            
            if ( self::$divisionid != 0 )
			{
				$query->where('pt.division_id = '.(int)self::$divisionid);
			}
			$query->where('t.club_id = '.$clubs[$index]->id );

			$db->setQuery($query);
			if ( ! $teams = $db->loadObjectList() )
			{
				echo $db->getErrorMsg();
			}
			$clubs[$index]->teams = $teams;
		}
		return $clubs;
	}

}
?>