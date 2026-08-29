<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Native Joomla 5/6 list model for project divisions.
 */
class DivisionsModel extends SportsManagementListModel
{
    private int $projectId = 0;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'dv.name', 'name',
            'dv.alias', 'alias',
            'dv.id', 'id',
            'dv.ordering', 'ordering',
            'dv.published', 'published',
            'parent_name',
        ];

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'dv.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = $this->administratorApplication();
        $input = $app->getInput();
        $projectId = $input->getInt('pid');

        if ($projectId <= 0) {
            $projectId = (int) $app->getUserState('com_sportsmanagement.pid', 0);
        }

        $this->projectId = max(0, $projectId);
        $app->setUserState('com_sportsmanagement.pid', $this->projectId);
        $this->setState('project.id', $this->projectId);

        $this->setState(
            'filter.search',
            $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string')
        );
        $this->setState(
            'filter.state',
            $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string')
        );

        $order = $app->getUserStateFromRequest(
            $this->context . '.filter_order',
            'filter_order',
            'dv.name',
            'cmd'
        );
        $direction = strtoupper((string) $app->getUserStateFromRequest(
            $this->context . '.filter_order_Dir',
            'filter_order_Dir',
            'ASC',
            'cmd'
        ));

        $this->setState('list.ordering', in_array($order, $this->filter_fields, true) ? $order : 'dv.name');
        $this->setState('list.direction', $direction === 'DESC' ? 'DESC' : 'ASC');
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true);
        $projectId = (int) $this->getState('project.id', $this->projectId);

        $query
            ->select([
                $db->quoteName('dv.id'),
                $db->quoteName('dv.project_id'),
                $db->quoteName('dv.parent_id'),
                $db->quoteName('dv.name'),
                $db->quoteName('dv.alias'),
                $db->quoteName('dv.shortname'),
                $db->quoteName('dv.picture'),
                $db->quoteName('dv.ordering'),
                $db->quoteName('dv.published'),
                $db->quoteName('dv.checked_out'),
                $db->quoteName('dv.checked_out_time'),
                $db->quoteName('dvp.name', 'parent_name'),
                $db->quoteName('u.name', 'editor'),
            ])
            ->from($db->quoteName('#__sportsmanagement_division', 'dv'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_division', 'dvp') . ' ON ' . $db->quoteName('dvp.id') . ' = ' . $db->quoteName('dv.parent_id'))
            ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('dv.checked_out'))
            ->where($db->quoteName('dv.project_id') . ' = ' . $projectId);

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('LOWER(' . $db->quoteName('dv.name') . ') LIKE LOWER(' . $token . ')');
        }

        $state = $this->getState('filter.state');

        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('dv.published') . ' = ' . (int) $state);
        }

        $ordering = (string) $this->getState('list.ordering', 'dv.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $orderMap = [
            'dv.name' => $db->quoteName('dv.name'),
            'name' => $db->quoteName('dv.name'),
            'dv.alias' => $db->quoteName('dv.alias'),
            'alias' => $db->quoteName('dv.alias'),
            'dv.id' => $db->quoteName('dv.id'),
            'id' => $db->quoteName('dv.id'),
            'dv.ordering' => $db->quoteName('dv.ordering'),
            'ordering' => $db->quoteName('dv.ordering'),
            'dv.published' => $db->quoteName('dv.published'),
            'published' => $db->quoteName('dv.published'),
            'parent_name' => $db->quoteName('dvp.name'),
        ];

        $query->order(($orderMap[$ordering] ?? $orderMap['dv.name']) . ' ' . $direction);

        return $query;
    }

    public function getProjectId(): int
    {
        $this->getState();

        return (int) $this->getState('project.id', $this->projectId);
    }

    public function getProject(): ?object
    {
        $projectId = $this->getProjectId();

        if ($projectId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p.id'),
                $db->quoteName('p.name'),
                $db->quoteName('p.alias'),
                $db->quoteName('p.project_type'),
                $db->quoteName('p.published'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->where($db->quoteName('p.id') . ' = ' . $projectId);

        $db->setQuery($query);

        return $db->loadObject() ?: null;
    }

    public function getDivisions(int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_division'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->order($db->quoteName('name') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList('value');
    }

    public function getProjectDivisionsCount(int $projectId): int
    {
        if ($projectId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_division', 'd'))
            ->where($db->quoteName('d.project_id') . ' = ' . $projectId);

        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . (int) $this->getState('project.id');
        $id .= ':' . (string) $this->getState('filter.search');
        $id .= ':' . (string) $this->getState('filter.state');

        return parent::getStoreId($id);
    }
}
