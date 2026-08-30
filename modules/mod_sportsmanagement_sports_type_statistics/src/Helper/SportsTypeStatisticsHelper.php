<?php
namespace Diddipoeler\Module\SportsManagementSportsTypeStatistics\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class SportsTypeStatisticsHelper
{
    public function getData(Registry $params, CMSApplicationInterface $app): array
    {
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = $app->getContainer()->get(DatabaseInterface::class);
        $db = $this->database($params, $joomlaDatabase);
        $sportTypeId = (int) $params->get('sportstypes', 0);

        $sportType = null;
        if ($sportTypeId > 0) {
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('id'),
                    $db->quoteName('name'),
                    $db->quoteName('icon'),
                ])
                ->from($db->quoteName('#__sportsmanagement_sports_type'))
                ->where($db->quoteName('id') . ' = ' . $sportTypeId);
            $db->setQuery($query, 0, 1);
            $sportType = $db->loadObject() ?: null;
        }

        $counts = [
            'projects' => $this->countProjects($db, $sportTypeId),
        ];

        $requested = [
            'leagues' => ['show_leagues', fn() => $this->countTable($db, '#__sportsmanagement_league')],
            'seasons' => ['show_seasons', fn() => $this->countTable($db, '#__sportsmanagement_season')],
            'playgrounds' => ['show_playgrounds', fn() => $this->countTable($db, '#__sportsmanagement_playground')],
            'clubs' => ['show_clubs', fn() => $this->countTable($db, '#__sportsmanagement_club')],
            'teams' => ['show_teams', fn() => $this->countProjectRelation($db, '#__sportsmanagement_project_team', $sportTypeId)],
            'players' => ['show_players', fn() => $this->countTable($db, '#__sportsmanagement_person')],
            'divisions' => ['show_divisions', fn() => $this->countProjectRelation($db, '#__sportsmanagement_division', $sportTypeId)],
            'rounds' => ['show_rounds', fn() => $this->countProjectRelation($db, '#__sportsmanagement_round', $sportTypeId)],
            'matches' => ['show_matches', fn() => $this->countMatches($db, $sportTypeId)],
            'player_events' => ['show_player_events', fn() => $this->countMatchChild($db, '#__sportsmanagement_match_event', $sportTypeId)],
            'player_stats' => ['show_player_stats', fn() => $this->countMatchChild($db, '#__sportsmanagement_match_statistic', $sportTypeId)],
        ];

        foreach ($requested as $key => [$parameter, $callback]) {
            if ((int) $params->get($parameter, 1) === 1) {
                $counts[$key] = (int) $callback();
            }
        }

        return [
            'sportstype' => $sportType,
            'counts' => $counts,
        ];
    }

    private function countProjects(DatabaseInterface $db, int $sportTypeId): int
    {
        if ($sportTypeId <= 0) {
            return 0;
        }

        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('sports_type_id') . ' = ' . $sportTypeId);
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    private function countTable(DatabaseInterface $db, string $table): int
    {
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName($table));
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    private function countProjectRelation(DatabaseInterface $db, string $table, int $sportTypeId): int
    {
        if ($sportTypeId <= 0) {
            return 0;
        }

        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName($table, 'rel'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('rel.project_id'))
            ->where($db->quoteName('p.sports_type_id') . ' = ' . $sportTypeId);
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    private function countMatches(DatabaseInterface $db, int $sportTypeId): int
    {
        if ($sportTypeId <= 0) {
            return 0;
        }

        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id'))
            ->where($db->quoteName('p.sports_type_id') . ' = ' . $sportTypeId);
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    private function countMatchChild(DatabaseInterface $db, string $table, int $sportTypeId): int
    {
        if ($sportTypeId <= 0) {
            return 0;
        }

        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName($table, 'child'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('child.match_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id'))
            ->where($db->quoteName('p.sports_type_id') . ' = ' . $sportTypeId);
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    private function database(Registry $params, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve(
            $fallbackDatabase,
            (int) $params->get('cfg_which_database', 0)
        );
    }
}
