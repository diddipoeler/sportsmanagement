<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Native Joomla 5/6 list model for age groups.
 */
class AgegroupsModel extends SportsManagementListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'obj.name', 'name',
            'obj.alias', 'alias',
            'obj.age_from', 'age_from',
            'obj.age_to', 'age_to',
            'obj.deadline_day', 'deadline_day',
            'obj.country', 'country',
            'obj.sportstype_id', 'sportstype_id',
            'sportstype',
            'obj.id', 'id',
            'obj.ordering', 'ordering',
            'obj.published', 'published',
            'obj.modified', 'modified',
        ];

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'obj.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = $this->administratorApplication();

        $this->setState(
            'filter.search',
            $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string')
        );
        $this->setState(
            'filter.state',
            $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string')
        );
        $this->setState(
            'filter.sports_type',
            $app->getUserStateFromRequest($this->context . '.filter.sports_type', 'filter_sports_type', '', 'int')
        );
        $this->setState(
            'filter.search_nation',
            $app->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', '', 'cmd')
        );

        $order = $app->getUserStateFromRequest(
            $this->context . '.filter_order',
            'filter_order',
            'obj.name',
            'cmd'
        );
        $direction = strtoupper((string) $app->getUserStateFromRequest(
            $this->context . '.filter_order_Dir',
            'filter_order_Dir',
            'ASC',
            'cmd'
        ));

        $this->setState('list.ordering', in_array($order, $this->filter_fields, true) ? $order : 'obj.name');
        $this->setState('list.direction', $direction === 'DESC' ? 'DESC' : 'ASC');
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        $query
            ->select([
                $db->quoteName('obj.id'),
                $db->quoteName('obj.name'),
                $db->quoteName('obj.alias'),
                $db->quoteName('obj.age_from'),
                $db->quoteName('obj.age_to'),
                $db->quoteName('obj.deadline_day'),
                $db->quoteName('obj.country'),
                $db->quoteName('obj.sportstype_id'),
                $db->quoteName('obj.picture'),
                $db->quoteName('obj.ordering'),
                $db->quoteName('obj.published'),
                $db->quoteName('obj.modified'),
                $db->quoteName('obj.modified_by'),
                $db->quoteName('obj.checked_out'),
                $db->quoteName('obj.checked_out_time'),
                $db->quoteName('obj.extended'),
                $db->quoteName('st.name', 'sportstype'),
                $db->quoteName('uc.name', 'editor'),
            ])
            ->from($db->quoteName('#__sportsmanagement_agegroup', 'obj'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_sports_type', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('obj.sportstype_id'))
            ->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('obj.checked_out'));

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('LOWER(' . $db->quoteName('obj.name') . ') LIKE LOWER(' . $token . ')');
        }

        $country = (string) $this->getState('filter.search_nation');

        if ($country !== '' && $country !== '0') {
            $query->where($db->quoteName('obj.country') . ' = ' . $db->quote($country));
        }

        $sportsType = (int) $this->getState('filter.sports_type');

        if ($sportsType > 0) {
            $query->where($db->quoteName('obj.sportstype_id') . ' = ' . $sportsType);
        }

        $state = $this->getState('filter.state');

        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('obj.published') . ' = ' . (int) $state);
        }

        $ordering = (string) $this->getState('list.ordering', 'obj.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $orderMap = [
            'obj.name' => $db->quoteName('obj.name'),
            'name' => $db->quoteName('obj.name'),
            'obj.alias' => $db->quoteName('obj.alias'),
            'alias' => $db->quoteName('obj.alias'),
            'obj.age_from' => $db->quoteName('obj.age_from'),
            'age_from' => $db->quoteName('obj.age_from'),
            'obj.age_to' => $db->quoteName('obj.age_to'),
            'age_to' => $db->quoteName('obj.age_to'),
            'obj.deadline_day' => $db->quoteName('obj.deadline_day'),
            'deadline_day' => $db->quoteName('obj.deadline_day'),
            'obj.country' => $db->quoteName('obj.country'),
            'country' => $db->quoteName('obj.country'),
            'obj.sportstype_id' => $db->quoteName('obj.sportstype_id'),
            'sportstype_id' => $db->quoteName('obj.sportstype_id'),
            'sportstype' => $db->quoteName('st.name'),
            'obj.id' => $db->quoteName('obj.id'),
            'id' => $db->quoteName('obj.id'),
            'obj.ordering' => $db->quoteName('obj.ordering'),
            'ordering' => $db->quoteName('obj.ordering'),
            'obj.published' => $db->quoteName('obj.published'),
            'published' => $db->quoteName('obj.published'),
            'obj.modified' => $db->quoteName('obj.modified'),
            'modified' => $db->quoteName('obj.modified'),
        ];

        $query->order(($orderMap[$ordering] ?? $orderMap['obj.name']) . ' ' . $direction);

        return $query;
    }

    public function getAgeGroups(string $country = '', bool $infoText = false): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('a.id', 'value'))
            ->from($db->quoteName('#__sportsmanagement_agegroup', 'a'))
            ->order($db->quoteName('a.name') . ' ASC');

        if ($infoText) {
            $query->select(
                "CONCAT(" . $db->quoteName('a.name') . ", ' von: ', " . $db->quoteName('a.age_from') . ", ' bis: ', " . $db->quoteName('a.age_to') . ", ' Stichtag: ', " . $db->quoteName('a.deadline_day') . ") AS " . $db->quoteName('text')
            );
        } else {
            $query->select(
                "CONCAT(" . $db->quoteName('a.name') . ", ' (', " . $db->quoteName('a.country') . ", ')') AS " . $db->quoteName('text')
            );
        }

        if ($country !== '') {
            $query->where($db->quoteName('a.country') . ' = ' . $db->quote($country));
        }

        $db->setQuery($query);

        return $db->loadObjectList();
    }
}
