<?php
/**
 * Joomla 5/6 administrator model entry point for the historical SIS importer.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::boot();

if (!class_exists('sportsmanagementModeljlextsisimport', false)) {
    $legacyModel = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/jlextsisimport.php';

    if (is_file($legacyModel)) {
        require_once $legacyModel;
    }
}

if (!class_exists('sportsmanagementModeljlextsisimport', false)) {
    throw new \RuntimeException('Legacy SportsManagement SIS import engine could not be loaded.', 500);
}

/**
 * Native MVCFactory entry point for the SIS importer.
 *
 * The SIS parser itself remains legacy code for now. Keeping that dependency
 * behind a namespaced model lets Joomla 5/6 resolve the workflow normally and
 * gives the remaining parser migration one explicit compatibility boundary.
 */
final class JlextsisimportModel extends \sportsmanagementModeljlextsisimport
{
}
