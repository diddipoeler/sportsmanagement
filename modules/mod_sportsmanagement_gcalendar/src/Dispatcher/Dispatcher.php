<?php
/**
 * Joomla 5/6 dispatcher for the SportsManagement GCalendar module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementGcalendar\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\Database\DatabaseInterface;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Build calendar event data and frontend script options.
     *
     * Queries the helper through Joomla's database service, preserving all
     * framework-owned layout variables and per-instance client options.
     * Accepts only the documented calendar template fields and initialises
     * empty calendars and calendar IDs even when optional loading fails.
     * Failures in optional event loading are logged by exception class, not
     * by message, to avoid leaking private API/database details to visitors.
     * On failure the module renders an empty event list rather than breaking
     * the site page. CSS and JavaScript assets are reused between instances.
     *
     * @return array|false Render variables, or false when skipped.
     * @throws \RuntimeException When the application is not the site client.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement GCalendar requires the Joomla site application.', 500);
        }

        // Establish the template contract even if the optional calendar
        // service is unavailable. The native helper supplies calendars and
        // calendarIds; the events key remains for older custom overrides.
        $data['calendars'] = [];
        $data['calendarIds'] = [];
        $data['events'] = [];
        $data['calendarConfig'] = [];

        try {
            $db = $this->joomlaDatabase();
            $payload = $this->getHelperFactory()
                ->getHelper('GcalendarHelper')
                ->getData($data['params'], $data['module'], $app, $db);
            $payload = is_array($payload) ? $payload : [];

            // Only merge fields defined by the calendar template contract.
            // Never replace Joomla's module/params/layout or custom context.
            foreach (['calendars', 'calendarIds', 'events', 'calendarConfig'] as $key) {
                $value = $payload[$key] ?? null;
                $data[$key] = is_array($value) ? $value : [];
            }
        } catch (\Throwable $exception) {
            // Calendar integrations are optional page content. Keep an API or
            // database outage from replacing the complete frontend response.
            Log::add(
                'SportsManagement GCalendar data load failed (' . get_class($exception) . ').',
                Log::ERROR,
                'com_sportsmanagement'
            );
            $data['events'] = [];
            $data['calendarConfig'] = [];
        }

        // The script-options key must be stable and tied to the module instance.
        $moduleId = max(0, (int) ($data['module']->id ?? 0));
        $document = $app->getDocument();
        if ($document instanceof HtmlDocument) {
            // Shared assets are registered once even when multiple calendar modules render.
            $this->registerCalendarAssets($document);

            $document->addScriptOptions(
                'mod_sportsmanagement_gcalendar.' . $moduleId,
                is_array($data['calendarConfig'] ?? null) ? $data['calendarConfig'] : []
            );
        }

        $data['calendarOptionsKey'] = 'mod_sportsmanagement_gcalendar.' . $moduleId;

        return $data;
    }


    /**
     * Register the GCalendar stylesheet and script once per Joomla document.
     *
     * Joomla may render several calendar module instances on the same page.
     * Reuses assets already present in the WebAssetManager registry and marks
     * them as used for every instance, retaining deferred JavaScript loading
     * and cache-aware URLs without overriding an existing registration.
     *
     * @param HtmlDocument $document Current HTML document with its asset manager.
     * @return void
     */
    private function registerCalendarAssets(HtmlDocument $document): void
    {
        $assets = $document->getWebAssetManager();
        $name = 'mod_sportsmanagement_gcalendar.calendar';

        if (!$assets->assetExists('style', $name)) {
            $assets->registerStyle(
                $name,
                'modules/mod_sportsmanagement_gcalendar/tmpl/gcalendar.css',
                ['version' => 'auto']
            );
        }

        $assets->useStyle($name);

        if (!$assets->assetExists('script', $name)) {
            $assets->registerScript(
                $name,
                'modules/mod_sportsmanagement_gcalendar/js/gcalendar.js',
                ['version' => 'auto'],
                ['defer' => true]
            );
        }

        $assets->useScript($name);
    }

    /**
     * Resolve Joomla's database service for calendar event queries.
     *
     * Retrieves and validates the typed service from Joomla's dependency
     * injection container before passing it to the calendar helper.
     *
     * @return DatabaseInterface Database service for helper queries.
     * @throws \RuntimeException When the database service is unavailable.
     */
    private function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement GCalendar database service is unavailable.', 500);
        }

        return $database;
    }
}
