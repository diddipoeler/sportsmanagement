<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

final class PositionsModel extends SportsManagementListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'po.name', 'name',
            'po.picture', 'picture',
            'po.parent_id', 'parent_id',
            'po.sports_type_id', 'sports_type',
            'po.persontype', 'persontype',
            'po.published', 'published', 'state',
            'po.ordering', 'ordering',
            'po.modified', 'modified',
            'po.modified_by', 'modified_by',
            'po.id', 'id',
        ];

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'po.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $input = $this->administratorApplication()->getInput();

        // populateState() is still active, so use the raw state bag instead of
        // re-entering Joomla's lazy getState() initialisation.
        if (!(int) $this->state->get('filter.sports_type', 0)) {
            $legacy = $input->getInt('filter_sports_type');

            if ($legacy > 0) {
                $this->setState('filter.sports_type', $legacy);
            }
        }

        if (!(int) $this->state->get('filter.persontype', 0)) {
            $legacy = $input->getInt('filter_persontype');

            if ($legacy > 0) {
                $this->setState('filter.persontype', $legacy);
            }
        }
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('po.id'),
                $db->quoteName('po.name'),
                $db->quoteName('po.alias'),
                $db->quoteName('po.picture'),
                $db->quoteName('po.parent_id'),
                $db->quoteName('po.sports_type_id'),
                $db->quoteName('po.persontype'),
                $db->quoteName('po.published'),
                $db->quoteName('po.ordering'),
                $db->quoteName('po.checked_out'),
                $db->quoteName('po.checked_out_time'),
                $db->quoteName('po.modified'),
                $db->quoteName('po.modified_by'),
                $db->quoteName('pop.name', 'parent_name'),
                $db->quoteName('st.name', 'sportstype'),
                $db->quoteName('u.name', 'editor'),
                '(SELECT COUNT(*) FROM ' . $db->quoteName('#__sportsmanagement_position_eventtype') . ' WHERE '
                    . $db->quoteName('position_id') . ' = ' . $db->quoteName('po.id') . ') AS ' . $db->quoteName('countEvents'),
                '(SELECT COUNT(*) FROM ' . $db->quoteName('#__sportsmanagement_position_statistic') . ' WHERE '
                    . $db->quoteName('position_id') . ' = ' . $db->quoteName('po.id') . ') AS ' . $db->quoteName('countStats'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position', 'po'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_sports_type', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('po.sports_type_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_position', 'pop')
                . ' ON ' . $db->quoteName('pop.id') . ' = ' . $db->quoteName('po.parent_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u')
                . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('po.checked_out')
            );

        $search = trim((string) $this->getState('filter.search'));

        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('LOWER(' . $db->quoteName('po.name') . ') LIKE LOWER(' . $token . ')');
        }

        $state = $this->getState('filter.state');

        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('po.published') . ' = ' . (int) $state);
        }

        $sportstype = (int) $this->getState('filter.sports_type');

        if ($sportstype > 0) {
            $query->where($db->quoteName('po.sports_type_id') . ' = ' . $sportstype);
        }

        $persontype = (int) $this->getState('filter.persontype');

        if ($persontype > 0) {
            $query->where($db->quoteName('po.persontype') . ' = ' . $persontype);
        }

        $map = [
            'po.name' => $db->quoteName('po.name'),
            'name' => $db->quoteName('po.name'),
            'po.picture' => $db->quoteName('po.picture'),
            'picture' => $db->quoteName('po.picture'),
            'po.parent_id' => $db->quoteName('po.parent_id'),
            'parent_id' => $db->quoteName('po.parent_id'),
            'po.sports_type_id' => $db->quoteName('po.sports_type_id'),
            'sports_type' => $db->quoteName('po.sports_type_id'),
            'po.persontype' => $db->quoteName('po.persontype'),
            'persontype' => $db->quoteName('po.persontype'),
            'po.published' => $db->quoteName('po.published'),
            'published' => $db->quoteName('po.published'),
            'state' => $db->quoteName('po.published'),
            'po.ordering' => $db->quoteName('po.ordering'),
            'ordering' => $db->quoteName('po.ordering'),
            'po.modified' => $db->quoteName('po.modified'),
            'modified' => $db->quoteName('po.modified'),
            'po.modified_by' => $db->quoteName('po.modified_by'),
            'modified_by' => $db->quoteName('po.modified_by'),
            'po.id' => $db->quoteName('po.id'),
            'id' => $db->quoteName('po.id'),
        ];

        $ordering = (string) $this->getState('list.ordering', 'po.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($map[$ordering] ?? $map['po.name']) . ' ' . $direction);

        return $query;
    }

    public function getParentsPositions(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
                $db->quoteName('alias'),
                $db->quoteName('parent_id'),
                $db->quoteName('persontype'),
                $db->quoteName('sports_type_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position'))
            ->where($db->quoteName('parent_id') . ' = 0')
            ->order($db->quoteName('ordering') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getProjectPositions($projectId, $persontype = 1): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('ppos.id', 'value'),
                $db->quoteName('pos.name', 'text'),
                $db->quoteName('ppos.position_id', 'position_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position', 'pos'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_position', 'ppos')
                . ' ON ' . $db->quoteName('ppos.position_id') . ' = ' . $db->quoteName('pos.id')
            )
            ->where($db->quoteName('ppos.project_id') . ' = ' . (int) $projectId)
            ->where($db->quoteName('pos.persontype') . ' = ' . (int) $persontype)
            ->order($db->quoteName('pos.ordering') . ' ASC');

        $db->setQuery($query);
        $positions = $db->loadObjectList() ?: [];

        foreach ($positions as $position) {
            $position->text = Text::_($position->text);
        }

        return $positions;
    }

    public function getPositions($projectId): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pp.id', 'value'),
                $db->quoteName('p.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position', 'p'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_position', 'pp')
                . ' ON ' . $db->quoteName('pp.position_id') . ' = ' . $db->quoteName('p.id')
            )
            ->where($db->quoteName('pp.project_id') . ' = ' . (int) $projectId)
            ->order($db->quoteName('p.ordering') . ' ASC');

        $db->setQuery($query);
        $positions = $db->loadObjectList() ?: [];

        foreach ($positions as $position) {
            $position->text = Text::_($position->text);
        }

        return $positions;
    }

    public function getAllPositions(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pos.id', 'value'),
                $db->quoteName('pos.name', 'posName'),
                $db->quoteName('s.name', 'sName'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position', 'pos'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_sports_type', 's')
                . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('pos.sports_type_id')
            )
            ->where($db->quoteName('pos.published') . ' = 1')
            ->order($db->quoteName('pos.ordering') . ', ' . $db->quoteName('pos.name'));

        $db->setQuery($query);
        $items = $db->loadObjectList() ?: [];

        foreach ($items as $item) {
            $item->text = Text::_($item->posName) . ' (' . Text::_($item->sName) . ')';
        }

        return $items;
    }

    public function getPositionListSelect(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
                $db->quoteName('alias'),
                $db->quoteName('parent_id'),
                $db->quoteName('persontype'),
                $db->quoteName('sports_type_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position'))
            ->order($db->quoteName('name') . ' ASC');

        $db->setQuery($query);
        $positions = $db->loadObjectList() ?: [];

        foreach ($positions as $position) {
            $position->text = Text::_($position->text);
        }

        return $positions;
    }
}
