<?php
/**
 * Joomla 5/6 iCalendar site model.
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

final class IcalModel extends SportsManagementProjectModel
{
    public static int $projectid = 0;
    public static int $divisionid = 0;
    public static int $cfg_which_database = 0;
    public static int $teamid = 0;
    public static int $projectteamid = 0;

    public ?object $team = null;
    public ?object $club = null;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        self::$projectid = $this->projectId;
        self::$divisionid = $this->divisionId;
        self::$cfg_which_database = $input->getInt('cfg_which_database', 0);
        self::$teamid = $input->getInt('tid', 0);
        self::$projectteamid = $input->getInt('ptid', 0);

        if (class_exists('sportsmanagementModelProject')) {
            \sportsmanagementModelProject::$projectid = self::$projectid;
        }
    }

    public function getResultsPlan($projectid = 0, $teamid = 0, $divisionid = 0, $playgroundid = 0, $ordering = 'ASC', $cfg_which_database = 0)
    {
        $projectId = max(0, (int) $projectid);
        $teamId = max(0, (int) $teamid);
        $databaseSelector = max(0, (int) $cfg_which_database);
        $direction = strtoupper((string) $ordering) === 'DESC' ? 'DESC' : 'ASC';

        if ($projectId <= 0) {
            return [];
        }

        $app = $this->siteApplication();

        try {
            if ($databaseSelector === self::$cfg_which_database) {
                $db = $this->getDatabase();
            } else {
                /** @var DatabaseInterface $joomlaDatabase */
                $joomlaDatabase = \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class);
                $db = SportsManagementDatabaseResolver::resolve($joomlaDatabase, $databaseSelector);
            }

            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('m.id'),
                    $db->quoteName('m.projectteam1_id'),
                    $db->quoteName('m.projectteam2_id'),
                    $db->quoteName('m.match_date'),
                    'DATE_FORMAT(' . $db->quoteName('m.time_present') . ', "%H:%i") AS time_present',
                    $db->quoteName('playground.id', 'playground_id'),
                    $db->quoteName('playground.name', 'playground_name'),
                    $db->quoteName('playground.short_name', 'playground_short_name'),
                    $db->quoteName('playground.address', 'playground_address'),
                    $db->quoteName('playground.zipcode', 'playground_zipcode'),
                    $db->quoteName('playground.city', 'playground_city'),
                    $db->quoteName('pt1.project_id'),
                    $db->quoteName('d1.name', 'divhome'),
                    $db->quoteName('d2.name', 'divaway'),
                    "CASE WHEN CHAR_LENGTH(t1.alias) AND CHAR_LENGTH(t2.alias) THEN CONCAT_WS(':',m.id,CONCAT_WS('_',t1.alias,t2.alias)) ELSE m.id END AS slug",
                    "CONCAT_WS(':', p.id, p.alias) AS project_slug",
                    "CONCAT_WS(':', r.id, r.alias) AS round_slug",
                    "CONCAT_WS(':', playground.id, playground.alias) AS playground_slug",
                    $db->quoteName('t1.id', 'team1'),
                    $db->quoteName('t2.id', 'team2'),
                    $db->quoteName('p.name', 'project_name'),
                ])
                ->from($db->quoteName('#__sportsmanagement_match', 'm'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('m.projectteam1_id') . ' = ' . $db->quoteName('pt1.id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('m.projectteam2_id') . ' = ' . $db->quoteName('pt2.id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_division', 'd1') . ' ON ' . $db->quoteName('m.division_id') . ' = ' . $db->quoteName('d1.id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_division', 'd2') . ' ON ' . $db->quoteName('m.division_id') . ' = ' . $db->quoteName('d2.id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_playground', 'playground') . ' ON ' . $db->quoteName('playground.id') . ' = ' . $db->quoteName('m.playground_id'))
                ->where($db->quoteName('m.published') . ' = 1')
                ->where($db->quoteName('r.project_id') . ' = ' . $projectId)
                ->order($db->quoteName('m.match_date') . ' ' . $direction)
                ->order($db->quoteName('m.match_number') . ' ' . $direction);

            if ($teamId > 0) {
                $query->where('(' . $db->quoteName('t1.id') . ' = ' . $teamId . ' OR ' . $db->quoteName('t2.id') . ' = ' . $teamId . ')');
            }

            $db->setQuery($query);

            return $db->loadObjectList('id') ?: [];
        } catch (\Throwable $exception) {
            $app->enqueueMessage(
                Text::_(__METHOD__ . ' ' . $exception->getMessage()),
                'error'
            );

            return false;
        }
    }
}
