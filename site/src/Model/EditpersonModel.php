<?php
/**
 * Joomla 5/6 frontend model for editing persons.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PersonTable;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\AdminModel;

/** Joomla 5/6 frontend model for editing persons. */
final class EditpersonModel extends AdminModel
{
    public $latitude = null;
    public $longitude = null;
    public $name = 'editperson';

    private ?PersonTable $person = null;

    private function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    /**
     * Merge the native Joomla jform payload while preserving the original ID
     * and the legacy request fields (including explicit zero/empty values).
     */
    public function updItem($data): bool
    {
        $data = (array) $data;
        $id = self::positiveId($data['id'] ?? null);

        if ($id <= 0) {
            $this->setError(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
            return false;
        }

        $request = is_array($data['request'] ?? null) ? $data['request'] : [];
        $form = is_array($data['jform'] ?? null) ? $data['jform'] : [];

        unset($data['request'], $data['jform']);
        unset($request['id'], $form['id']);

        // jform fields take precedence, but never let nested data change
        // the record ID chosen by the editor form.
        $data = array_replace($data, $request, $form);
        $data['id'] = $id;

        try {
            $table = $this->getTable('player');

            if (!$table->bind($data) || !$table->check()) {
                return false;
            }

            return (bool) $table->store();
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'jsmerror');
            $this->setError(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));

            return false;
        }
    }

    /** Reject malformed, negative and overflowing person record IDs. */
    private static function positiveId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $raw = trim((string) $value);

        if ($raw === '' || !ctype_digit($raw)) {
            return 0;
        }

        $digits = ltrim($raw, '0');
        $digits = $digits === '' ? '0' : $digits;
        $max = (string) PHP_INT_MAX;

        if ($digits === '0'
            || strlen($digits) > strlen($max)
            || (strlen($digits) === strlen($max) && strcmp($digits, $max) > 0)) {
            return 0;
        }

        return (int) $digits;
    }

    public function getTable($type = 'player', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (in_array(strtolower((string) $type), ['player', 'person'], true)) {
            return new PersonTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    public function getForm($data = [], $loadData = true)
    {
        FormHelper::addFormPath(JPATH_SITE . '/components/com_sportsmanagement/models/forms');
        FormHelper::addFieldPrefix('Diddipoeler\\Component\\SportsManagement\\Administrator\\Field');

        $form = $this->loadForm(
            'com_sportsmanagement.' . $this->name,
            $this->name,
            ['load_data' => $loadData]
        );

        if (!$form) {
            return false;
        }

        $params = ComponentHelper::getParams('com_sportsmanagement');
        $mediaTool = trim((string) $params->get('cfg_which_media_tool', 'media'));

        if ($mediaTool === '' || ctype_digit($mediaTool)) {
            $mediaTool = 'media';
        }

        if ($form->getField('picture')) {
            $form->setFieldAttribute('picture', 'default', (string) $params->get('ph_player', ''));
            $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/persons');
            $form->setFieldAttribute('picture', 'type', $mediaTool);
        }

        return $form;
    }

    protected function loadFormData()
    {
        $app = $this->siteApplication();
        $data = $app->getUserState('com_sportsmanagement.edit.' . $this->name . '.data', []);

        if (empty($data)) {
            $data = $this->getData();
        }

        return $data;
    }

    public function getData(): PersonTable
    {
        if ($this->person === null) {
            $personId = $this->siteApplication()->getInput()->getInt('id', 0);
            $this->person = $this->getTable('player');

            if ($personId > 0) {
                $this->person->load($personId);
            }
        }

        return $this->person;
    }
}
