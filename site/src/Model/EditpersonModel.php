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

    public function updItem($data): bool
    {
        $data = (array) $data;
        $request = $data['request'] ?? [];

        if (is_array($request)) {
            foreach ($request as $key => $value) {
                $data[$key] = $value;
            }
        }

        unset($data['request']);

        // Keep explicit 0, false and empty-string values: administrators can
        // clear existing frontend fields and switch boolean options off.

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
            $this->setError(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
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
