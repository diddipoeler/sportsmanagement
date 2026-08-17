<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Factory;

final class Dispatcher extends ComponentDispatcher
{
    private const NATIVE_CRUD_CONTROLLERS = [
        'eventtype', 'eventtypes',
        'extrafield', 'extrafields',
    ];

    private const NATIVE_EDIT_VIEWS = [
        'eventtype',
        'extrafield',
    ];

    public function dispatch()
    {
        $identity = Factory::getApplication()->getIdentity();

        if (!$identity->authorise('core.manage', 'com_sportsmanagement')) {
            throw new \RuntimeException('Not authorised to manage SportsManagement.', 403);
        }

        $task = strtolower($this->input->getCmd('task', 'display'));
        $view = strtolower($this->input->getCmd('view', 'cpanel'));
        $controller = strtolower($this->input->getCmd('controller', ''));
        $layout = strtolower($this->input->getCmd('layout', 'default'));
        $format = strtolower($this->input->getCmd('format', 'html'));

        if (
            $this->isModernCrudTask($task, $format)
            || $this->isModernEditDisplay($task, $view, $controller, $layout, $format)
            || $this->isModernDisplayRequest($task, $view, $controller, $layout, $format)
        ) {
            $this->input->set('view', $view);
            parent::dispatch();
            return;
        }

        $this->dispatchLegacy();
    }

    private function isModernCrudTask(string $task, string $format): bool
    {
        if ($format !== 'html' || $task === 'display') {
            return false;
        }

        $controller = strtolower((string) strtok($task, '.'));

        return in_array($controller, self::NATIVE_CRUD_CONTROLLERS, true);
    }

    private function isModernEditDisplay(string $task, string $view, string $controller, string $layout, string $format): bool
    {
        if ($task !== 'display' || $layout !== 'edit' || $format !== 'html') {
            return false;
        }

        if ($controller !== '' && $controller !== 'display') {
            return false;
        }

        return in_array($view, self::NATIVE_EDIT_VIEWS, true);
    }

    private function isModernDisplayRequest(string $task, string $view, string $controller, string $layout, string $format): bool
    {
        if ($task !== 'display' || $layout !== 'default' || $format !== 'html') {
            return false;
        }

        if ($controller !== '' && $controller !== 'display') {
            return false;
        }

        $legacyView = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/' . $view . '/view.html.php';
        $nativeView = 'Diddipoeler\\Component\\SportsManagement\\Administrator\\View\\' . ucfirst($view) . '\\HtmlView';

        return is_file($legacyView) || class_exists($nativeView);
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
