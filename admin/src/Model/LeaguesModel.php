<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

final class LeaguesModel extends SportsManagementListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'obj.name', 'name',
            'obj.short_name', 'short_name',
            'obj.country', 'country',
            'obj.published_act_season', 'published_act_season',
            'obj.champions_complete', 'champions_complete',
            'obj.league_level', 'league_level',
            'obj.published', 'published', 'state',
            'obj.ordering', 'ordering',
            'obj.id', 'id',
            'st.name', 'sportstype',
            'ag.name', 'agegroup',
            'fed.name', 'fedname',
        ];

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'obj.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = $this->administratorApplication();
        $input = $app->getInput();

        $legacy = [
            'search_nation' => 'filter_search_nation',
            'search_agegroup' => 'filter_search_agegroup',
            'search_league_level' => 'filter_search_league_level',
            'search_champions_complete' => 'filter_search_champions_complete',
            'search_associations_leagues' => 'filter_search_associations_leagues',
            'search_federation' => 'filter_search_federation',
        ];

        foreach ($legacy as $stateName => $inputName) {
            if ((string) $this->state->get('filter.' . $stateName, '') === '') {
                $value = $input->getString($inputName, '');

                if ($value !== '') {
                    $this->setState('filter.' . $stateName, $value);
                }
            }
        }

        // Do not call getState() while Joomla is still inside populateState().
        // That would trigger lazy state initialisation again on Joomla 5/6.
        $app->setUserState(
            'com_sportsmanagement.leaguenation',
            (string) $this->state->get('filter.search_nation', '')
        );
        $app->setUserState(
            'com_sportsmanagement.leaguefederation',
            (string) $this->state->get('filter.search_federation', '')
        );
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('obj.id'),
                $db->quoteName('obj.name'),
                $db->quoteName('obj.short_name'),
                $db->quoteName('obj.alias'),
                $db->quoteName('obj.associations'),
                $db->quoteName('obj.country'),
                $db->quoteName('obj.ordering'),
                $db->quoteName('obj.picture'),
                $db->quoteName('obj.checked_out'),
                $db->quoteName('obj.checked_out_time'),
                $db->quoteName('obj.agegroup_id'),
                $db->quoteName('obj.published'),
                $db->quoteName('obj.modified'),
                $db->quoteName('obj.modified_by'),
                $db->quoteName('obj.published_act_season'),
                $db->quoteName('obj.league_level'),
                $db->quoteName('obj.champions_complete'),
                $db->quoteName('st.name', 'sportstype'),
                $db->quoteName('uc.name', 'editor'),
                $db->quoteName('ag.name', 'agegroup'),
                $db->quoteName('fed.name', 'fedname'),
            ])
            ->from($db->quoteName('#__sportsmanagement_league', 'obj'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_sports_type', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('obj.sports_type_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'uc')
                . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('obj.checked_out')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_agegroup', 'ag')
                . ' ON ' . $db->quoteName('ag.id') . ' = ' . $db->quoteName('obj.agegroup_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_associations', 'fed')
                . ' ON ' . $db->quoteName('fed.id') . ' = ' . $db->quoteName('obj.associations')
            );

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where(
                '(' . $db->quoteName('obj.name') . ' LIKE ' . $token
                . ' OR ' . $db->quoteName('obj.short_name') . ' LIKE ' . $token . ')'
            );
        }

        $country = trim((string) $this->getState('filter.search_nation'));

        if ($country !== '') {
            $query->where($db->quoteName('obj.country') . ' = ' . $db->quote($country));
        }

        $association = (int) $this->getState('filter.search_associations_leagues');

        if ($association > 0) {
            $query->where($db->quoteName('obj.associations') . ' = ' . $association);
        }

        $federation = (int) $this->getState('filter.search_federation');

        if ($federation > 0) {
            $query
                ->join(
                    'LEFT',
                    $db->quoteName('#__sportsmanagement_countries', 'co')
                    . ' ON ' . $db->quoteName('co.alpha3') . ' = ' . $db->quoteName('obj.country')
                )
                ->where($db->quoteName('co.federation') . ' = ' . $federation);
        }

        $agegroup = (int) $this->getState('filter.search_agegroup');

        if ($agegroup > 0) {
            $query->where($db->quoteName('obj.agegroup_id') . ' = ' . $agegroup);
        }

        $leagueLevel = (int) $this->getState('filter.search_league_level');

        if ($leagueLevel > 0) {
            $query->where($db->quoteName('obj.league_level') . ' = ' . $leagueLevel);
        }

        $champions = $this->getState('filter.search_champions_complete');

        if ($champions !== '' && is_numeric($champions)) {
            $query->where($db->quoteName('obj.champions_complete') . ' = ' . (int) $champions);
        }

        $state = $this->getState('filter.state');

        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('obj.published') . ' = ' . (int) $state);
        }

        $map = [
            'obj.name' => $db->quoteName('obj.name'),
            'name' => $db->quoteName('obj.name'),
            'obj.short_name' => $db->quoteName('obj.short_name'),
            'short_name' => $db->quoteName('obj.short_name'),
            'obj.country' => $db->quoteName('obj.country'),
            'country' => $db->quoteName('obj.country'),
            'obj.published_act_season' => $db->quoteName('obj.published_act_season'),
            'published_act_season' => $db->quoteName('obj.published_act_season'),
            'obj.champions_complete' => $db->quoteName('obj.champions_complete'),
            'champions_complete' => $db->quoteName('obj.champions_complete'),
            'obj.league_level' => $db->quoteName('obj.league_level'),
            'league_level' => $db->quoteName('obj.league_level'),
            'obj.published' => $db->quoteName('obj.published'),
            'published' => $db->quoteName('obj.published'),
            'state' => $db->quoteName('obj.published'),
            'obj.ordering' => $db->quoteName('obj.ordering'),
            'ordering' => $db->quoteName('obj.ordering'),
            'obj.id' => $db->quoteName('obj.id'),
            'id' => $db->quoteName('obj.id'),
            'st.name' => $db->quoteName('st.name'),
            'sportstype' => $db->quoteName('st.name'),
            'ag.name' => $db->quoteName('ag.name'),
            'agegroup' => $db->quoteName('ag.name'),
            'fed.name' => $db->quoteName('fed.name'),
            'fedname' => $db->quoteName('fed.name'),
        ];

        $ordering = (string) $this->getState('list.ordering', 'obj.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($map[$ordering] ?? $map['obj.name']) . ' ' . $direction);

        return $query;
    }

    public function getLeagues(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('league_level'),
            ])
            ->from($db->quoteName('#__sportsmanagement_league'))
            ->order($db->quoteName('name') . ' ASC');

        $country = trim((string) $this->getState('filter.search_nation'));

        if ($country !== '') {
            $query->where($db->quoteName('country') . ' = ' . $db->quote($country));
        }

        $association = (int) $this->getState('filter.search_associations_leagues');

        if ($association > 0) {
            $query->where($db->quoteName('associations') . ' = ' . $association);
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getInlineOptions(): array
    {
        $db = $this->getDatabase();

        $load = static function ($db, string $table, string $valueField, string $textField, string $orderField): array {
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName($valueField, 'value'),
                    $db->quoteName($textField, 'text'),
                ])
                ->from($db->quoteName($table))
                ->order($db->quoteName($orderField));
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        };

        return [
            'countries' => $load($db, '#__sportsmanagement_countries', 'alpha3', 'name', 'name'),
            'associations' => $load($db, '#__sportsmanagement_associations', 'id', 'name', 'name'),
            'agegroups' => $load($db, '#__sportsmanagement_agegroup', 'id', 'name', 'name'),
        ];
    }
}
