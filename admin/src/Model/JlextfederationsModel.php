<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/** Native Joomla 5/6 administrator list model for federations. */
final class JlextfederationsModel extends SportsManagementListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'objassoc.name', 'name',
            'objassoc.short_name', 'short_name',
            'objassoc.alias', 'alias',
            'objassoc.id', 'id',
            'objassoc.ordering', 'ordering',
            'objassoc.picture', 'picture',
            'objassoc.flag_maps', 'flag_maps',
            'objassoc.assocflag', 'assocflag',
            'objassoc.published', 'published', 'state',
            'objassoc.modified', 'modified',
            'objassoc.modified_by', 'modified_by',
            'objassoc.checked_out', 'checked_out',
            'objassoc.checked_out_time', 'checked_out_time',
            'objassoc.country', 'country', 'search_nation',
        ];

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'objassoc.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $country = $this->administratorApplication()->getInput()->getString('filter_search_nation');

        if ($country !== '') {
            $this->setState('filter.search_nation', $country);
        }
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('objassoc') . '.*',
                $db->quoteName('uc.name', 'editor'),
            ])
            ->from($db->quoteName('#__sportsmanagement_federations', 'objassoc'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'uc')
                . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('objassoc.checked_out')
            );

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('LOWER(' . $db->quoteName('objassoc.name') . ') LIKE LOWER(' . $token . ')');
        }

        $country = trim((string) $this->getState('filter.search_nation'));

        if ($country !== '' && $country !== '0') {
            $query->where($db->quoteName('objassoc.country') . ' = ' . $db->quote($country));
        }

        $state = $this->getState('filter.state');

        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('objassoc.published') . ' = ' . (int) $state);
        }

        $map = [
            'objassoc.name' => $db->quoteName('objassoc.name'),
            'name' => $db->quoteName('objassoc.name'),
            'objassoc.short_name' => $db->quoteName('objassoc.short_name'),
            'short_name' => $db->quoteName('objassoc.short_name'),
            'objassoc.alias' => $db->quoteName('objassoc.alias'),
            'alias' => $db->quoteName('objassoc.alias'),
            'objassoc.id' => $db->quoteName('objassoc.id'),
            'id' => $db->quoteName('objassoc.id'),
            'objassoc.ordering' => $db->quoteName('objassoc.ordering'),
            'ordering' => $db->quoteName('objassoc.ordering'),
            'objassoc.picture' => $db->quoteName('objassoc.picture'),
            'picture' => $db->quoteName('objassoc.picture'),
            'objassoc.flag_maps' => $db->quoteName('objassoc.flag_maps'),
            'flag_maps' => $db->quoteName('objassoc.flag_maps'),
            'objassoc.assocflag' => $db->quoteName('objassoc.assocflag'),
            'assocflag' => $db->quoteName('objassoc.assocflag'),
            'objassoc.published' => $db->quoteName('objassoc.published'),
            'published' => $db->quoteName('objassoc.published'),
            'state' => $db->quoteName('objassoc.published'),
            'objassoc.modified' => $db->quoteName('objassoc.modified'),
            'modified' => $db->quoteName('objassoc.modified'),
            'objassoc.modified_by' => $db->quoteName('objassoc.modified_by'),
            'modified_by' => $db->quoteName('objassoc.modified_by'),
            'objassoc.checked_out' => $db->quoteName('objassoc.checked_out'),
            'checked_out' => $db->quoteName('objassoc.checked_out'),
            'objassoc.checked_out_time' => $db->quoteName('objassoc.checked_out_time'),
            'checked_out_time' => $db->quoteName('objassoc.checked_out_time'),
            'objassoc.country' => $db->quoteName('objassoc.country'),
            'country' => $db->quoteName('objassoc.country'),
            'search_nation' => $db->quoteName('objassoc.country'),
        ];

        $ordering = (string) $this->getState('list.ordering', 'objassoc.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($map[$ordering] ?? $map['objassoc.name']) . ' ' . $direction);

        return $query;
    }
}
