<?php
/**
 * Joomla 5/6 administrator roster-positions list model.
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

final class RosterpositionsModel extends SportsManagementListModel
{
    /**
     * Initialise the administrator roster-position list model.
     * Registers supported filter fields and safe ordering columns.
     * @param array $config Joomla list model configuration.
     * @param MVCFactoryInterface|null $factory Optional Joomla MVC factory.
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'obj.name', 'name',
            'obj.short_name', 'short_name',
            'obj.country', 'country',
            'obj.players', 'players',
            'obj.published', 'published', 'state',
            'obj.ordering', 'ordering',
            'obj.id', 'id',
        ];

        parent::__construct($config, $factory);
    }

    /**
     * Load roster-position search and publication filters.
     * Preserves the historical filter_published request parameter
     * for compatibility with older administrator list URLs.
     * @param string $ordering Default roster-position sort field.
     * @param string $direction Default sort direction.
     * @return void
     */
    protected function populateState($ordering = 'obj.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = $this->administratorApplication();
        $input = $app->getInput();

        $this->setState(
            'filter.search',
            $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string')
        );

        $state = $app->getUserStateFromRequest(
            $this->context . '.filter.state',
            'filter_state',
            '',
            'string'
        );

        // Preserve the legacy filter name used by older administrator links.
        if ($state === '') {
            $legacyState = $input->getString('filter_published', '');

            if ($legacyState !== '') {
                $state = $legacyState;
            }
        }

        $this->setState('filter.state', $state);
    }

    /**
     * Build the filtered and sorted roster-position database query.
     * Selects roster positions and editor names, applies bound search
     * terms and limits sort columns to configured fields.
     * @return mixed Joomla query for roster-position records.
     */
    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('obj.id'),
                $db->quoteName('obj.name'),
                $db->quoteName('obj.alias'),
                $db->quoteName('obj.country'),
                $db->quoteName('obj.ordering'),
                $db->quoteName('obj.checked_out'),
                $db->quoteName('obj.checked_out_time'),
                $db->quoteName('obj.short_name'),
                $db->quoteName('obj.picture'),
                $db->quoteName('obj.players'),
                $db->quoteName('obj.published'),
                $db->quoteName('obj.modified'),
                $db->quoteName('obj.modified_by'),
                $db->quoteName('uc.name', 'editor'),
            ])
            ->from($db->quoteName('#__sportsmanagement_rosterposition', 'obj'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'uc')
                . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('obj.checked_out')
            );

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $searchName = '%' . $db->escape($search, true) . '%';
            $searchShortName = $searchName;
            $query->where(
                '(' . $db->quoteName('obj.name') . ' LIKE :rosterSearchName'
                . ' OR ' . $db->quoteName('obj.short_name') . ' LIKE :rosterSearchShortName)'
            )
                ->bind(':rosterSearchName', $searchName, ParameterType::STRING)
                ->bind(':rosterSearchShortName', $searchShortName, ParameterType::STRING);
        }

        $state = $this->getState('filter.state');

        if ($state !== '' && is_numeric($state)) {
            $published = (int) $state;
            $query->where($db->quoteName('obj.published') . ' = :rosterPublished')
                ->bind(':rosterPublished', $published, ParameterType::INTEGER);
        }

        $map = [
            'obj.name' => $db->quoteName('obj.name'),
            'name' => $db->quoteName('obj.name'),
            'obj.short_name' => $db->quoteName('obj.short_name'),
            'short_name' => $db->quoteName('obj.short_name'),
            'obj.country' => $db->quoteName('obj.country'),
            'country' => $db->quoteName('obj.country'),
            'obj.players' => $db->quoteName('obj.players'),
            'players' => $db->quoteName('obj.players'),
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
     * Return the predefined home-team player positioning coordinates.
     * @return array<string,array<int,array>> Home roster layout coordinates.
     */
    public function getRosterHome(): array
    {
        return [
            'HOME_POS' => [
                ['heim' => ['oben' => 5, 'links' => 233]],
                ['heim' => ['oben' => 113, 'links' => 69]],
                ['heim' => ['oben' => 113, 'links' => 179]],
                ['heim' => ['oben' => 113, 'links' => 288]],
                ['heim' => ['oben' => 113, 'links' => 397]],
                ['heim' => ['oben' => 236, 'links' => 179]],
                ['heim' => ['oben' => 236, 'links' => 288]],
                ['heim' => ['oben' => 318, 'links' => 69]],
                ['heim' => ['oben' => 318, 'links' => 233]],
                ['heim' => ['oben' => 318, 'links' => 397]],
                ['heim' => ['oben' => 400, 'links' => 233]],
            ],
        ];
    }

    /**
     * Return the predefined away-team player positioning coordinates.
     * @return array<string,array<int,array>> Away roster layout coordinates.
     */
    public function getRosterAway(): array
    {
        return [
            'AWAY_POS' => [
                ['heim' => ['oben' => 970, 'links' => 233]],
                ['heim' => ['oben' => 828, 'links' => 69]],
                ['heim' => ['oben' => 828, 'links' => 179]],
                ['heim' => ['oben' => 828, 'links' => 288]],
                ['heim' => ['oben' => 828, 'links' => 397]],
                ['heim' => ['oben' => 746, 'links' => 179]],
                ['heim' => ['oben' => 746, 'links' => 288]],
                ['heim' => ['oben' => 664, 'links' => 69]],
                ['heim' => ['oben' => 664, 'links' => 397]],
                ['heim' => ['oben' => 587, 'links' => 179]],
                ['heim' => ['oben' => 587, 'links' => 288]],
            ],
        ];
    }
}
