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
    public function getTable($type = 'jsmGCalendar', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'jsmGCalendar') === 0) {
            return new JsmgcalendarTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Use Joomla's native form persistence without writing obsolete Google
     * GData Atom templates to a shared site tmp directory.
     *
     * Google Calendar authentication and imports now use the OAuth service;
     * createcal.xml is neither consumed by the Joomla 5/6 workflow nor
     * required to create a calendar record.
     */
    public function save($data)
    {
        return parent::save($data);
    }

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

    protected function allowEdit($data = [], $key = 'id')
    {
        $id = (int) ($data[$key] ?? 0);

        return $this->administratorApplication()->getIdentity()->authorise(
            'core.edit',
            'com_sportsmanagement.calendar.' . $id
        ) || parent::allowEdit($data, $key);
    }

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
