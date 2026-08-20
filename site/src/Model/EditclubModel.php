<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\ClubTable;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
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

        $input = Factory::getApplication()->getInput();
        $this->projectid = $input->getInt('p', 0);
        $this->clubid = $input->getInt('cid', 0);
    }

    public function updItem($data)
    {
        $this->bootSportsManagementHelper();

        if (($data['founded'] ?? '') !== '0000-00-00' && ($data['founded'] ?? '') !== '') {
            $data['founded'] = \sportsmanagementHelper::convertDate($data['founded'], 0);
        }

        if (($data['dissolved'] ?? '') !== '0000-00-00' && ($data['dissolved'] ?? '') !== '') {
            $data['dissolved'] = \sportsmanagementHelper::convertDate($data['dissolved'], 0);
        }

        if (($data['founded'] ?? '') === '0000-00-00' || ($data['founded'] ?? '') === '') {
            $data['founded'] = '0000-00-00';
        }

        if ($data['founded'] !== '0000-00-00') {
            $data['founded_year'] = date('Y', strtotime($data['founded']));
            $data['founded_timestamp'] = \sportsmanagementHelper::getTimestamp($data['founded']);
        }

        if (($data['dissolved'] ?? '') === '0000-00-00' || ($data['dissolved'] ?? '') === '') {
            $data['dissolved'] = '0000-00-00';
        }

        if ($data['dissolved'] !== '0000-00-00') {
            $data['dissolved_year'] = date('Y', strtotime($data['dissolved']));
            $data['dissolved_timestamp'] = \sportsmanagementHelper::getTimestamp($data['dissolved']);
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
        $option = Factory::getApplication()->getInput()->getCmd('option', 'com_sportsmanagement');
        $params = ComponentHelper::getParams($option);
        $cfgWhichMediaTool = $params->get('cfg_which_media_tool', 0);
        $showTeamCommunity = (bool) $params->get('show_team_community', 0);

        Form::addFormPath(JPATH_COMPONENT_ADMINISTRATOR . '/forms');
        Form::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/fields');

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

        $form->setFieldAttribute('logo_small', 'default', $params->get('ph_logo_small', ''));
        $form->setFieldAttribute('logo_small', 'directory', 'com_sportsmanagement/database/clubs/small');
        $form->setFieldAttribute('logo_small', 'type', $cfgWhichMediaTool);

        $form->setFieldAttribute('logo_middle', 'default', $params->get('ph_logo_medium', ''));
        $form->setFieldAttribute('logo_middle', 'directory', 'com_sportsmanagement/database/clubs/medium');
        $form->setFieldAttribute('logo_middle', 'type', $cfgWhichMediaTool);

        $form->setFieldAttribute('logo_big', 'default', $params->get('ph_logo_big', ''));
        $form->setFieldAttribute('logo_big', 'directory', 'com_sportsmanagement/database/clubs/large');
        $form->setFieldAttribute('logo_big', 'type', $cfgWhichMediaTool);

        $form->setFieldAttribute('trikot_home', 'default', $params->get('ph_logo_small', ''));
        $form->setFieldAttribute('trikot_home', 'directory', 'com_sportsmanagement/database/clubs/trikot_home');
        $form->setFieldAttribute('trikot_home', 'type', $cfgWhichMediaTool);

        $form->setFieldAttribute('trikot_away', 'default', $params->get('ph_logo_small', ''));
        $form->setFieldAttribute('trikot_away', 'directory', 'com_sportsmanagement/database/clubs/trikot_away');
        $form->setFieldAttribute('trikot_away', 'type', $cfgWhichMediaTool);

        return $form;
    }

    protected function loadFormData()
    {
        $app = Factory::getApplication();
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

    private function bootSportsManagementHelper(): void
    {
        if (!class_exists('sportsmanagementHelper', false)) {
            require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';
        }
    }
}
