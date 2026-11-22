<?php
/**
 * Joomla 5/6 administrator model entry point for the historical DFB.net importer.
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

if (!class_exists('sportsmanagementModeljlextdfbnetplayerimport', false)) {
    $legacyModel = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/jlextdfbnetplayerimport.php';

    if (is_file($legacyModel)) {
        require_once $legacyModel;
    }
}

if (!class_exists('sportsmanagementModeljlextdfbnetplayerimport', false)) {
    throw new \RuntimeException('Legacy SportsManagement DFB.net import engine could not be loaded.', 500);
}

/** Native MVCFactory entry point for the DFB.net importer. */
final class JlextdfbnetplayerimportModel extends \sportsmanagementModeljlextdfbnetplayerimport
{
    /**
     * Adapt the Joomla 5/6 MVC factory to the legacy DFB.net player import engine.
     *
     * Accept the current Joomla model configuration and optional MVC factory
     * without passing unsupported arguments into the historical parameterless
     * importer constructor. The legacy importer remains responsible for
     * establishing its own compatibility state until its full migration.
     *
     * @param array $config Joomla MVC model configuration supplied by the factory.
     * @param MVCFactoryInterface|null $factory Optional native Joomla MVC factory.
     * @return void
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct();
    }
}
