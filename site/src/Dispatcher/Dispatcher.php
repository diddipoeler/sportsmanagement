<?php
namespace Diddipoeler\Component\SportsManagement\Site\Dispatcher;
\defined('_JEXEC') or die;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
final class Dispatcher extends ComponentDispatcher
{
    public function dispatch()
    {
        $task = strtolower($this->input->getCmd('task', 'display'));
        $view = strtolower($this->input->getCmd('view', ''));
        $controller = strtolower($this->input->getCmd('controller', ''));
        $layout = strtolower($this->input->getCmd('layout', 'default'));
        $format = strtolower($this->input->getCmd('format', 'html'));
        if ($this->isModernDisplayRequest($task, $view, $controller, $layout, $format)) {
            parent::dispatch();
            return;
        }
        $this->dispatchLegacy();
    }
    private function isModernDisplayRequest(string $task, string $view, string $controller, string $layout, string $format): bool
    {
        if ($view === '' || $task !== 'display' || $layout !== 'default' || $format !== 'html') {
            return false;
        }
        if ($controller !== '' && $controller !== 'display') {
            return false;
        }
        $legacyView = JPATH_SITE . '/components/com_sportsmanagement/views/' . $view . '/view.html.php';
        $nativeView = 'Diddipoeler\\Component\\SportsManagement\\Site\\View\\' . ucfirst($view) . '\\HtmlView';
        return is_file($legacyView) || class_exists($nativeView);
    }
    private function dispatchLegacy(): void
    {
        $legacyEntryPoint = JPATH_SITE . '/components/com_sportsmanagement/sportsmanagement.php';
        if (!is_file($legacyEntryPoint)) {
            throw new \RuntimeException('SportsManagement legacy site entry point not found.', 500);
        }
        require $legacyEntryPoint;
    }
}
