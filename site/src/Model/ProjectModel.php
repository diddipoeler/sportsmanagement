<?php
/**
 * Native Joomla 5/6 frontend project model.
 *
 * This concrete model exposes the shared project data API implemented by
 * SportsManagementProjectModel so legacy static callers can migrate without
 * duplicating Joomla database access.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;

final class ProjectModel extends SportsManagementProjectModel
{
    /** Return project event types, optionally restricted to one position. */
    public function getProjectEvents(int $positionId = 0): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $projectId = $this->projectId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('et.id'),
                $db->quoteName('et.name'),
                $db->quoteName('et.icon'),
            ])
            ->from($db->quoteName('#__sportsmanagement_eventtype', 'et'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_position_eventtype', 'pet')
                . ' ON ' . $db->quoteName('pet.eventtype_id') . ' = ' . $db->quoteName('et.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_position', 'ppos')
                . ' ON ' . $db->quoteName('ppos.position_id') . ' = ' . $db->quoteName('pet.position_id')
            )
            ->where($db->quoteName('ppos.project_id') . ' = :projectEventsProjectId')
            ->bind(':projectEventsProjectId', $projectId, ParameterType::INTEGER)
            ->group([
                $db->quoteName('et.id'),
                $db->quoteName('et.name'),
                $db->quoteName('et.icon'),
            ]);

        if ($positionId > 0) {
            $query->where($db->quoteName('ppos.position_id') . ' = :projectEventsPositionId')
                ->bind(':projectEventsPositionId', $positionId, ParameterType::INTEGER);
        }

        $db->setQuery($query);

        return $db->loadObjectList('id') ?: [];
    }

    /** Return the positions configured for the active project, keyed by position id. */
    public function getProjectPositions(): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $projectId = $this->projectId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pos.id'),
                $db->quoteName('pos.persontype'),
                $db->quoteName('pos.name'),
                $db->quoteName('pos.ordering'),
                $db->quoteName('pos.published'),
                $db->quoteName('ppos.id', 'pposid'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_position', 'ppos'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_position', 'pos')
                . ' ON ' . $db->quoteName('ppos.position_id') . ' = ' . $db->quoteName('pos.id')
            )
            ->where($db->quoteName('ppos.project_id') . ' = :projectPositionsProjectId')
            ->bind(':projectPositionsProjectId', $projectId, ParameterType::INTEGER)
            ->order([
                $db->quoteName('pos.persontype') . ' ASC',
                $db->quoteName('pos.ordering') . ' ASC',
            ]);

        $db->setQuery($query);

        return $db->loadObjectList('id') ?: [];
    }

    /** Return team ids for the active project, optionally restricted to a division tree. */
    public function getTeamIds(int $divisionId = 0): array
    {
        $ids = [];

        foreach ($this->getProjectTeams($divisionId) as $team) {
            $teamId = (int) ($team->id ?? 0);
            if ($teamId > 0) {
                $ids[$teamId] = $teamId;
            }
        }

        return array_values($ids);
    }

    /** Return playground options using the legacy value/text object contract. */
    public function getPlaygrounds(): array
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_playground'))
            ->order($db->quoteName('name') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /** Return regular game time plus configured additional time for a project. */
    public function getProjectGameRegularTime(int $projectId): int
    {
        if ($projectId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('game_regular_time'),
                $db->quoteName('allow_add_time'),
                $db->quoteName('add_time'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = :gameTimeProjectId')
            ->bind(':gameTimeProjectId', $projectId, ParameterType::INTEGER);

        $db->setQuery($query, 0, 1);
        $project = $db->loadObject();

        if (!$project) {
            return 0;
        }

        $regularTime = (int) ($project->game_regular_time ?? 0);

        if ((int) ($project->allow_add_time ?? 0) === 1) {
            $regularTime += (int) ($project->add_time ?? 0);
        }

        return $regularTime;
    }

    /** Return the league country attached to the active project. */
    public function getProjectCountry(): string
    {
        $project = $this->getProject();

        return $project ? (string) ($project->country ?? '') : '';
    }

    /**
     * Return event types using the legacy id-keyed contract.
     *
     * @param mixed $eventIds Comma-separated ids, one id, or an array of ids.
     */
    public function getEventTypes($eventIds = 0, int $sportsTypeId = 0, int $projectId = 0, int $matchId = 0): array
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('et.id', 'etid'),
                $db->quoteName('et.name'),
                $db->quoteName('et.icon'),
                $db->quoteName('et.id', 'id'),
                "CONCAT_WS(':', et.id, et.alias) AS event_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_eventtype', 'et'));

        $needsMatchJoin = $matchId > 0 || $projectId > 0;
        if ($needsMatchJoin) {
            $query->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_match_event', 'me')
                . ' ON ' . $db->quoteName('me.event_type_id') . ' = ' . $db->quoteName('et.id')
            );
        }

        if ($matchId > 0) {
            $query->where($db->quoteName('me.match_id') . ' = :eventTypeMatchId')
                ->bind(':eventTypeMatchId', $matchId, ParameterType::INTEGER);
        }

        if ($projectId > 0) {
            $query
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_match', 'mat')
                    . ' ON ' . $db->quoteName('mat.id') . ' = ' . $db->quoteName('me.match_id')
                )
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_round', 'r')
                    . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('mat.round_id')
                )
                ->where($db->quoteName('r.project_id') . ' = :eventTypeProjectId')
                ->bind(':eventTypeProjectId', $projectId, ParameterType::INTEGER);
        }

        $ids = [];
        foreach (is_array($eventIds) ? $eventIds : explode(',', (string) $eventIds) as $value) {
            $id = (int) trim((string) $value);
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        if ($ids) {
            $query->whereIn($db->quoteName('et.id'), array_values($ids), ParameterType::INTEGER);
        }

        if ($sportsTypeId > 0) {
            $query->where($db->quoteName('et.sports_type_id') . ' = :eventTypeSportsTypeId')
                ->bind(':eventTypeSportsTypeId', $sportsTypeId, ParameterType::INTEGER);
        }

        $query
            ->group([
                $db->quoteName('et.id'),
                $db->quoteName('et.name'),
                $db->quoteName('et.icon'),
                $db->quoteName('et.alias'),
            ])
            ->order($db->quoteName('et.name') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList('etid') ?: [];
    }


    /** Count matches for one project or for all projects in one league/season. */
    public function getProjectMatchCount(
        int $projectId = 0,
        bool $allOverLeagueId = false,
        int $leagueId = 0,
        int $seasonId = 0
    ): int {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(DISTINCT ' . $db->quoteName('m.id') . ')')
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            );

        if ($allOverLeagueId && $leagueId > 0 && $seasonId > 0) {
            $query
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_project', 'p')
                    . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id')
                )
                ->where($db->quoteName('p.league_id') . ' = :matchCountLeagueId')
                ->where($db->quoteName('p.season_id') . ' = :matchCountSeasonId')
                ->bind(':matchCountLeagueId', $leagueId, ParameterType::INTEGER)
                ->bind(':matchCountSeasonId', $seasonId, ParameterType::INTEGER);
        } else {
            $projectId = $projectId > 0 ? $projectId : $this->projectId;
            if ($projectId <= 0) {
                return 0;
            }
            $query->where($db->quoteName('r.project_id') . ' = :matchCountProjectId')
                ->bind(':matchCountProjectId', $projectId, ParameterType::INTEGER);
        }

        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /** Return the next or previous project by name inside the same league. */
    public function getAdjacentProject(string $name, int $leagueId, bool $next = true): ?object
    {
        if ($leagueId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                'p.*',
                "CONCAT_WS(':', p.id, p.alias) AS slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->where($db->quoteName('p.league_id') . ' = :adjacentLeagueId')
            ->where($db->quoteName('p.name') . ($next ? ' > ' : ' < ') . ':adjacentProjectName')
            ->bind(':adjacentLeagueId', $leagueId, ParameterType::INTEGER)
            ->bind(':adjacentProjectName', $name, ParameterType::STRING)
            ->order($db->quoteName('p.name') . ($next ? ' ASC' : ' DESC'));

        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    /** Increment project hits when requested. */
    public function updateProjectHits(int $projectId, bool $increment): void
    {
        if (!$increment || $projectId <= 0) {
            return;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->update($db->quoteName('#__sportsmanagement_project'))
            ->set($db->quoteName('hits') . ' = ' . $db->quoteName('hits') . ' + 1')
            ->where($db->quoteName('id') . ' = :hitProjectId')
            ->bind(':hitProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query)->execute();
    }

    /** Return published divisions for the active divisions project, keyed by division id. */
    public function getProjectDivisions(int $level = 0): array
    {
        $project = $this->getProject();
        if (!$project || (string) ($project->project_type ?? '') !== 'DIVISIONS_LEAGUE') {
            return [];
        }

        $projectId = $this->projectId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_division'))
            ->where($db->quoteName('project_id') . ' = :divisionProjectId')
            ->where($db->quoteName('published') . ' = 1')
            ->bind(':divisionProjectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('ordering') . ' ASC');

        if ($level === 1) {
            $query->where('(' . $db->quoteName('parent_id') . ' = 0 OR ' . $db->quoteName('parent_id') . ' IS NULL)');
        } elseif ($level === 2) {
            $query->where($db->quoteName('parent_id') . ' > 0');
        }

        $db->setQuery($query);

        return $db->loadObjectList('id') ?: [];
    }

    /** Return division ids for the active project and requested hierarchy level. */
    public function getProjectDivisionIds(int $level = 0): array
    {
        return array_values(
            array_map(
                'intval',
                array_keys($this->getProjectDivisions($level))
            )
        );
    }

    /** Public compatibility wrapper around the recursive native division-tree resolver. */
    public function getProjectDivisionTreeIds(int $divisionId): array
    {
        if ($divisionId <= 0) {
            return $this->getProjectDivisionIds();
        }

        return parent::getDivisionTreeIds($divisionId);
    }

    /** Return Joomla list options for the active project's rounds. */
    public function getRoundOptions(string $ordering = 'ASC', bool $slug = true): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $projectId = $this->projectId;
        $direction = strtoupper($ordering) === 'DESC' ? 'DESC' : 'ASC';
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('r.id', 'value'))
            ->select(
                "CASE LENGTH(r.name) WHEN 0 THEN CONCAT('"
                . Text::_('COM_SPORTSMANAGEMENT_MATCHDAY_NAME')
                . "', ' ', r.id) ELSE CONCAT(r.name, ' (', r.round_date_first, ')') END AS text"
            )
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = :roundOptionsProjectId')
            ->bind(':roundOptionsProjectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('r.roundcode') . ' ' . $direction);

        if ($slug) {
            $query->select("CONCAT_WS(':', r.id, r.alias) AS slug");
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /** Return the historical project-team information object for one project-team id. */
    public function getProjectTeamInfo(int $projectTeamId): ?object
    {
        if ($projectTeamId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                't.*',
                $db->quoteName('t.id', 'team_id'),
                $db->quoteName('t.picture'),
                $db->quoteName('t.picture', 'team_picture'),
                $db->quoteName('t.extended', 'teamextended'),
                "CONCAT_WS(':', t.id, t.alias) AS team_slug",
                $db->quoteName('pt.division_id'),
                $db->quoteName('pt.picture', 'projectteam_picture'),
                $db->quoteName('c.logo_small'),
                $db->quoteName('c.logo_middle'),
                $db->quoteName('c.logo_big'),
                $db->quoteName('c.country'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_club', 'c')
                . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id')
            )
            ->where($db->quoteName('pt.id') . ' = :projectTeamInfoId')
            ->bind(':projectTeamInfoId', $projectTeamId, ParameterType::INTEGER);

        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    /** Return the project-team id for one season-team id in the active project. */
    public function getProjectTeamId(int $seasonTeamId): int
    {
        if ($seasonTeamId <= 0 || $this->projectId <= 0) {
            return 0;
        }

        $projectId = $this->projectId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_project_team'))
            ->where($db->quoteName('team_id') . ' = :projectTeamSeasonTeamId')
            ->where($db->quoteName('project_id') . ' = :projectTeamProjectId')
            ->bind(':projectTeamSeasonTeamId', $seasonTeamId, ParameterType::INTEGER)
            ->bind(':projectTeamProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return (int) $db->loadResult();
    }

    /** Return legacy referee select options for the active project. */
    public function getRefereeOptions(): array
    {
        $project = $this->getProject();
        if (!$project) {
            return [];
        }

        $db = $this->getDatabase();

        if ((int) ($project->teams_as_referees ?? 0) === 1) {
            $query = $db->createQuery()
                ->select([
                    $db->quoteName('t.id', 'value'),
                    $db->quoteName('t.name', 'text'),
                ])
                ->from($db->quoteName('#__sportsmanagement_team', 't'))
                ->order($db->quoteName('t.name') . ' ASC');
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        }

        $projectId = $this->projectId;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pr.id', 'value'),
                $db->quoteName('p.firstname'),
                $db->quoteName('p.lastname'),
                "CONCAT(p.lastname, ',', p.firstname) AS text",
            ])
            ->from($db->quoteName('#__sportsmanagement_project_referee', 'pr'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_person_id', 'spi')
                . ' ON ' . $db->quoteName('spi.id') . ' = ' . $db->quoteName('pr.person_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_person', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('spi.person_id')
            )
            ->where($db->quoteName('pr.project_id') . ' = :refereeOptionsProjectId')
            ->bind(':refereeOptionsProjectId', $projectId, ParameterType::INTEGER)
            ->order([
                $db->quoteName('p.lastname') . ' ASC',
                $db->quoteName('p.firstname') . ' ASC',
            ]);

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }


    /** Check component ACL, falling back to the historical project admin/editor assignment. */
    public function hasEditPermission(?string $task = null): bool
    {
        $app = $this->siteApplication();
        $user = $app->getIdentity();

        if ((int) $user->id <= 0) {
            return false;
        }

        $allowed = false;

        if ($task !== null && $task !== '') {
            $allowed = $user->authorise($task, 'com_sportsmanagement');

            if (!$allowed) {
                $app->enqueueMessage(
                    Text::sprintf('COM_SPORTSMANAGEMENT_CLUBINFO_PAGE_ERROR_ACL_PERMISSION', $task),
                    'error'
                );
            }
        }

        if (!$allowed && $this->isUserProjectAdminOrEditor((int) $user->id)) {
            return true;
        }

        if (!$allowed) {
            $app->enqueueMessage(
                Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_PAGE_ERROR_ADMIN_EDITOR'),
                'error'
            );
        }

        return $allowed;
    }

    /** Check the historical per-project administrator/editor fields. */
    public function isUserProjectAdminOrEditor(int $userId, ?object $project = null): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $project ??= $this->getProject();

        if (!$project) {
            return false;
        }

        return $userId === (int) ($project->admin ?? 0)
            || $userId === (int) ($project->editor ?? 0);
    }


    /** Return the resolved current round as the historical round object. */
    public function getCurrentRoundData(): ?object
    {
        $roundId = $this->getCurrentRound();

        if ($roundId <= 0 || $this->projectId <= 0) {
            return null;
        }

        $projectId = $this->projectId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('r.id'),
                $db->quoteName('r.roundcode'),
                "CONCAT_WS(':', r.id, r.alias) AS round_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.id') . ' = :currentRoundDataId')
            ->where($db->quoteName('r.project_id') . ' = :currentRoundDataProjectId')
            ->bind(':currentRoundDataId', $roundId, ParameterType::INTEGER)
            ->bind(':currentRoundDataProjectId', $projectId, ParameterType::INTEGER);

        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    /** Return the historical project match object without legacy DB helpers. */
    public function getLegacyMatch(int $matchId): ?object
    {
        if ($matchId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                'm.*',
                'DATE_FORMAT(m.time_present, "%H:%i") AS time_present',
                $db->quoteName('r.project_id'),
                $db->quoteName('p.timezone'),
                $db->quoteName('p.game_parts'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id')
            )
            ->where($db->quoteName('m.id') . ' = :legacyMatchId')
            ->bind(':legacyMatchId', $matchId, ParameterType::INTEGER);

        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

}
