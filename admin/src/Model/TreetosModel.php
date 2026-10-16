<?php
/**
 * Joomla 5/6 administrator tournament-trees list model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;

/** Native Joomla 5/6 administrator list model for tournament trees. */
final class TreetosModel extends SportsManagementListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'tt.name', 'name',
            'tt.id', 'id',
            'tt.tree_i', 'tree_i',
            'tt.hide', 'hide',
            'tt.division_id', 'division_id',
            'tt.published', 'published', 'state',
        ];

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'tt.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = $this->administratorApplication();
        $input = $app->getInput();
        $projectId = $input->getInt('pid') ?: (int) $app->getUserState('com_sportsmanagement.pid', 0);
        $this->setState('filter.pid', $projectId);

        if ($projectId > 0) {
            $app->setUserState('com_sportsmanagement.pid', $projectId);
        }

        $divisionId = $input->getInt(
            'division',
            (int) $app->getUserState('com_sportsmanagement.treetos.division', 0)
        );
        $this->setState('filter.division', $divisionId);
        $app->setUserState('com_sportsmanagement.treetos.division', $divisionId);
    }

    public function getProjectId(): int
    {
        return (int) $this->getState('filter.pid', 0);
    }

    /** Save only tree division assignments within the submitted project. */
    public function storeshort($cid, $data): bool
    {
        $identity = $this->administratorApplication()->getIdentity();

        if ($identity === null || !$identity->authorise('core.edit', 'com_sportsmanagement')) {
            throw new \RuntimeException('Not authorized to edit tournament trees.', 403);
        }

        if (!is_array($cid) || count($cid) > 500 || !is_array($data)) {
            $this->setError('Invalid tournament-tree selection.');
            return false;
        }

        $projectId = $this->positiveId($data['project_id'] ?? $data['pid'] ?? null);
        $ids = [];
        $divisions = [];

        foreach ($cid as $rawId) {
            $id = $this->positiveId($rawId);

            if ($id <= 0) {
                $this->setError('Invalid tournament-tree ID.');
                return false;
            }

            $rawDivision = $data['division_id' . $id] ?? 0;
            $divisionId = $rawDivision === 0 || $rawDivision === '0' || $rawDivision === ''
                ? 0
                : $this->positiveId($rawDivision);

            if ($divisionId < 0 || ($divisionId === 0 && !in_array($rawDivision, [0, '0', ''], true))) {
                $this->setError('Invalid tournament division ID.');
                return false;
            }

            $ids[$id] = $id;
            $divisions[$id] = $divisionId;
        }

        if ($projectId <= 0) {
            $this->setError('Invalid tournament-tree project.');
            return false;
        }

        if ($ids === []) {
            return true;
        }

        $db = $this->getDatabase();

        try {
            // All selected trees must be owned by the same project.
            $treeQuery = $db->createQuery()
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__sportsmanagement_treeto'))
                ->whereIn($db->quoteName('id'), array_values($ids), ParameterType::INTEGER)
                ->where($db->quoteName('project_id') . ' = :treeProjectId')
                ->bind(':treeProjectId', $projectId, ParameterType::INTEGER);
            $db->setQuery($treeQuery, 0, 500);
            $found = array_map('intval', $db->loadColumn() ?: []);
            sort($found);
            $expected = array_values($ids);
            sort($expected);

            if ($found !== $expected) {
                $this->setError('Selected tournament trees do not belong to this project.');
                return false;
            }

            $divisionIds = array_values(array_unique(array_filter(
                array_values($divisions),
                static fn(int $id): bool => $id > 0
            )));

            if ($divisionIds !== []) {
                $divisionQuery = $db->createQuery()
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__sportsmanagement_division'))
                    ->whereIn($db->quoteName('id'), $divisionIds, ParameterType::INTEGER)
                    ->where($db->quoteName('project_id') . ' = :divisionProjectId')
                    ->bind(':divisionProjectId', $projectId, ParameterType::INTEGER);
                $db->setQuery($divisionQuery, 0, 500);
                $foundDivisions = array_map('intval', $db->loadColumn() ?: []);
                sort($foundDivisions);
                sort($divisionIds);

                if ($foundDivisions !== $divisionIds) {
                    $this->setError('Tournament divisions do not belong to this project.');
                    return false;
                }
            }
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return false;
        }

        $db->transactionStart();

        try {
            foreach ($ids as $id) {
                $row = (object) [
                    'id' => $id,
                    'division_id' => $divisions[$id],
                ];

                if (!$db->updateObject('#__sportsmanagement_treeto', $row, 'id')) {
                    throw new \RuntimeException('Could not update tournament division.');
                }
            }

            $db->transactionCommit();
            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());
            return false;
        }
    }

    private function positiveId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $raw = trim((string) $value);

        if (!preg_match('/^[1-9][0-9]*$/D', $raw)) {
            return 0;
        }

        $id = filter_var($raw, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        return $id === false ? 0 : $id;
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $projectId = $this->getProjectId();
        $query = $db->createQuery()
            ->select($db->quoteName('tt') . '.*')
            ->from($db->quoteName('#__sportsmanagement_treeto', 'tt'))
            ->where($db->quoteName('tt.project_id') . ' = :listProjectId')
            ->bind(':listProjectId', $projectId, ParameterType::INTEGER);

        $divisionId = (int) $this->getState('filter.division', 0);

        if ($divisionId > 0) {
            $query->where($db->quoteName('tt.division_id') . ' = :listDivisionId')
                ->bind(':listDivisionId', $divisionId, ParameterType::INTEGER);
        }

        $orderMap = [
            'tt.name' => $db->quoteName('tt.name'),
            'name' => $db->quoteName('tt.name'),
            'tt.id' => $db->quoteName('tt.id'),
            'id' => $db->quoteName('tt.id'),
            'tt.tree_i' => $db->quoteName('tt.tree_i'),
            'tree_i' => $db->quoteName('tt.tree_i'),
            'tt.hide' => $db->quoteName('tt.hide'),
            'hide' => $db->quoteName('tt.hide'),
            'tt.division_id' => $db->quoteName('tt.division_id'),
            'division_id' => $db->quoteName('tt.division_id'),
            'tt.published' => $db->quoteName('tt.published'),
            'published' => $db->quoteName('tt.published'),
            'state' => $db->quoteName('tt.published'),
        ];
        $ordering = (string) $this->getState('list.ordering', 'tt.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($orderMap[$ordering] ?? $orderMap['tt.name']) . ' ' . $direction);

        return $query;
    }

    public function getProject(): ?object
    {
        $projectId = $this->getProjectId();

        if ($projectId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    /** Return division selector options in the shape expected by the administrator list view. */
    public function getDivisions(): array
    {
        $db = $this->getDatabase();
        $projectId = $this->getProjectId();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('d.id', 'value'),
                $db->quoteName('d.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_division', 'd'))
            ->where($db->quoteName('d.project_id') . ' = :divisionProjectId')
            ->bind(':divisionProjectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('d.name') . ' ASC');

        try {
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->administratorApplication()->enqueueMessage($e->getMessage(), 'error');

            return [];
        }
    }
}
