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
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\Database\DatabaseInterface;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Build calendar event data and frontend script options.
     *
     * Queries the helper through Joomla's database service, preserves framework layout variables, and registers calendar CSS, JavaScript and instance-specific configuration. Assets are reused across repeated module instances on the same document.
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

        $db = $this->joomlaDatabase();
        $payload = $this->getHelperFactory()
            ->getHelper('GcalendarHelper')
            ->getData($data['params'], $data['module'], $app, $db);
        $payload = is_array($payload) ? $payload : [];
        unset($payload['module'], $payload['params'], $payload['layout']);
        $data = array_merge($data, $payload);

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
