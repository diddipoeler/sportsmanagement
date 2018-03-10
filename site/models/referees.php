<?php
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model' );


/**
 * sportsmanagementModelReferees
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementModelReferees extends JModelLegacy
{
    
    static $cfg_which_database = 0;
    static $projectid = 0;
    
	/**
	 * sportsmanagementModelReferees::__construct()
	 * 
	 * @return void
	 */
	function __construct()
	{
		
        self::$projectid = JFactory::getApplication()->input->getInt( 'p', 0 );
		sportsmanagementModelProject::$projectid = self::$projectid;
        self::$cfg_which_database = JFactory::getApplication()->input->getInt( 'cfg_which_database', 0 );
		parent::__construct();
	}
    
	/**
	 * sportsmanagementModelReferees::getReferees()
	 * 
	 * @return
	 */
	function getReferees()
	{
		$option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
    $db = sportsmanagementHelper::getDBConnection(TRUE, self::$cfg_which_database );
    $query = $db->getQuery(true);
    $subquery = $db->getQuery(true);
        
        // Select some fields
        $query->select('p.*,p.id AS pid,CONCAT_WS( \':\', p.id, p.alias ) AS slug');
        $query->select('pr.id AS prid, pr.notes AS description');
        $query->select('ppos.position_id');
        $query->select('pos.name AS position,pos.parent_id');
        
        $subquery->select('count(*)');
        $subquery->from('#__sportsmanagement_match AS m');
        $subquery->join('INNER','#__sportsmanagement_round AS r ON m.round_id = r.id');
        $subquery->join('LEFT','#__sportsmanagement_project_team AS ptt1 ON m.projectteam1_id = ptt1.id');
        $subquery->join('LEFT','#__sportsmanagement_project_team AS ptt2 ON m.projectteam2_id = ptt2.id');
        $subquery->join('INNER','#__sportsmanagement_match_referee AS mr ON m.id = mr.match_id');
        $subquery->where('(ptt1.project_id = pr.project_id or ptt2.project_id = pr.project_id)');
        $subquery->where('mr.project_referee_id=pr.id');
        $query->select('('.$subquery.') AS countGames');
        
        $query->from('#__sportsmanagement_project_referee as pr ');
        $query->join('INNER','#__sportsmanagement_season_person_id AS o ON o.id = pr.person_id');
        
        $query->join('INNER','#__sportsmanagement_person as p ON o.person_id = p.id ');
        $query->join('INNER','#__sportsmanagement_project_position as ppos ON ppos.id = pr.project_position_id ');
        $query->join('INNER','#__sportsmanagement_position as pos ON pos.id = ppos.position_id ');
        
        $query->where('pr.project_id = '.self::$projectid);
        $query->where('p.published = 1');
       
        $query->order('pos.ordering');
        $query->order('pos.id');
        
		$db->setQuery( $query );
		return $db->loadObjectList();
	}

}
?>