<?php
/**
 * Joomla 5/6 administrator model entry point for the historical ProfiLeague importer.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

LegacyBootstrap::boot();

if (!class_exists('sportsmanagementModeljlextprofleagimport', false)) {
    $legacyModel = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/jlextprofleagimport.php';

    if (is_file($legacyModel)) {
        require_once $legacyModel;
    }
}

if (!class_exists('sportsmanagementModeljlextprofleagimport', false)) {
    throw new \RuntimeException('Legacy SportsManagement ProfiLeague import engine could not be loaded.', 500);
}

/** Native MVCFactory entry point for the ProfiLeague importer. */
final class JlextprofleagimportModel extends \sportsmanagementModeljlextprofleagimport
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        // The legacy constructor references an undefined $config variable.
        // Initialize its BaseDatabaseModel ancestor directly with Joomla's
        // current constructor contract, then reproduce the legacy state setup.
        BaseDatabaseModel::__construct($config, $factory);

        $this->jsmdb = \sportsmanagementHelper::getDBConnection();
        $this->jsmquery = $this->jsmdb->createQuery();
        $this->jsmapp = Factory::getContainer()->get(AdministratorApplication::class);
        $this->jsmjinput = $this->jsmapp->getInput();
        $this->jsmoption = $this->jsmjinput->getCmd('option', 'com_sportsmanagement');
        $this->debug_info = (bool) ComponentHelper::getParams($this->jsmoption)->get('show_debug_info', 0);
    }
}
