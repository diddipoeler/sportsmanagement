<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

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

        $app = Factory::getApplication();
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

    /** Save division assignments from the compact list editor. */
    public function storeshort($cid, $data): bool
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $cid))));

        if (!$ids) {
            return true;
        }

        $db = $this->getDatabase();
        $db->transactionStart();

        try {
            foreach ($ids as $id) {
                $divisionId = (int) ($data['division_id' . $id] ?? 0);
                $row = (object) [
                    'id' => $id,
                    'division_id' => $divisionId,
                ];
                $db->updateObject('#__sportsmanagement_treeto', $row, 'id');
            }

            $db->transactionCommit();

            return true;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            $this->setError($e->getMessage());

            return false;
        }
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('tt') . '.*')
            ->from($db->quoteName('#__sportsmanagement_treeto', 'tt'))
            ->where($db->quoteName('tt.project_id') . ' = ' . $this->getProjectId());

        $divisionId = (int) $this->getState('filter.division', 0);

        if ($divisionId > 0) {
            $query->where($db->quoteName('tt.division_id') . ' = ' . $divisionId);
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
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    /** Return division selector options in the shape expected by the administrator list view. */
    public function getDivisions(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('d.id', 'value'),
                $db->quoteName('d.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_division', 'd'))
            ->where($db->quoteName('d.project_id') . ' = ' . $this->getProjectId())
            ->order($db->quoteName('d.name') . ' ASC');

        try {
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return [];
        }
    }
}
