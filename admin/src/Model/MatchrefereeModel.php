<?php
/**
 * Native Joomla 5/6 administrator model for match referees.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchrefereeTable;
use Joomla\CMS\Form\Form;

/**
 * Native Joomla 5/6 administrator form model for match referees.
 */
final class MatchrefereeModel extends SportsManagementAdminModel
{
    public function getForm($data = [], $loadData = true)
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/forms');

        return $this->loadForm(
            'com_sportsmanagement.matchreferee',
            'matchreferee',
            ['control' => 'jform', 'load_data' => $loadData]
        );
    }

    /** @return array<int,object> */
    public function getTeamsRefereeRoster(int $matchId): array
    {
        if ($matchId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('spi.id', 'value'),
                $db->quoteName('pr.name'),
                $db->quoteName('pr.middle_name'),
                $db->quoteName('pr.short_name'),
                $db->quoteName('pr.alias'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_referee', 'mr'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_team', 'spi')
                . ' ON ' . $db->quoteName('mr.project_referee_id') . ' = ' . $db->quoteName('spi.id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st1')
                . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('spi.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_team', 'pr')
                . ' ON ' . $db->quoteName('st1.team_id') . ' = ' . $db->quoteName('pr.id')
                . ' AND ' . $db->quoteName('pr.published') . ' = 1'
            )
            ->where($db->quoteName('mr.match_id') . ' = ' . $matchId);

        try {
            $db->setQuery($query);
            return $db->loadObjectList('value') ?: [];
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

    public function getTable($type = 'matchreferee', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'matchreferee') === 0) {
            return new MatchrefereeTable($this->getDatabase());
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
