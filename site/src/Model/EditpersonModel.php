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
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
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

    private function siteApplication(): SiteApplication
    {
        return Factory::getContainer()->get(SiteApplication::class);
    }

    public function updItem($data): bool
    {
        foreach ((array) ($data['request'] ?? []) as $key => $value) {
            $data[$key] = $value;
        }

        // Preserve the historical frontend editor behaviour: empty/zero values were filtered before binding.
        $data = array_filter((array) $data);

        try {
            $table = $this->getTable('player');

            if (!$table->bind($data)) {
                return false;
            }

            if (!$table->check()) {
                return false;
            }

            return (bool) $table->store();
        } catch (\Throwable $e) {
            Log::add($e->getMessage(), Log::ERROR, 'jsmerror');
            $this->siteApplication()->enqueueMessage(
                Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
                'error'
            );
            return false;
        }
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