<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Native Joomla 5/6 administrator list model for project-position assignments.
 */
final class ProjectpositionsModel extends SportsManagementListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'po.name', 'name',
            'po.parent_id', 'parent_id',
            'po.id', 'id',
            'pt.id', 'positiontoolid',
        ];

        parent::__construct($config, $factory);
    }

    public function updateprojectpositions($items = null, $project_id = 0)
    {
        $items = is_iterable($items) ? $items : [];
        $projectId = (int) $project_id;
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('mp.match_id'))
            ->from($db->quoteName('#__sportsmanagement_match_player', 'mp'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_match', 'm')
                . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('mp.match_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->where($db->quoteName('r.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('mp.project_position_id') . ' != 0');

        try {
            $db->setQuery($query);
            $matchIds = array_values(array_unique(array_map('intval', $db->loadColumn() ?: [])));

            if (!$matchIds) {
                return true;
            }

            foreach ($items as $item) {
                $positionId = (int) ($item->position_id ?? 0);
                $positionToolId = (int) ($item->positiontoolid ?? 0);

                if ($positionId <= 0 || $positionToolId <= 0) {
                    continue;
                }

                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__sportsmanagement_match_player'))
                    ->set($db->quoteName('project_position_id') . ' = ' . $positionId)
                    ->where($db->quoteName('project_position_id') . ' = ' . $positionToolId)
                    ->where($db->quoteName('match_id') . ' IN (' . implode(',', $matchIds) . ')');
                $db->setQuery($query)->execute();
            }
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        return true;
    }

    public function insertStandardProjectPositions($project_id = 0, $sports_type_id = 0): bool
    {
        $projectId = (int) $project_id;
        $sportsTypeId = (int) $sports_type_id;
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_position'))
            ->where($db->quoteName('parent_id') . ' != 0')
            ->where($db->quoteName('sports_type_id') . ' = ' . $sportsTypeId)
            ->where($db->quoteName('persontype') . ' IN (1,2)');

        try {
            $db->setQuery($query);
            $positionIds = array_map('intval', $db->loadColumn() ?: []);

            foreach ($positionIds as $positionId) {
                $query = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($db->quoteName('#__sportsmanagement_project_position'))
                    ->where($db->quoteName('project_id') . ' = ' . $projectId)
                    ->where($db->quoteName('position_id') . ' = ' . $positionId);
                $db->setQuery($query);

                if ((int) $db->loadResult() > 0) {
                    continue;
                }

                $assignment = new \stdClass();
                $assignment->project_id = $projectId;
                $assignment->position_id = $positionId;
                $db->insertObject('#__sportsmanagement_project_position', $assignment);
            }
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        return true;
    }

    public function getSubPositions($sports_type_id = 1)
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
                $db->quoteName('sports_type_id', 'type'),
                $db->quoteName('parent_id', 'parentID'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position'))
            ->where($db->quoteName('sports_type_id') . ' = ' . (int) $sports_type_id)
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('parent_id') . ' ASC')
            ->order($db->quoteName('name') . ' ASC');

        try {
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }

    public function getProjectPositionsCount($project_id): int
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_project_position', 'pp'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pp.project_id')
            )
            ->where($db->quoteName('p.id') . ' = ' . (int) $project_id);
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    protected function populateState($ordering = 'po.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = Factory::getApplication();
        $input = $app->getInput();
        $this->setState(
            'filter.search',
            $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string')
        );
        $this->setState(
            'filter.state',
            $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string')
        );
        $this->setState('filter.pid', $input->getInt('pid'));
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $eventCount = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_position_eventtype', 'pe'))
            ->where($db->quoteName('pe.position_id') . ' = ' . $db->quoteName('po.id'));
        $statCount = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_position_statistic', 'ps'))
            ->where($db->quoteName('ps.position_id') . ' = ' . $db->quoteName('po.id'));

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pt') . '.*',
                $db->quoteName('pt.id', 'positiontoolid'),
                $db->quoteName('po') . '.*',
                $db->quoteName('po.name', 'name'),
                $db->quoteName('pid.name', 'parent_name'),
                '(' . $eventCount . ') AS ' . $db->quoteName('countEvents'),
                '(' . $statCount . ') AS ' . $db->quoteName('countStats'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_position', 'pt'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_position', 'po')
                . ' ON ' . $db->quoteName('pt.position_id') . ' = ' . $db->quoteName('po.id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_position', 'pid')
                . ' ON ' . $db->quoteName('po.parent_id') . ' = ' . $db->quoteName('pid.id')
            )
            ->where($db->quoteName('pt.project_id') . ' = ' . (int) $this->getState('filter.pid'));

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('LOWER(' . $db->quoteName('po.name') . ') LIKE LOWER(' . $token . ')');
        }

        $orderMap = [
            'po.name' => $db->quoteName('po.name'),
            'name' => $db->quoteName('po.name'),
            'po.parent_id' => $db->quoteName('po.parent_id'),
            'parent_id' => $db->quoteName('po.parent_id'),
            'po.id' => $db->quoteName('po.id'),
            'id' => $db->quoteName('po.id'),
            'pt.id' => $db->quoteName('pt.id'),
            'positiontoolid' => $db->quoteName('pt.id'),
        ];
        $ordering = (string) $this->getState('list.ordering', 'po.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($orderMap[$ordering] ?? $orderMap['po.name']) . ' ' . $direction);

        return $query;
    }
}
