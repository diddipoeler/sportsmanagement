<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       rivals.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage rivals
 */


defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementModelRivals
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2016
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelRivals extends BaseDatabaseModel
{
	var $project = null;

	var $projectid = 0;

	var $teamid = 0;

	var $team = null;

	static $cfg_which_database = 0;

	/**
	 * sportsmanagementModelRivals::__construct()
	 *
	 * @return void
	 */
	function __construct( )
	{
		  $app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;

			  self::$cfg_which_database = (int) $jinput->get('cfg_which_database', 0, '');

			  parent::__construct();

		$this->projectid = $jinput->getInt("p", 0);
		$this->teamid = $jinput->getInt("tid", 0);
		$this->getTeam();
	}

	/**
	 * sportsmanagementModelRivals::getTeam()
	 *
	 * @return
	 */
	function getTeam( )
	{
		if (!isset($this->team))
		{
			if ($this->teamid > 0)
			{
				$this->team = $this->getTable('Team', 'sportsmanagementTable');
				$this->team->load($this->teamid);
			}
		}

		return $this->team;
	}



	/**
	 * sportsmanagementModelRivals::getOpponents()
	 *
	 * @return
	 */
	function getOpponents()
	{
		  $app = Factory::getApplication();

		  // JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		  // Create a new query object.
		$db = sportsmanagementHelper::getDBConnection(true, self::$cfg_which_database);
		$query = $db->getQuery(true);

			  $query = $db->getQuery(true);
		$query->clear();
		$query->select(
			'm.id , m.projectteam1_id , m.projectteam2_id
		,pt1.project_id
		, pt1.team_id AS pteam1_id
		, pt2.team_id AS pteam2_id
        , t1.id AS team1_id
		, t2.id AS team2_id
		, pt1.division_id AS division_id
		, m.team1_result
		, m.team2_result
		, m.alt_decision
		, m.team1_result_decision
		, m.team2_result_decision
		, t1.short_name AS short_name1
		, t2.short_name AS short_name2
		, t1.middle_name AS middle_name1
		, t2.middle_name AS middle_name2
		, t1.name AS name1
		, t2.name AS name2
        ,CONCAT_WS(\':\',pt1.id,t1.alias) AS projectteam1_slug
        ,CONCAT_WS(\':\',pt2.id,t2.alias) AS projectteam2_slug
      
        ,t1.picture as teampicture1
        ,t2.picture as teampicture2
        ,pt1.picture as pteampicture1
        ,pt2.picture as pteampicture2
      
        ,c1.logo_small as logo_small1
        ,c2.logo_small as logo_small2
      
        ,c1.country as country1
        ,c2.country as country2
      
        ,c1.logo_middle as logo_middle1
        ,c2.logo_middle as logo_middle2
      
        ,c1.logo_big as logo_big1
        ,c2.logo_big as logo_big2
      
		, t1.club_id AS club1_id
		, t2.club_id AS club2_id'
		);
		   $query->from('#__sportsmanagement_match AS m');
		   $query->join('INNER', '#__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id');
		   $query->join('INNER', '#__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id');

				 $query->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id ');
		   $query->join('INNER', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id ');

				 $query->join('INNER', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
		   $query->join('INNER', '#__sportsmanagement_club AS c1 ON c1.id = t1.club_id');

				 $query->join('INNER', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');
		   $query->join('INNER', '#__sportsmanagement_club AS c2 ON c2.id = t2.club_id');

				 $query->where('m.published = 1');

				 // $query->where('( (m.projectteam1_id = ' .$this->teamid. ' )'.' OR (m.projectteam2_id = ' .$this->teamid. ' ) )');
		   $query->where('( (t1.id = ' . $this->teamid . ' )' . ' OR (t2.id = ' . $this->teamid . ' ) )');
		   $query->where('(m.team1_result IS NOT NULL OR m.alt_decision > 0)');
		   $query->where('(m.cancel IS NULL OR m.cancel = 0)');

				 $query->where('pt1.project_id = ' . $this->projectid);
		   $query->where('pt2.project_id = ' . $this->projectid);

				 $query->where('(m.cancel IS NULL OR m.cancel = 0)');

				 $query->order('m.id');

			 $db->setQuery($query);
		$matches = $db->loadObjectList();
		   $db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

			  $opo = array();

		foreach ($matches as $match)
		{
			if (!isset($opo[$match->team2_id]))
			{
				$opo[$match->team2_id] = array('match' => 0, 'name' => '', 'g_for' => 0, 'g_aga' => 0, 'win' => 0, 'tie' => 0, 'los' => 0);
			}

			if (!isset($opo[$match->team1_id]))
			{
				 $opo[$match->team1_id] = array('match' => 0, 'name' => '', 'g_for' => 0, 'g_aga' => 0, 'win' => 0, 'tie' => 0, 'los' => 0);
			}

			if ($match->team1_id == $this->teamid)
			{
				 $opo[$match->team2_id]['projectteamid']    = $match->projectteam2_id;
						  $opo[$match->team2_id]['projectteam_slug']    = $match->projectteam2_slug;
				 $opo[$match->team2_id]['project_id']    = $match->project_id;
				 $opo[$match->team2_id]['division_id']    = $match->division_id;
				 $opo[$match->team2_id]['match']            += 1;
				 $opo[$match->team2_id]['id']            = $match->team2_id;
				 $opo[$match->team2_id]['team_id']        = $match->team2_id;
				 $opo[$match->team2_id]['club_id']        = $match->club2_id;
				 $opo[$match->team2_id]['name']            = $match->name2;
				 $opo[$match->team2_id]['short_name']    = $match->short_name2;
				 $opo[$match->team2_id]['middle_name']    = $match->middle_name2;
				 $opo[$match->team2_id]['g_for']            += $match->team1_result;
				 $opo[$match->team2_id]['g_aga']            += $match->team2_result;

										$opo[$match->team2_id]['logo_small']            = $match->logo_small2;
						  $opo[$match->team2_id]['logo_middle']            = $match->logo_middle2;
						  $opo[$match->team2_id]['logo_big']            = $match->logo_big2;
						  $opo[$match->team2_id]['country_flag']            = $match->country2;
						  $opo[$match->team2_id]['team_picture']            = $match->teampicture2;
						  $opo[$match->team2_id]['projectteam_picture']            = $match->pteampicture2;

				if (!$match->alt_decision)
				{
					if ($match->team1_result > $match->team2_result)
					{
						$opo[$match->team2_id]['win'] += 1;
					}
					elseif ($match->team1_result < $match->team2_result)
					{
						$opo[$match->team2_id]['los'] += 1;
					}
					else
					{
						$opo[$match->team2_id]['tie'] += 1;
					}
				}
				else
				{
					if (empty($match->team1_result_decision))
					{
						$opo[$match->team2_id]['forfeit'] += 1;
					}
					else
					{
						if ($match->team1_result_decision > $match->team2_result_decision)
						{
							$opo[$match->team2_id]['win'] += 1;
						}
						elseif ($match->team1_result_decision < $match->team2_result_decision)
						{
							$opo[$match->team2_id]['los'] += 1;
						}
						else
						{
							$opo[$match->team2_id]['tie'] += 1;
						}
					}
				}
			}
			else
			{
				 $opo[$match->team1_id]['projectteamid']    = $match->projectteam1_id;
					   $opo[$match->team1_id]['projectteam_slug']    = $match->projectteam1_slug;
				 $opo[$match->team1_id]['project_id']    = $match->project_id;
				 $opo[$match->team1_id]['division_id']    = $match->division_id;
				 $opo[$match->team1_id]['match']            += 1;
				 $opo[$match->team1_id]['id']            = $match->team1_id;
				 $opo[$match->team1_id]['team_id']        = $match->team1_id;
				 $opo[$match->team1_id]['club_id']        = $match->club1_id;
				 $opo[$match->team1_id]['name']            = $match->name1;
				 $opo[$match->team1_id]['short_name']    = $match->short_name1;
				 $opo[$match->team1_id]['middle_name']    = $match->middle_name1;
				 $opo[$match->team1_id]['g_for']            += $match->team2_result;
				 $opo[$match->team1_id]['g_aga']            += $match->team1_result;

							   $opo[$match->team1_id]['logo_small']            = $match->logo_small1;
						  $opo[$match->team1_id]['logo_middle']            = $match->logo_middle1;
						  $opo[$match->team1_id]['logo_big']            = $match->logo_big1;

										$opo[$match->team1_id]['country_flag']            = $match->country1;
						  $opo[$match->team1_id]['team_picture']            = $match->teampicture1;
						  $opo[$match->team1_id]['projectteam_picture']            = $match->pteampicture1;

				if (!$match->alt_decision)
				{
					if ($match->team1_result > $match->team2_result)
					{
						$opo[$match->team1_id]['los'] += 1;
					}
					elseif ($match->team1_result < $match->team2_result)
					{
						$opo[$match->team1_id]['win'] += 1;
					}
					else
					{
						$opo[$match->team1_id]['tie'] += 1;
					}
				}
				else
				{
					if (empty($match->team2_result_decision))
					{
						$opo[$match->team1_id]['forfeit'] += 1;
					}
					else
					{
						if ($match->team1_result_decision > $match->team2_result_decision)
						{
							$opo[$match->team1_id]['los'] += 1;
						}
						elseif ($match->team1_result_decision < $match->team2_result_decision)
						{
							$opo[$match->team1_id]['win'] += 1;
						}
						else
						{
							$opo[$match->team1_id]['tie'] += 1;
						}
					}
				}
			}
		}

		function array_csort()
		{
			$i = 0;
			$args = func_get_args();
			$marray = array_shift($args);
			$msortline = 'return(array_multisort(';

			foreach ($args as $arg)
			{
				$i++;

				if (is_string($arg))
				{
					foreach ($marray as $row)
					{
						$sortarr[$i][] = $row[$arg];
					}
				}
				else
				{
					$sortarr[$i] = $arg;
				}

				$msortline .= '$sortarr[' . $i . '],';
			}

			$msortline .= '$marray));';
			eval($msortline);

			return $marray;
		}
			$sorted = array();

		if (count($opo))
		{
				 $sorted = array_csort($opo, 'match', SORT_DESC, 'win', SORT_DESC, 'g_for', SORT_DESC);
		}

			return $sorted;

	}
}
