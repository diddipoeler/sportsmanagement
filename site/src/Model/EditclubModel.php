<?php
/**
 * Joomla 5/6 frontend model for editing clubs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\ClubTable;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;

final class EditclubModel extends AdminModel
{
    public $latitude = null;
    public $longitude = null;
    public $projectid = 0;
    public $clubid = 0;
    public $club = null;
    public $name = 'club';

    public function __construct(
        $config = [],
        ?MVCFactoryInterface $factory = null,
        ?FormFactoryInterface $formFactory = null
    ) {
        parent::__construct($config, $factory, $formFactory);

        $input = $this->siteApplication()->getInput();
        $this->projectid = $input->getInt('p', 0);
        $this->clubid = $input->getInt('cid', 0);
    }

    public function updItem($data)
    {
        if (($data['founded'] ?? '') !== '0000-00-00' && ($data['founded'] ?? '') !== '') {
            $data['founded'] = $this->normaliseFrontendDate((string) $data['founded']);
        }

        if (($data['dissolved'] ?? '') !== '0000-00-00' && ($data['dissolved'] ?? '') !== '') {
            $data['dissolved'] = $this->normaliseFrontendDate((string) $data['dissolved']);
        }

        if (($data['founded'] ?? '') === '0000-00-00' || ($data['founded'] ?? '') === '') {
            $data['founded'] = '0000-00-00';
        }

        if ($data['founded'] !== '0000-00-00') {
            $data['founded_year'] = date('Y', strtotime($data['founded']));
            $data['founded_timestamp'] = $this->dateTimestamp($data['founded']);
        }

        if (($data['dissolved'] ?? '') === '0000-00-00' || ($data['dissolved'] ?? '') === '') {
            $data['dissolved'] = '0000-00-00';
        }

        if ($data['dissolved'] !== '0000-00-00') {
            $data['dissolved_year'] = date('Y', strtotime($data['dissolved']));
            $data['dissolved_timestamp'] = $this->dateTimestamp($data['dissolved']);
        }

        if (empty($data['founded_year'])) {
            $data['founded_year'] = 'kein';
        }

        foreach ((array) ($data['request'] ?? []) as $key => $value) {
            $data[$key] = $value;
        }

        try {
            $table = $this->getTable('club');
            $table->bind($data);
            $table->store();
        } catch (\Throwable $e) {
            Log::add($e->getMessage(), Log::ERROR, 'jsmerror');
        }
    }

    public function getTable($type = 'club', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'club') === 0) {
            return new ClubTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    public function getForm($data = [], $loadData = true)
    {
        $option = $this->siteApplication()->getInput()->getCmd('option', 'com_sportsmanagement');
        $params = ComponentHelper::getParams($option);
        $mediaTool = trim((string) $params->get('cfg_which_media_tool', 'media'));
        $showTeamCommunity = (bool) $params->get('show_team_community', 0);

        if ($mediaTool === '' || ctype_digit($mediaTool)) {
            $mediaTool = 'media';
        }

        FormHelper::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');
        FormHelper::addFieldPrefix('Diddipoeler\\Component\\SportsManagement\\Administrator\\Field');

        $form = $this->loadForm(
            'com_sportsmanagement.' . $this->name,
            $this->name,
            ['load_data' => $loadData]
        );

        if (!$form) {
            return false;
        }

        if (!$showTeamCommunity) {
            $form->setFieldAttribute('merge_teams', 'type', 'hidden');
        }

        $mediaFields = [
            'logo_small' => ['ph_logo_small', 'com_sportsmanagement/database/clubs/small'],
            'logo_middle' => ['ph_logo_medium', 'com_sportsmanagement/database/clubs/medium'],
            'logo_big' => ['ph_logo_big', 'com_sportsmanagement/database/clubs/large'],
            'trikot_home' => ['ph_logo_small', 'com_sportsmanagement/database/clubs/trikot_home'],
            'trikot_away' => ['ph_logo_small', 'com_sportsmanagement/database/clubs/trikot_away'],
        ];

        foreach ($mediaFields as $field => [$placeholder, $directory]) {
            if (!$form->getField($field)) {
                continue;
            }

            $form->setFieldAttribute($field, 'default', (string) $params->get($placeholder, ''));
            $form->setFieldAttribute($field, 'directory', $directory);
            $form->setFieldAttribute($field, 'type', $mediaTool);
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

    public function getData()
    {
        if ($this->club === null) {
            $this->club = $this->getTable('Club', 'sportsmanagementTable');
            $this->club->load($this->clubid);
        }

        return $this->club;
    }

    private function siteApplication(): SiteApplication
    {
        return Factory::getContainer()->get(SiteApplication::class);
    }

    private function normaliseFrontendDate(string $date): string
    {
        if (strpos($date, '-') === false) {
            if (strlen($date) === 8) {
                return substr($date, 4, 4) . '-' . substr($date, 2, 2) . '-' . substr($date, 0, 2);
            }

            if (strlen($date) === 6) {
                return substr(date('Y'), 0, 2) . substr($date, 4, 2) . '-' . substr($date, 2, 2) . '-' . substr($date, 0, 2);
            }

            return '';
        }

        return substr($date, 6, 4) . '-' . substr($date, 3, 2) . '-' . substr($date, 0, 2);
    }

    private function dateTimestamp(string $date): int
    {
        return (int) strtotime($date);
    }
}