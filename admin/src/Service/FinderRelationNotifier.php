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
use Joomla\CMS\Factory;
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
    public function __construct(private DatabaseInterface $db)
    {
    }

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
            $dispatcher = Factory::getApplication()->getDispatcher();
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

    public function notifyPeople(array $personIds): void
    {
        $this->notify(['person' => $personIds]);
    }

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

    private function emptyEntities(): array
    {
        return ['club' => [], 'team' => [], 'person' => []];
    }

    private function normaliseIds(mixed $ids): array
    {
        return array_values(array_unique(array_filter(
            array_map('intval', (array) $ids),
            static fn (int $id): bool => $id > 0
        )));
    }
}
