<?php
/**
 * Joomla 5/6 administrator model entry point for the historical DBB importer.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

LegacyBootstrap::boot();

if (!class_exists('sportsmanagementModeljlextdbbimport', false)) {
    $legacyModel = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/jlextdbbimport.php';

    if (is_file($legacyModel)) {
        require_once $legacyModel;
    }
}

if (!class_exists('sportsmanagementModeljlextdbbimport', false)) {
    throw new \RuntimeException('Legacy SportsManagement DBB import engine could not be loaded.', 500);
}

/**
 * Native MVCFactory entry point for the DBB importer.
 *
 * The legacy engine has a parameterless constructor, while Joomla 5/6 passes
 * the model configuration and MVC factory. Accept the native signature here
 * and keep the old initialization behind this explicit compatibility boundary.
 */
final class JlextdbbimportModel extends \sportsmanagementModeljlextdbbimport
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct();
    }
}
