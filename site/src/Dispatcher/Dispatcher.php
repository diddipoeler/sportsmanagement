<?php
namespace Diddipoeler\Component\SportsManagement\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcher;

final class Dispatcher extends ComponentDispatcher
{
    private const MODERN_FORMATS = ['html', 'raw', 'pdf'];
    private const NATIVE_JSON_TASKS = [
        'ajax.getroute',
        'ajax.getprojectsoptions',
    ];
    private const NATIVE_SPECIAL_TASKS = [
        'editclub.apply',
        'editclub.save',
        'editclub.cancel',
        'editclub.load',
        'editmatch.applyshortsinglematch',
        'editmatch.saveshortsinglematch',
        'editmatch.deletesinglematch',
        'editmatch.savestats',
        'editmatch.cancel',
        'editmatch.savereferees',
        'editmatch.saverosterbillard',
        'editmatch.saveroster',
        'editmatch.saveshort',
        'editperson.apply',
        'editperson.submit',
        'editperson.save',
        'editperson.cancel',
        'editprojectteam.apply',
        'editprojectteam.submit',
        'editprojectteam.save',
        'editprojectteam.cancel',
        'editteam.apply',
        'editteam.submit',
        'editteam.save',
        'editteam.cancel',
        'imagehandler.upload',
        'imagehandler.delete',
        'matches.saveevent',
        'matches.savesubst',
        'matches.removesubst',
        'matches.savecomment',
        'matches.removeevent',
        'matches.removecommentary',
        'predictionranking.selectprojectround',
        'predictionresults.selectprojectround',
        'predictionresults.recalculatepoints',
        'predictionusers.select',
        'predictionusers.selectprojectround',
        'predictionusers.savememberdata',
        'predictionusers.cancel',
        'predictionuser.save',
        'predictionuser.cancel',
        'predictionentry.select',
        'predictionentry.selectprojectround',
        'predictionentry.register',
        'predictionentry.addtipp',
    ];
    private const NATIVE_VIEW_LAYOUTS = [
        'predictionuser' => ['edit'],
        'editmatch' => ['edit', 'editreferees', 'editevents', 'editstats', 'editlineup'],
    ];

    public function dispatch()
    {
        $task = strtolower($this->input->getCmd('task', 'display'));
        $view = strtolower($this->input->getCmd('view', ''));
        $controller = strtolower($this->input->getCmd('controller', ''));
        $layout = strtolower($this->input->getCmd('layout', 'default'));
        $format = strtolower($this->input->getCmd('format', 'html'));

        if ($task === 'display'
            && $view === 'predictionuser'
            && $layout === 'default'
            && $format === 'html'
            && ($controller === '' || $controller === 'display')) {
            $this->input->set('view', 'predictionusers');
            $view = 'predictionusers';
        }

        if ($this->isModernTask($task, $format)
            || $this->isModernDisplayRequest($task, $view, $controller, $layout, $format)) {
            parent::dispatch();
            return;
        }

        $this->dispatchLegacy();
    }

    private function isModernTask(string $task, string $format): bool
    {
        if ($format === 'json' && in_array($task, self::NATIVE_JSON_TASKS, true)) {
            return true;
        }

        return $format === 'html' && in_array($task, self::NATIVE_SPECIAL_TASKS, true);
    }

    private function isModernDisplayRequest(string $task, string $view, string $controller, string $layout, string $format): bool
    {
        if ($view === '' || $task !== 'display' || !in_array($format, self::MODERN_FORMATS, true)) {
            return false;
        }

        if ($layout !== 'default') {
            $nativeLayouts = self::NATIVE_VIEW_LAYOUTS[$view] ?? [];

            if ($format !== 'html' || !in_array($layout, $nativeLayouts, true)) {
                return false;
            }
        }

        if ($controller !== '' && $controller !== 'display') {
            return false;
        }

        $legacyView = JPATH_SITE . '/components/com_sportsmanagement/views/' . $view . '/view.' . $format . '.php';
        $nativeView = 'Diddipoeler\\Component\\SportsManagement\\Site\\View\\' . ucfirst($view) . '\\' . ucfirst($format) . 'View';

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
