<?php
/**
 * Smart Search notifications for SportsManagement relation-table writes.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Event\Finder as FinderEvent;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Bridges SportsManagement relation-table writes to Joomla Smart Search.
 *
 * Native Joomla content models trigger Finder events automatically. Several
 * SportsManagement administrator workflows update relation tables directly,
 * so this service derives the affected primary entities and emits the same
 * typed onFinderAfterSave event Joomla's content/finder plugin emits.
 */
final class FinderRelationNotifier
{
    /**
     * Create the relation-change notifier with Joomla's database connection.
     * Stores the database used to identify affected clubs, teams and people after relation-table edits.
     *
     * @param DatabaseInterface $db Joomla database driver.
     * @return void
     */
    public function __construct(private DatabaseInterface $db)
    {
    }

    /**
     * Send Smart Search update events for affected clubs, teams and people.
     * Deduplicates validated entity IDs, imports Joomla Finder plugins and dispatches typed after-save events; a Finder failure never undoes an already committed relation write.
     *
     * @param array ...$entitySets Collections of club, team and person IDs affected by the write.
     * @return void
     */
    public function notify(array ...$entitySets): void
    {
        $entities = ['club' => [], 'team' => [], 'person' => []];

        foreach ($entitySets as $set) {
            foreach ($entities as $entity => $_) {
                foreach ($this->normaliseIds($set[$entity] ?? []) as $id) {
                    $entities[$entity][$id] = $id;
                }
            }
        }

        $contexts = [
            'club' => 'com_sportsmanagement.club',
            'team' => 'com_sportsmanagement.team',
            'person' => 'com_sportsmanagement.person',
        ];

        if (!$entities['club'] && !$entities['team'] && !$entities['person']) {
            return;
        }

        try {
            $dispatcher = SportsManagementAdministratorApplicationResolver::resolve()->getDispatcher();
            PluginHelper::importPlugin('finder', null, true, $dispatcher);

            foreach ($contexts as $entity => $context) {
                foreach ($entities[$entity] as $id) {
                    $dispatcher->dispatch(
                        'onFinderAfterSave',
                        new FinderEvent\AfterSaveEvent('onFinderAfterSave', [
                            'context' => $context,
                            'subject' => (object) ['id' => $id],
                            'isNew' => false,
                        ])
                    );
                }
            }
        } catch (\Throwable) {
            // Finder updates must never turn an already committed admin write
            // into a user-visible failure. A full Smart Search rebuild remains
            // available as the recovery path.
        }
    }

    /**
     * Notify Joomla Smart Search that selected person records changed.
     * Wraps the person ID list into the entity structure required by the generic Finder notifier.
     *
     * @param array $personIds Person identifiers to reindex.
     * @return void
     */
    public function notifyPeople(array $personIds): void
    {
        $this->notify(['person' => $personIds]);
    }

    /**
     * Find person IDs referenced by selected season-team-person relation records.
     * Validates and deduplicates relation IDs before querying, and returns an empty list when the relation lookup fails.
     *
     * @param array $relationIds Season-team-person relation record IDs.
     * @return array<int,int> Related person identifiers safe for Finder updates.
     */
    public function peopleForTeamRelations(array $relationIds): array
    {
        $ids = $this->normaliseIds($relationIds);

        if (!$ids) {
            return [];
        }

        $query = $this->db->createQuery()
            ->select($this->db->quoteName('person_id'))
            ->from($this->db->quoteName('#__sportsmanagement_season_team_person_id'))
            ->where($this->db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');

        try {
            return $this->normaliseIds($this->db->setQuery($query)->loadColumn());
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Find a team's player or staff people within one season for reindexing.
     * Checks team, season and person type before binding those values into the registration query; missing relations yield an empty list.
     *
     * @param int $teamId Underlying team identifier.
     * @param int $seasonId Season identifier.
     * @param int $personType Player or staff category, restricted to types 1 and 2.
     * @return array<int,int> Person IDs belonging to the selected team context.
     */
    public function peopleForTeamContext(int $teamId, int $seasonId, int $personType): array
    {
        if ($teamId <= 0 || $seasonId <= 0 || !in_array($personType, [1, 2], true)) {
            return [];
        }

        $query = $this->db->createQuery()
            ->select($this->db->quoteName('person_id'))
            ->from($this->db->quoteName('#__sportsmanagement_season_team_person_id'))
            ->where($this->db->quoteName('team_id') . ' = :teamId')
            ->where($this->db->quoteName('season_id') . ' = :seasonId')
            ->where($this->db->quoteName('persontype') . ' = :personType')
            ->bind(':teamId', $teamId, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER)
            ->bind(':personType', $personType, ParameterType::INTEGER);

        try {
            return $this->normaliseIds($this->db->setQuery($query)->loadColumn());
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Collect Finder entities affected by one project's team relations.
     * Finds teams, clubs and registered people connected to the given project while excluding unsupported individual-project types.
     *
     * @param int $projectId Project whose team relationships changed.
     * @return array{club:array,team:array,person:array} Affected entity identifiers.
     */
    public function projectTeamEntitiesForProject(int $projectId): array
    {
        if ($projectId <= 0) {
            return $this->emptyEntities();
        }

        return $this->loadProjectTeamEntities(
            $this->db->quoteName('pt.project_id') . ' = :projectId',
            $projectId
        );
    }

    /**
     * Collect Finder entity IDs from a set of project-team relation rows.
     * Normalises relation IDs and restricts the shared relation query to those rows, producing empty entity lists for invalid input.
     *
     * @param array $projectTeamIds Project-team relation identifiers.
     * @return array{club:array,team:array,person:array} Related club, team and person IDs.
     */
    public function projectTeamEntitiesForRows(array $projectTeamIds): array
    {
        $ids = $this->normaliseIds($projectTeamIds);

        if (!$ids) {
            return $this->emptyEntities();
        }

        return $this->loadProjectTeamEntities(
            $this->db->quoteName('pt.id') . ' IN (' . implode(',', $ids) . ')'
        );
    }

    /**
     * Find the people associated with changed project-referee relations.
     * Joins referee relations to season-person records and returns normalised person IDs for Joomla Smart Search events.
     *
     * @param array $projectRefereeIds Changed project-referee relation row IDs.
     * @return array<int,int> Associated person identifiers.
     */
    public function projectRefereePeopleForRows(array $projectRefereeIds): array
    {
        $ids = $this->normaliseIds($projectRefereeIds);

        if (!$ids) {
            return [];
        }

        $query = $this->db->createQuery()
            ->select($this->db->quoteName('sp.person_id'))
            ->from($this->db->quoteName('#__sportsmanagement_project_referee', 'pr'))
            ->join(
                'INNER',
                $this->db->quoteName('#__sportsmanagement_season_person_id', 'sp')
                . ' ON ' . $this->db->quoteName('sp.id') . ' = ' . $this->db->quoteName('pr.person_id')
            )
            ->where($this->db->quoteName('pr.id') . ' IN (' . implode(',', $ids) . ')');

        try {
            return $this->normaliseIds($this->db->setQuery($query)->loadColumn());
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Look up club, team and person IDs for a controlled project-team query scope.
     * Joins project teams with season registrations and their clubs, collecting unique positive entity IDs while treating unavailable Finder lookup data as nonfatal.
     *
     * @param string $where Trusted project relation query condition.
     * @param int|null $projectId Optional project ID bound to the SQL condition.
     * @return array{club:array,team:array,person:array} Deduplicated affected entities.
     */
    private function loadProjectTeamEntities(string $where, ?int $projectId = null): array
    {
        $entities = $this->emptyEntities();
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('t.id', 'team_id'),
                $this->db->quoteName('t.club_id'),
                $this->db->quoteName('tp.person_id'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'INNER',
                $this->db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $this->db->quoteName('p.id') . ' = ' . $this->db->quoteName('pt.project_id')
            )
            ->join(
                'INNER',
                $this->db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $this->db->quoteName('st.id') . ' = ' . $this->db->quoteName('pt.team_id')
            )
            ->join(
                'INNER',
                $this->db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $this->db->quoteName('t.id') . ' = ' . $this->db->quoteName('st.team_id')
            )
            ->join(
                'LEFT',
                $this->db->quoteName('#__sportsmanagement_season_team_person_id', 'tp')
                . ' ON ' . $this->db->quoteName('tp.team_id') . ' = ' . $this->db->quoteName('st.team_id')
                . ' AND ' . $this->db->quoteName('tp.season_id') . ' = ' . $this->db->quoteName('st.season_id')
                . ' AND ' . $this->db->quoteName('tp.persontype') . ' IN (1,2)'
            )
            ->where($this->db->quoteName('p.project_art_id') . ' <> 3')
            ->where($where);

        if ($projectId !== null) {
            $query->bind(':projectId', $projectId, ParameterType::INTEGER);
        }

        try {
            $rows = $this->db->setQuery($query)->loadObjectList() ?: [];
        } catch (\Throwable) {
            return $entities;
        }

        foreach ($rows as $row) {
            $teamId = (int) ($row->team_id ?? 0);
            $clubId = (int) ($row->club_id ?? 0);
            $personId = (int) ($row->person_id ?? 0);

            if ($teamId > 0) {
                $entities['team'][$teamId] = $teamId;
            }
            if ($clubId > 0) {
                $entities['club'][$clubId] = $clubId;
            }
            if ($personId > 0) {
                $entities['person'][$personId] = $personId;
            }
        }

        return array_map('array_values', $entities);
    }

    /**
     * Create an empty typed collection for all supported Finder entity categories.
     * Provides consistent club, team and person result keys when no valid relations exist.
     *
     * @return array{club:array,team:array,person:array} Empty Finder entity collections.
     */
    private function emptyEntities(): array
    {
        return ['club' => [], 'team' => [], 'person' => []];
    }

    /**
     * Validate and deduplicate positive entity IDs before Finder queries and events.
     * Accepts positive integers and complete decimal strings, rejects booleans, nonnumeric suffixes, fractional values and overflow IDs.
     *
     * @param mixed $ids Scalar or list of candidate entity identifiers.
     * @return array<int,int> Unique positive integer identifiers.
     */
    private function normaliseIds(mixed $ids): array
    {
        $normalised = [];

        foreach (is_array($ids) ? $ids : [$ids] as $candidate) {
            if (is_int($candidate)) {
                $id = $candidate;
            } elseif (is_string($candidate)
                && preg_match('/^[0-9]+$/D', trim($candidate))) {
                $digits = ltrim(trim($candidate), '0');

                if ($digits === '') {
                    continue;
                }

                $id = filter_var($digits, FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1],
                ]);

                if ($id === false) {
                    continue;
                }
            } else {
                // (int) "12garbage", true and 12.9 must not notify unrelated
                // Finder entities or unexpectedly broaden relation lookups.
                continue;
            }

            if ($id > 0) {
                $normalised[$id] = $id;
            }
        }

        return array_values($normalised);
    }
}
