<?php
/**
 * Joomla 5/6 site dispatcher for SportsManagement with incremental legacy fallback.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Dispatcher;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ResultsLegacyHtmlCompatibility;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Dispatcher\ComponentDispatcher;

final class Dispatcher extends ComponentDispatcher
{
    private const MODERN_FORMATS = ['html', 'raw', 'pdf'];
    private const NATIVE_JSON_TASKS = [
        'ajax.getlink',
        'ajax.getprojectteams',
        'ajax.getprojectselect',
        'ajax.getassocleagueselect',
        'ajax.getcountrysubsubassocselect',
        'ajax.getcountrysubassocselect',
        'ajax.getcountryassoc',
        'ajax.getroute',
        'ajax.getprojectsoptions',
    ];
    private const NATIVE_SPECIAL_TASKS = [
        'editclub.apply',
        'editclub.save',
        'editclub.cancel',
        'editclub.load',
        'editmatch.apply',
        'editmatch.save',
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
        'results.savereferees',
        'results.saveshort',
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
        'results' => ['form', 'form_dfcday'],
        'predictionuser' => ['edit'],
        'editmatch' => [
            'edit', 'edit_3', 'edit_4',
            'editreferees', 'editreferees_3', 'editreferees_4',
            'editevents', 'editevents_3', 'editevents_4',
            'editstats', 'editstats_3', 'editstats_4',
            'editlineup', 'editlineup_3', 'editlineup_4',
        ],
    ];

    /**
     * Route the current frontend request to native Joomla MVC or the legacy bridge.
     *
     * Checks that a site application is active, validates request identifiers,
     * maps the historical predictionuser view alias and selects the migrated
     * controller/view only for supported tasks, layouts and output formats.
     * All other valid requests continue through the legacy entry point.
     *
     * @return void
     * @throws \RuntimeException If called outside the site or the legacy entry point is missing.
     * @throws \InvalidArgumentException If a request identifier is malformed.
     */
    public function dispatch()
    {
        if (!$this->app instanceof SiteApplication || !$this->app->isClient('site')) {
            throw new \RuntimeException('SportsManagement requires the Joomla site application.', 500);
        }

        $task = $this->validatedIdentifier('task', 'display');
        $view = $this->validatedIdentifier('view', '');
        $controller = $this->validatedIdentifier('controller', '');
        $layout = $this->validatedIdentifier('layout', 'default');
        $format = $this->validatedIdentifier('format', 'html');

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
            if ($view === 'results') {
                ResultsLegacyHtmlCompatibility::register();
            }

            parent::dispatch();
            return;
        }

        $this->dispatchLegacy();
    }


    /**
     * Reject arrays and filtered request identifiers before MVC or legacy routing.
     *
     * Joomla's getCmd() silently removes invalid characters; accepting the
     * transformed value could select a different controller, view or layout.
     *
     * @param string $key Joomla input key to validate.
     * @param string $default Default identifier when the key is absent.
     * @return string Lowercase, unmodified command identifier.
     * @throws \InvalidArgumentException If the value is not a string or fails filtering.
     */
    private function validatedIdentifier(string $key, string $default): string
    {
        $raw = $this->input->get($key, $default, 'raw');

        if (!is_string($raw)) {
            throw new \InvalidArgumentException('Invalid SportsManagement ' . $key . '.', 400);
        }

        $filtered = $this->input->getCmd($key, $default);

        if (strtolower($filtered) !== strtolower($raw)) {
            throw new \InvalidArgumentException('Invalid SportsManagement ' . $key . '.', 400);
        }

        return strtolower($filtered);
    }

    /**
     * Determine whether the request names an explicitly migrated controller action.
     *
     * JSON AJAX actions and HTML form actions have separate allowlists so
     * unported tasks and unsupported output formats cannot reach a native
     * controller accidentally.
     *
     * @param string $task Normalised Joomla task identifier.
     * @param string $format Normalised requested response format.
     * @return bool True when the task and format are supported by native MVC.
     */
    private function isModernTask(string $task, string $format): bool
    {
        if ($format === 'json' && in_array($task, self::NATIVE_JSON_TASKS, true)) {
            return true;
        }

        return $format === 'html' && in_array($task, self::NATIVE_SPECIAL_TASKS, true);
    }

    /**
     * Select native rendering only for available Joomla 5/6 frontend view classes.
     *
     * Requires the default display task and controller, a supported HTML/raw/PDF
     * format, and either the default layout or an explicitly migrated HTML
     * layout for that view. A missing native view retains the legacy path.
     *
     * @param string $task Requested Joomla task.
     * @param string $view Selected frontend view name.
     * @param string $controller Explicit controller selector, if supplied.
     * @param string $layout Requested template layout.
     * @param string $format Output document format.
     * @return bool True when the corresponding native view can handle the request.
     */
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

        $nativeView = 'Diddipoeler\\Component\\SportsManagement\\Site\\View\\' . ucfirst($view) . '\\' . ucfirst($format) . 'View';

        return class_exists($nativeView);
    }

    /**
     * Execute the retained frontend compatibility entry point.
     *
     * Resolves the component path from Joomla's fixed site directory rather
     * than request data and fails with a controlled exception if the file is
     * missing. This keeps unported frontend operations accessible during
     * incremental migration.
     *
     * @return void
     * @throws \RuntimeException If the legacy component entry point is absent.
     */
    private function dispatchLegacy(): void
    {
        $legacyEntryPoint = JPATH_SITE . '/components/com_sportsmanagement/sportsmanagement.php';

        if (!is_file($legacyEntryPoint)) {
            throw new \RuntimeException('SportsManagement legacy site entry point not found.', 500);
        }

        require $legacyEntryPoint;
    }
}
