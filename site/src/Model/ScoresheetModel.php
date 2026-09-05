<?php
/**
 * Joomla 5/6 scoresheet site model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\DatabaseInterface;

final class ScoresheetModel extends SportsManagementProjectModel
{
    public static int $cfg_which_database = 0;
    public static int $matchid = 0;
    public static int $projectid = 0;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        self::$matchid = $input->getInt('mid', 0);
        self::$cfg_which_database = $input->getInt('cfg_which_database', 0);
        self::$projectid = $this->projectId;

        if (class_exists('sportsmanagementModelProject')) {
            \sportsmanagementModelProject::$projectid = self::$projectid;
        }
    }

    public function getMatch($matchid = 0, $cfg_which_database = 0)
    {
        $matchId = max(0, (int) $matchid);
        $databaseSelector = max(0, (int) $cfg_which_database);

        if ($matchId <= 0) {
            return [];
        }

        $app = $this->siteApplication();

        try {
            $db = $this->database($databaseSelector);
            $project = $this->getProject();

            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('m.match_number', 'match_number'),
                    $db->quoteName('m.match_date', 'match_date'),
                    $db->quoteName('m.projectteam1_id', 'projectteam1_id'),
                    $db->quoteName('m.projectteam2_id', 'projectteam2_id'),
                    $db->quoteName('x.game_parts', 'game_parts'),
                    $db->quoteName('x.season_id', 'season_id'),
                    $db->quoteName('s1.team_id', 'team1_id'),
                    $db->quoteName('t1.name', 'team1_name'),
                    $db->quoteName('s2.team_id', 'team2_id'),
                    $db->quoteName('t2.name', 'team2_name'),
                    $db->quoteName('j.name', 'projectname'),
                    $db->quoteName('j.timezone', 'timezone'),
                    $db->quoteName('g.name', 'playgroundname'),
                ])
                ->from($db->quoteName('#__sportsmanagement_match', 'm'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'p1') . ' ON ' . $db->quoteName('m.projectteam1_id') . ' = ' . $db->quoteName('p1.id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 's1') . ' ON ' . $db->quoteName('p1.team_id') . ' = ' . $db->quoteName('s1.id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('s1.team_id') . ' = ' . $db->quoteName('t1.id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'x') . ' ON ' . $db->quoteName('p1.project_id') . ' = ' . $db->quoteName('x.id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'p2') . ' ON ' . $db->quoteName('m.projectteam2_id') . ' = ' . $db->quoteName('p2.id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 's2') . ' ON ' . $db->quoteName('p2.team_id') . ' = ' . $db->quoteName('s2.id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('s2.team_id') . ' = ' . $db->quoteName('t2.id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'j') . ' ON ' . $db->quoteName('p1.project_id') . ' = ' . $db->quoteName('j.id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_playground', 'g') . ' ON ' . $db->quoteName('m.playground_id') . ' = ' . $db->quoteName('g.id'));

            if (!empty($project->teams_as_referees)) {
                $query->select($db->quoteName('u.name', 'referee'))
                    ->join('LEFT', $db->quoteName('#__sportsmanagement_match_referee', 'r') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('r.match_id'))
                    ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'spi') . ' ON ' . $db->quoteName('r.project_referee_id') . ' = ' . $db->quoteName('spi.id'))
                    ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('spi.team_id'))
                    ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 'u') . ' ON ' . $db->quoteName('st1.team_id') . ' = ' . $db->quoteName('u.id') . ' AND ' . $db->quoteName('u.published') . ' = 1');
            } else {
                $query->select($db->quoteName('u.lastname', 'referee'))
                    ->join('LEFT', $db->quoteName('#__sportsmanagement_match_referee', 'r') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('r.match_id'))
                    ->join('LEFT', $db->quoteName('#__sportsmanagement_project_referee', 's') . ' ON ' . $db->quoteName('r.project_referee_id') . ' = ' . $db->quoteName('s.id'))
                    ->join('LEFT', $db->quoteName('#__sportsmanagement_person', 'u') . ' ON ' . $db->quoteName('s.person_id') . ' = ' . $db->quoteName('u.id'));
            }

            $query->where($db->quoteName('m.id') . ' = ' . $matchId);
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $app->enqueueMessage(Text::_(__METHOD__ . ' ' . $e->getMessage()), 'error');
            return false;
        }
    }

    public function getTeamPlayer($teamid = 0, $seasonid = 0, $cfg_which_database = 0)
    {
        $teamId = max(0, (int) $teamid);
        $seasonId = max(0, (int) $seasonid);
        $databaseSelector = max(0, (int) $cfg_which_database);

        if ($teamId <= 0 || $seasonId <= 0) {
            return [];
        }

        $app = $this->siteApplication();

        try {
            $db = $this->database($databaseSelector);
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('b.firstname'),
                    $db->quoteName('b.lastname'),
                    $db->quoteName('b.knvbnr'),
                ])
                ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'a'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'b') . ' ON ' . $db->quoteName('a.person_id') . ' = ' . $db->quoteName('b.id'))
                ->where($db->quoteName('a.team_id') . ' = ' . $teamId)
                ->where($db->quoteName('a.season_id') . ' = ' . $seasonId)
                ->order($db->quoteName('b.lastname') . ' ASC');
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $app->enqueueMessage(Text::_(__METHOD__ . ' ' . $e->getMessage()), 'error');
            return false;
        }
    }

    private function database(int $selector): DatabaseInterface
    {
        if ($selector === self::$cfg_which_database) {
            return $this->getDatabase();
        }

        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class);

        return SportsManagementDatabaseResolver::resolve($joomlaDatabase, $selector);
    }
}
