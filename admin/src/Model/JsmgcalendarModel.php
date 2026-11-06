<?php
/**
 * Joomla 5/6 administrator Google Calendar form model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\JsmgcalendarTable;
use Joomla\Registry\Registry;

/** Native Joomla 5/6 administrator form model for Google calendars. */
final class JsmgcalendarModel extends SportsManagementAdminModel
{
    /**
     * Resolve the native Google Calendar table for administrator edits.
     * @param string $type Requested table type.
     * @param string $prefix Legacy Joomla table namespace.
     * @param array $config Optional table settings.
     * @return mixed Native JsmgcalendarTable or parent result.
     */
    public function getTable($type = 'jsmGCalendar', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'jsmGCalendar') === 0) {
            return new JsmgcalendarTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Save a Google calendar record through Joomla native form persistence.
     * Avoids obsolete GData Atom templates in the site temporary directory.
     * Google authentication and imports use the current OAuth service.
     * @param array $data Submitted calendar values.
     * @return bool Whether Joomla saved the calendar.
     */
    public function save($data)
    {
        return parent::save($data);
    }

    /**
     * Serialise submitted calendar extended settings for persistence.
     * @param array $data Submitted calendar fields.
     * @return array Prepared calendar data.
     */
    protected function prepareSportsManagementData(array $data): array
    {
        $post = $this->administratorApplication()->getInput()->post->getArray();

        if (array_key_exists('extended', $post) && is_array($post['extended'])) {
            $params = new Registry();
            $params->loadArray($post['extended']);
            $data['params'] = $params->toString();
        }

        return parent::prepareSportsManagementData($data);
    }

    /**
     * Check permission to edit the selected Google calendar record.
     * Uses the standard Joomla model ACL fallback where applicable.
     * @param array $data Current calendar fields.
     * @param string $key Record identifier field.
     * @return bool Whether the current identity may edit.
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        $id = (int) ($data[$key] ?? 0);

        $identity = $this->administratorApplication()->getIdentity();

        return ($identity !== null && $identity->authorise(
            'core.edit',
            'com_sportsmanagement.calendar.' . $id
        )) || parent::allowEdit($data, $key);
    }

    /**
     * Load pending Google calendar editor form state or model item.
     * Supports both current and historical user-state keys.
     * @return mixed Selected calendar form values.
     */
    protected function loadFormData()
    {
        $app = $this->administratorApplication();
        $data = $app->getUserState('com_sportsmanagement.edit.jsmgcalendar.data', []);

        if (empty($data)) {
            $data = $app->getUserState('com_sportsmanagement.edit.jsmGCalendar.data', []);
        }

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

}
