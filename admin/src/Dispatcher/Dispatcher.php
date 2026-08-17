<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcher;

/**
 * Hybrid administrator dispatcher used during the Joomla 5/6 migration.
 *
 * Only explicitly migrated, read-only routes are handled by the modern Joomla
 * dispatcher. Every other request is delegated to the existing legacy entry
 * point so the component can be migrated incrementally without a big-bang cutover.
 */
final class Dispatcher extends ComponentDispatcher
{
    private const MODERN_DISPLAY_VIEWS = [
        'close',
        'currentseasons',
    ];

    public function dispatch()
    {
        $task = strtolower($this->input->getCmd('task', 'display'));
        $view = strtolower($this->input->getCmd('view', ''));
        $controller = strtolower($this->input->getCmd('controller', ''));

        if (
            $task === 'display'
            && ($controller === '' || $controller === 'display')
            && in_array($view, self::MODERN_DISPLAY_VIEWS, true)
        ) {
            parent::dispatch();

            return;
        }

        $this->dispatchLegacy();
    }

    private function dispatchLegacy(): void
    {
        $legacyEntryPoint = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/sportsmanagement.php';

        if (!is_file($legacyEntryPoint)) {
            throw new \RuntimeException('SportsManagement legacy administrator entry point not found.', 500);
        }

        require $legacyEntryPoint;
    }
}
