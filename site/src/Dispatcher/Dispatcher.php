<?php
namespace Diddipoeler\Component\SportsManagement\Site\Dispatcher;
\defined('_JEXEC') or die;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
final class Dispatcher extends ComponentDispatcher
{
    private const MODERN_DISPLAY_VIEWS = ['about','clubs','predictionrules','referees','teams'];
    public function dispatch()
    {
        $task = strtolower($this->input->getCmd('task', 'display'));
        $view = strtolower($this->input->getCmd('view', ''));
        $controller = strtolower($this->input->getCmd('controller', ''));
        $layout = strtolower($this->input->getCmd('layout', 'default'));
        if ($task === 'display' && $layout === 'default' && ($controller === '' || $controller === 'display') && in_array($view, self::MODERN_DISPLAY_VIEWS, true)) {
            parent::dispatch();
            return;
        }
        $this->dispatchLegacy();
    }
    private function dispatchLegacy(): void
    {
        $legacyEntryPoint = JPATH_SITE . '/components/com_sportsmanagement/sportsmanagement.php';
        if (!is_file($legacyEntryPoint)) { throw new \RuntimeException('SportsManagement legacy site entry point not found.', 500); }
        require $legacyEntryPoint;
    }
}
