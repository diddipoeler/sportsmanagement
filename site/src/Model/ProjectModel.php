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

}
