<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

final class ClubnamesModel extends SportsManagementListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'obj.name', 'name',
            'obj.name_long', 'name_long',
            'obj.country', 'country',
            'obj.published', 'published', 'state',
            'obj.ordering', 'ordering',
            'obj.id', 'id',
        ];

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'obj.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        // Do not re-enter Joomla's lazy getState() initialisation while populateState() is running.
        if ((string) $this->state->get('filter.search_nation', '') === '') {
            $legacyCountry = $this->administratorApplication()->getInput()->getString('filter_search_nation');

            if ($legacyCountry !== '') {
                $this->setState('filter.search_nation', $legacyCountry);
            }
        }
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('obj.id'),
                $db->quoteName('obj.name'),
                $db->quoteName('obj.name_long'),
                $db->quoteName('obj.country'),
                $db->quoteName('obj.published'),
                $db->quoteName('obj.ordering'),
                $db->quoteName('obj.checked_out'),
                $db->quoteName('obj.checked_out_time'),
                $db->quoteName('obj.modified'),
                $db->quoteName('obj.modified_by'),
                $db->quoteName('uc.name', 'editor'),
            ])
            ->from($db->quoteName('#__sportsmanagement_club_names', 'obj'))
            ->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('obj.checked_out'));

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where(
                '(' . $db->quoteName('obj.name') . ' LIKE ' . $token
                . ' OR ' . $db->quoteName('obj.name_long') . ' LIKE ' . $token . ')'
            );
        }

        $country = trim((string) $this->getState('filter.search_nation'));

        if ($country !== '') {
            $query->where($db->quoteName('obj.country') . ' = ' . $db->quote($country));
        }

        $state = $this->getState('filter.state');

        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('obj.published') . ' = ' . (int) $state);
        }

        $map = [
            'obj.name' => $db->quoteName('obj.name'),
            'name' => $db->quoteName('obj.name'),
            'obj.name_long' => $db->quoteName('obj.name_long'),
            'name_long' => $db->quoteName('obj.name_long'),
            'obj.country' => $db->quoteName('obj.country'),
            'country' => $db->quoteName('obj.country'),
            'obj.published' => $db->quoteName('obj.published'),
            'published' => $db->quoteName('obj.published'),
            'state' => $db->quoteName('obj.published'),
            'obj.ordering' => $db->quoteName('obj.ordering'),
            'ordering' => $db->quoteName('obj.ordering'),
            'obj.id' => $db->quoteName('obj.id'),
            'id' => $db->quoteName('obj.id'),
        ];

        $ordering = (string) $this->getState('list.ordering', 'obj.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($map[$ordering] ?? $map['obj.name']) . ' ' . $direction);

        return $query;
    }

    public function getClubNames(string $country = ''): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('name'), $db->quoteName('name_long')])
            ->from($db->quoteName('#__sportsmanagement_club_names'))
            ->order($db->quoteName('name'));

        if ($country !== '') {
            $query->where($db->quoteName('country') . ' = ' . $db->quote($country));
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }
}
