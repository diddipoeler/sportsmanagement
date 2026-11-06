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
use Joomla\Database\ParameterType;

/**
 * Native Joomla 5/6 administrator form model for match events.
 */
final class MatcheventModel extends SportsManagementAdminModel
{
    /**
     * Load the administrator form used to edit match events.
     * Registers the native and legacy component form directories.
     * @param array $data Optional form data.
     * @param bool $loadData Whether to load persisted form values.
     * @return mixed Joomla form object or failure result.
     */
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
        $playerName = 'CONCAT('
            . $db->quoteName('t1.firstname') . ', '
            . $db->quote(" '") . ', '
            . $db->quoteName('t1.nickname') . ', '
            . $db->quote("' ") . ', '
            . $db->quoteName('t1.lastname') . ') AS '
            . $db->quoteName('player1');
        $query = $db->createQuery()
            ->select([
                'me.*',
                $db->quoteName('t.name', 'team'),
                $db->quoteName('et.name', 'event'),
                $playerName,
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
            ->where($db->quoteName('me.match_id') . ' = :matchId')
            ->bind(':matchId', $matchId, ParameterType::INTEGER)
            ->order($db->quoteName('me.event_time') . ' ASC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return [];
        }
    }

    /**
     * Persist the ordering of selected match events.
     * Loads each event row and updates only changed ordering values.
     * @param array|null $pks Event record identifiers.
     * @param array|null $order New order values aligned with identifiers.
     * @return bool True when all writable ordering changes succeed.
     */
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

    /**
     * Resolve the native match event table via Joomla MVC.
     * @param string $type Requested table name.
     * @param string $prefix Legacy Joomla table prefix.
     * @param array $config Optional table factory settings.
     * @return mixed Native MatcheventTable or parent table result.
     */
    public function getTable($type = 'matchevent', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'matchevent') === 0) {
            return new MatcheventTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Determine whether the current administrator can edit a match event.
     * Falls back to the standard model ACL check when a record-specific
     * permission is not available.
     * @param array $data Current event record values.
     * @param string $key Record identifier property.
     * @return bool Whether the event can be edited.
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        $id = (int) ($data[$key] ?? 0);

        $identity = $this->administratorApplication()->getIdentity();

        return ($identity !== null && $identity->authorise(
            'core.edit',
            'com_sportsmanagement.message.' . $id
        )) || parent::allowEdit($data, $key);
    }
}
