<?php
/**
 * Joomla 5/6 dispatcher for the SportsManagement Project Map module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementProjectMap\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Assemble project location data and initialise the interactive map.
     *
     * Loads current-season projects, supplies JavaScript options, registers
     * dependent map assets and logs helper errors while keeping the module
     * renderable. Reuses init/worldmap scripts when multiple maps share one
     * HTML document, maintaining their explicit core/init dependencies.
     * Ensures stable empty template values on helper failures
     * and avoids exposing database connection details in error logs.
     *
     * @return array|false Layout data or false if rendering is skipped.
     * @throws \RuntimeException If the site client or database is unavailable.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Project Map requires the Joomla site application.', 500);
        }

        $language = $app->getLanguage();
        $tag = $language->getTag();

        $language->load('mod_sportsmanagement_projectmap', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $tag, true);
        $language->load('com_sportsmanagement', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement_countries', JPATH_ADMINISTRATOR, $tag, true);

        try {
            $db = $this->joomlaDatabase();
            $seasonIds = ComponentHelper::getParams('com_sportsmanagement')->get('current_season', []);
            $mapData = $this->getHelperFactory()
                ->getHelper('ProjectMapHelper')
                ->getMapData($seasonIds, $db);

            $mapData = is_array($mapData) ? $mapData : [];
            $data['projects'] = is_array($mapData['projects'] ?? null) ? $mapData['projects'] : [];

            $document = $app->getDocument();

            if ($document instanceof HtmlDocument) {
                $document->addScriptOptions(
                    'mod_sportsmanagement_projectmap.mapdata',
                    is_array($mapData['options'] ?? null) ? $mapData['options'] : []
                );

                $this->useProjectMapScripts($document);
            }
        } catch (\Throwable $exception) {
            $data['projects'] = [];
            Log::add(__METHOD__ . ': project map unavailable (' . get_class($exception) . ').', Log::ERROR, 'jsmerror');
            $app->enqueueMessage('SportsManagement Project Map data is currently unavailable.', 'warning');
        }

        return $data;
    }

    /**
     * Register and activate Project Map scripts with preserved dependencies.
     *
     * Multiple map modules may render into the same Joomla document. The
     * map initializer depends on Joomla core and worldmap.js depends on
     * the initializer. Register both only when absent from WebAssetManager,
     * but activate them on each render in dependency order. This leaves
     * script options supplied by the caller's addScriptOptions() untouched.
     *
     * @param HtmlDocument $document The shared HTML document for map modules.
     * @return void
     */
    private function useProjectMapScripts(HtmlDocument $document): void
    {
        $assets = $document->getWebAssetManager();
        $initAsset = 'mod_sportsmanagement_projectmap.init';
        $worldMapAsset = 'mod_sportsmanagement_projectmap.worldmap';

        if (!$assets->assetExists('script', $initAsset)) {
            $assets->registerScript(
                $initAsset,
                'modules/mod_sportsmanagement_projectmap/htmlworldmap/projectmap-init.js',
                [],
                [],
                ['core']
            );
        }

        $assets->useScript($initAsset);

        if (!$assets->assetExists('script', $worldMapAsset)) {
            $assets->registerScript(
                $worldMapAsset,
                'modules/mod_sportsmanagement_projectmap/htmlworldmap/worldmap.js',
                [],
                [],
                [$initAsset]
            );
        }

        $assets->useScript($worldMapAsset);
    }

    /**
     * Resolve the Joomla database service used by the Project Map helper.
     *
     * Retrieves the database through Joomla's dependency injection container
     * and validates the interface before executing helper queries.
     *
     * @return DatabaseInterface Database connection for module queries.
     * @throws \RuntimeException When the database service is unavailable.
     */
    private function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement Project Map database service is unavailable.', 500);
        }

        return $database;
    }
}
