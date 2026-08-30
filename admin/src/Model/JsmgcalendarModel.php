<?php
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

    public function save($data)
    {
        if (empty($data['id']) && !$this->writeCreateCalendarTemplate($data)) {
            return false;
        }

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

    private function writeCreateCalendarTemplate(array $data): bool
    {
        $tmpDir = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp';

        if (!is_dir($tmpDir) && !mkdir($tmpDir, 0755, true) && !is_dir($tmpDir)) {
            return false;
        }

        $offset = (string) $this->administratorApplication()->get('offset', 'UTC');
        $color = ltrim((string) ($data['color'] ?? ''), '#');
        $xml = "<entry xmlns='http://www.w3.org/2005/Atom'\n"
            . "xmlns:gd='http://schemas.google.com/g/2005'\n"
            . "xmlns:gCal='http://schemas.google.com/gCal/2005'>\n"
            . "<title type='text'>[TITLE]</title>\n"
            . "<summary type='text'>[SUMMARY]</summary>\n"
            . "<gCal:timezone value='" . htmlspecialchars($offset, ENT_QUOTES, 'UTF-8') . "'></gCal:timezone>\n"
            . "<gCal:hidden value='false'></gCal:hidden>\n"
            . "<gCal:color value='#" . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . "'></gCal:color>\n"
            . "<gd:where rel='' label='' valueString='Oakland'></gd:where>\n"
            . "</entry>\n";

        return file_put_contents(
            $tmpDir . DIRECTORY_SEPARATOR . 'createcal.xml',
            $xml,
            LOCK_EX
        ) !== false;
    }
}
