<?php
/**
 * Native Joomla 5/6 list model for club-name aliases.
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

final class ClubnamesModel extends SportsManagementListModel
{
    /**
     * Define the database fields available for alias-list filtering.
     * Initialises the native list model with safe sort-column aliases
     * while respecting an explicitly supplied filter-fields collection.
     * @param array $config Model construction configuration.
     * @param MVCFactoryInterface|null $factory Optional MVC factory.
     * @return void
     */
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

    /**
     * Restore the club-alias list state and legacy country filter.
     * Invokes the parent list-state implementation and honours the old
     * country-filter request name without re-entering lazy state loading.
     * @param string $ordering Default alias ordering.
     * @param string $direction Default sort direction.
     * @return void
     */
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

    /**
     * Build the administrator query for alternative club names.
     * Selects club-name fields and checkout editor details, filters
     * by search text, country and state, and safely resolves sorting.
     * @return \Joomla\Database\DatabaseQuery Query for alias list pagination.
     */
    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
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
            $searchName = '%' . $search . '%';
            $searchLongName = $searchName;
            $query->where(
                '(' . $db->quoteName('obj.name') . ' LIKE :searchName'
                . ' OR ' . $db->quoteName('obj.name_long') . ' LIKE :searchLongName)'
            )
                ->bind(':searchName', $searchName, ParameterType::STRING)
                ->bind(':searchLongName', $searchLongName, ParameterType::STRING);
        }

        $country = trim((string) $this->getState('filter.search_nation'));

        if ($country !== '') {
            $query->where($db->quoteName('obj.country') . ' = :country')
                ->bind(':country', $country, ParameterType::STRING);
        }

        $state = $this->getState('filter.state');

        if ($state !== '' && is_numeric($state)) {
            $published = (int) $state;
            $query->where($db->quoteName('obj.published') . ' = :published')
                ->bind(':published', $published, ParameterType::INTEGER);
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

    /**
     * List club aliases for a selected country or for all countries.
     * Retrieves short and long club names in a stable order for use
     * in backend forms and legacy alias selectors.
     * @param string $country Optional country code to filter by.
     * @return array<int,object> Club-name records.
     */
    public function getClubNames(string $country = ''): array
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([$db->quoteName('name'), $db->quoteName('name_long')])
            ->from($db->quoteName('#__sportsmanagement_club_names'))
            ->order($db->quoteName('name'));

        if ($country !== '') {
            $query->where($db->quoteName('country') . ' = :country')
                ->bind(':country', $country, ParameterType::STRING);
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }
}
