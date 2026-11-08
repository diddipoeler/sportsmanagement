<?php
/**
 * Joomla 5/6 JEvents connector for the SportsManagement calendar module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

if (!class_exists(SportsManagementSiteApplicationResolver::class)) {
    $resolverFile = JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php';

    if (is_file($resolverFile)) {
        require_once $resolverFile;
    }
}

if (!class_exists(SportsManagementSiteApplicationResolver::class)) {
    throw new \RuntimeException('SportsManagement site application resolver could not be loaded.', 500);
}

final class JEventsConnector extends JSMCalendar
{
    /**
     * Resolve the Joomla frontend application for calendar connector diagnostics.
     * Reuses the component's site application resolver, avoiding removed global Joomla application accessors.
     *
     * @return CMSApplicationInterface Active Joomla site application.
     */
    private static function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    public static Registry $xparams;
    private static $jevent;

    /**
     * Load JEvents calendar entries for the month requested by the module.
     * Checks that JEvents is installed, parses the calendar's starting date, requests the relevant month from JEventsDataModel and appends normalised events to the shared match array.
     *
     * @param array $caldates Date and time boundaries selected by the calendar.
     * @param Registry $params Module settings, retained for event headings.
     * @param array $matches Shared calendar rows populated by reference.
     * @return array<int,array> JEvents records formatted for the module.
     */
    public static function getEntries(array $caldates, Registry $params, array &$matches): array
    {
        if (!self::checkJEvents()) {
            return [];
        }

        $start = substr((string) ($caldates['start'] ?? ''), 0, 10);
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $start);
        if (!$date) {
            return [];
        }

        self::$xparams = $params;
        $data = self::$jevent->getCalendarData((int) $date->format('Y'), (int) $date->format('m'), 1);

        return self::formatEntries((array) ($data['dates'] ?? []), $matches);
    }

    /**
     * Verify that the JEvents adapter and its data-model entry point are available.
     * Lazily loads the optional JEvents PHP files, validates the external model API and reports a frontend warning rather than fatally requiring an uninstalled extension.
     *
     * @return bool Whether the JEvents model is ready for calendar data retrieval.
     */
    private static function checkJEvents(): bool
    {
        $base = JPATH_SITE . '/components/com_jevents';
        $defines = $base . '/mod.defines.php';
        $model = $base . '/libraries/datamodel.php';

        if (!is_file($defines) || !is_file($model)) {
            self::raiseError('JEvents connector disabled: required JEvents files were not found.');
            return false;
        }

        require_once $defines;
        require_once $model;

        if (!class_exists('JEventsDataModel')) {
            self::raiseError('JEvents connector disabled: JEventsDataModel is unavailable.');
            return false;
        }

        self::$jevent = new \JEventsDataModel();
        if (!is_callable([self::$jevent, 'getCalendarData'])) {
            self::raiseError('JEvents connector disabled: getCalendarData() is unavailable.');
            return false;
        }

        return true;
    }

    /**
     * Display a nonfatal calendar connector warning in the Joomla frontend.
     * Rejects unsupported Joomla clients and forwards missing-extension diagnostics through the application's notification queue.
     *
     * @param string $message Safe, user-visible warning text.
     * @return void
     */
    private static function raiseError(string $message): void
    {
        $app = self::siteApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Calendar JEvents connector requires the Joomla site application.', 500);
        }

        $app->enqueueMessage($message, 'warning');
    }

    /**
     * Convert JEvents day and event records into SportsManagement calendar rows.
     * Normalises titles, locations, date/time ranges and event metadata, and appends every formatted event to the shared cross-connector collection.
     *
     * @param array $rows Date-grouped JEvents data returned by the provider.
     * @param array $matches Shared calendar rows extended in-place.
     * @return array<int,array> Newly formatted event records.
     */
    private static function formatEntries(array $rows, array &$matches): array
    {
        $newRows = [];

        foreach ($rows as $row) {
            if (empty($row['events']) || !is_iterable($row['events'])) {
                continue;
            }

            foreach ($row['events'] as $event) {
                $formatted = [
                    'link' => self::buildLink($event, (int) ($row['year'] ?? 0), (int) ($row['month'] ?? 0)),
                    'date' => date('Y-m-d', (int) ($row['cellDate'] ?? 0)) . ' ' . date('H:i', (int) ($event->_dtstart ?? 0)),
                    'type' => 'jevents',
                    'time' => '',
                    'headingtitle' => (string) self::$xparams->get('jevents_text', 'JEvents'),
                    'name' => '',
                    'title' => (string) ($event->_title ?? ''),
                    'location' => (string) ($event->_location ?? ''),
                    'color' => (string) ($event->_color_bar ?? ''),
                    'matchcode' => 0,
                    'project_id' => 0,
                ];

                if ((int) ($event->_alldayevent ?? 0) !== 1) {
                    $start = (int) ($event->_dtstart ?? 0);
                    $end = (int) ($event->_dtend ?? 0);
                    $formatted['time'] = date('H:i', $start);
                    if ($start !== $end && (int) ($event->_noendtime ?? 0) === 0) {
                        $formatted['time'] .= '-' . date('H:i', $end);
                    }
                }

                $newRows[] = $formatted;
                $matches[] = $formatted;
            }
        }

        return $newRows;
    }

    /**
     * Route a JEvents occurrence to its detail page.
     * Preserves occurrence, year, month and UID information and uses Joomla's routing service so the result fits the active site configuration.
     *
     * @param object $event JEvents event occurrence metadata.
     * @param int $year Event year.
     * @param int $month Event month.
     * @return string Joomla-routed JEvents event URL.
     */
    private static function buildLink(object $event, int $year, int $month): string
    {
        $router = JPATH_SITE . '/components/com_jevents/router.php';
        if (is_file($router)) {
            require_once $router;
        }

        $link = 'index.php?option=com_jevents&task=icalrepeat.detail&evid=' . (int) ($event->_eventdetail_id ?? 0)
            . '&year=' . $year . '&month=' . $month . '&day=' . (int) ($event->_dup ?? 0)
            . '&uid=' . rawurlencode((string) ($event->_uid ?? ''));

        return Route::_($link);
    }
}
