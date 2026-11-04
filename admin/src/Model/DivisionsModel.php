<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 * @version     5.6.0
 * @author      diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright   Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;

/**
 * Native Joomla 5/6 list model for project divisions.
 */
class DivisionsModel extends SportsManagementListModel
{
    private int $projectId = 0;

    /**
     * Configure sortable columns for the project-divisions list.
     * Retains caller configuration while registering division properties
     * and their aliases with the native Joomla list-model constructor.
     * @param array $config Joomla list-model configuration.
     * @param MVCFactoryInterface|null $factory Optional MVC factory.
     * @return void
     */
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

    /**
     * Restore the selected project and division-list filters.
     * Obtains the project selection and user-backed search/order state
     * from the administrator application before querying divisions.
     * @param string $ordering Default division-list column.
     * @param string $direction Default sorting direction.
     * @return void
     */
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

    /**
     * Build a project-scoped database query for the divisions list.
     * Joins related division information, filters by selected project
     * and publication/search settings, and applies validated ordering.
     * @return \Joomla\Database\DatabaseQuery Query for the list view.
     */
    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->createQuery();
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
            ->where($db->quoteName('dv.project_id') . ' = :divisionProjectId')
            ->bind(':divisionProjectId', $projectId, ParameterType::INTEGER);

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = '%' . $db->escape($search, true) . '%';
            $query->where('LOWER(' . $db->quoteName('dv.name') . ') LIKE LOWER(:divisionSearch)')
                ->bind(':divisionSearch', $token, ParameterType::STRING);
        }

        $state = $this->getState('filter.state');

        if ($state !== '' && is_numeric($state)) {
            $published = (int) $state;
            $query->where($db->quoteName('dv.published') . ' = :divisionPublished')
                ->bind(':divisionPublished', $published, ParameterType::INTEGER);
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

    /**
     * Return the project identifier selected for division administration.
     * Ensures the model's lazy Joomla state has been initialised before
     * reading the project ID shared by the list and edit actions.
     * @return int Selected project identifier, or zero.
     */
    public function getProjectId(): int
    {
        $this->getState();

        return (int) $this->getState('project.id', $this->projectId);
    }

    /**
     * Load the project associated with the current division selection.
     * Returns null for a missing project identifier instead of querying
     * an unrestricted or unrelated project record.
     * @return object|null Project record when the selection is valid.
     */
    public function getProject(): ?object
    {
        $projectId = $this->getProjectId();

        if ($projectId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('p.id'),
                $db->quoteName('p.name'),
                $db->quoteName('p.alias'),
                $db->quoteName('p.project_type'),
                $db->quoteName('p.published'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->where($db->quoteName('p.id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);

        $db->setQuery($query);

        return $db->loadObject() ?: null;
    }

    /**
     * Build ID/name selector options for divisions in one project.
     * Rejects nonpositive project IDs and restricts database rows to
     * the selected project before returning Joomla select options.
     * @param int $projectId Project whose divisions may be selected.
     * @return array<int,object> Project-specific division options.
     */
    public function getDivisions(int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_division'))
            ->where($db->quoteName('project_id') . ' = :divisionsProjectId')
            ->bind(':divisionsProjectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('name') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList('value');
    }

    /**
     * Count divisions belonging to the requested project.
     * Performs a project-scoped count query and returns zero when no
     * valid positive project identifier is supplied.
     * @param int $projectId Project to count division records for.
     * @return int Number of matching divisions.
     */
    public function getProjectDivisionsCount(int $projectId): int
    {
        if ($projectId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_division', 'd'))
            ->where($db->quoteName('d.project_id') . ' = :divisionCountProjectId')
            ->bind(':divisionCountProjectId', $projectId, ParameterType::INTEGER);

        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /**
     * Differentiate Joomla list-cache entries by project and filter state.
     * Adds project, search and publication selectors to the inherited
     * store identifier so unrelated backend selections are not mixed.
     * @param string $id Parent list-model cache key.
     * @return string State-specific cache key.
     */
    protected function getStoreId($id = '')
    {
        $id .= ':' . (int) $this->getState('project.id');
        $id .= ':' . (string) $this->getState('filter.search');
        $id .= ':' . (string) $this->getState('filter.state');

        return parent::getStoreId($id);
    }
}
