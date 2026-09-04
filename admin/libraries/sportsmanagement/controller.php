<?php
/**
 * Legacy compatibility shims for the native Joomla 5/6 administrator controller bases.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Controller\SportsManagementAdminController;
use Diddipoeler\Component\SportsManagement\Administrator\Controller\SportsManagementFormController;

if (!class_exists(SportsManagementAdminController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementAdminController.php';
}

if (!class_exists(SportsManagementFormController::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Controller/SportsManagementFormController.php';
}

if (!class_exists('JSMControllerAdmin', false)) {
    class JSMControllerAdmin extends SportsManagementAdminController
    {
        /** @var int */
        public $team_club_id = 0;

        /** @var object|null */
        public $jsmapp;

        /** @var object|null */
        public $jsmjinput;

        /** @var string */
        public $jsmoption = 'com_sportsmanagement';

        public function __construct($config = [])
        {
            parent::__construct($config);
            $this->jsmapp = $this->app;
            $this->jsmjinput = $this->input;
            $this->jsmoption = $this->input->getCmd('option', 'com_sportsmanagement');
        }
    }
}

if (!class_exists('JSMControllerForm', false)) {
    class JSMControllerForm extends SportsManagementFormController
    {
        /** @var object|null */
        public $jsmdb;

        /** @var object|null */
        public $jsmapp;

        /** @var object|null */
        public $jsmjinput;

        /** @var string */
        public $jsmoption = 'com_sportsmanagement';

        /** @var object|null */
        public $jsmdocument;

        /** @var object|null */
        public $jsmuser;

        /** @var object|null */
        public $jsmdate;

        /** @var int */
        public $team_club_id = 0;

        /** @var int */
        public $club_id = 0;

        /** @var int */
        public $person_id = 0;

        /** @var int */
        public $team_id = 0;

        /** @var int */
        public $insert_id = 0;
    }
}
