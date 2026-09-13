<?php
/**
 * Joomla 5/6 administrator model entry point for the historical LMO importer.
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

if (!class_exists('sportsmanagementModeljlextlmoimports', false)) {
    $legacyModel = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/jlextlmoimports.php';

    if (is_file($legacyModel)) {
        require_once $legacyModel;
    }
}

if (!class_exists('sportsmanagementModeljlextlmoimports', false)) {
    throw new \RuntimeException('Legacy SportsManagement LMO import engine could not be loaded.', 500);
}

/** Native MVCFactory entry point for the LMO importer. */
final class JlextlmoimportsModel extends \sportsmanagementModeljlextlmoimports
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct();
    }
}
