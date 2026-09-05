<?php
/**
 * Native Joomla 5/6 administrator model for match events.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatcheventTable;
use Joomla\CMS\Form\Form;

/**
 * Native Joomla 5/6 administrator form model for match events.
 */
final class MatcheventModel extends SportsManagementAdminModel
{
    public function getForm($data = [], $loadData = true)
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/forms');

        return $this->loadForm(
            'com_sportsmanagement.matchevent',
            'matchevent',
            ['control' => 'jform', 'load_data' => $loadData]
        );
    }

    /** @return array<int,object> */
    public function getMatchEvents(int $matchId): array
    {
        if ($matchId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                'me.*',
                $db->quoteName('t.name', 'team'),
                $db->quoteName('et.name', 'event'),
                "CONCAT(t1.firstname, ' \\'', t1.nickname, '\\' ', t1.lastname) AS player1",
            ])
            ->from($db->quoteName('#__sportsmanagement_match_event', 'me'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp1')
                . ' ON ' . $db->quoteName('tp1.id') . ' = ' . $db->quoteName('me.teamplayer_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st1')
                . ' ON ' . $db->quoteName('st1.team_id') . ' = ' . $db->quoteName('tp1.team_id')
                . ' AND ' . $db->quoteName('st1.season_id') . ' = ' . $db->quoteName('tp1.season_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_person', 't1')
                . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('tp1.person_id')
                . ' AND ' . $db->quoteName('t1.published') . ' = 1'
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st1.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_eventtype', 'et')
                . ' ON ' . $db->quoteName('et.id') . ' = ' . $db->quoteName('me.event_type_id')
            )
            ->where($db->quoteName('me.match_id') . ' = ' . $matchId)
            ->order($db->quoteName('me.event_time') . ' ASC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return [];
        }
    }

    public function saveorder($pks = null, $order = null)
    {
        $pks = array_values((array) $pks);
        $order = array_values((array) $order);
        $row = $this->getTable();

        foreach ($pks as $index => $pk) {
            if (!array_key_exists($index, $order) || !$row->load((int) $pk)) {
                continue;
            }

            $ordering = (int) $order[$index];

            if ((int) $row->ordering === $ordering) {
                continue;
            }

            $row->ordering = $ordering;

            if (!$row->store()) {
                $this->setError((string) $row->getError());

                return false;
            }
        }

        return true;
    }

    public function getTable($type = 'matchevent', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'matchevent') === 0) {
            return new MatcheventTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    protected function allowEdit($data = [], $key = 'id')
    {
        $id = (int) ($data[$key] ?? 0);

        return $this->administratorApplication()->getIdentity()->authorise(
            'core.edit',
            'com_sportsmanagement.message.' . $id
        ) || parent::allowEdit($data, $key);
    }
}
