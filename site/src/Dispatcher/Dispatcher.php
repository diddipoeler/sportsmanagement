<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcher;

/**
 * Site compatibility dispatcher for the Joomla 5/6 migration.
 *
 * No site view is switched to the modern dispatcher yet. Keeping this class in
 * place allows the component service provider to be enabled while preserving
 * the existing frontend bootstrap and extension-controller behaviour.
 */
final class Dispatcher extends ComponentDispatcher
{
    public function dispatch()
    {
        $legacyEntryPoint = JPATH_SITE . '/components/com_sportsmanagement/sportsmanagement.php';

        if (!is_file($legacyEntryPoint)) {
            throw new \RuntimeException('SportsManagement legacy site entry point not found.', 500);
        }

        require $legacyEntryPoint;
    }
}
