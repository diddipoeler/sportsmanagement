<?php
/**
 * Native Joomla 5/6 dispatcher for the events ranking module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementEventsRanking\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Load event ranking statistics for the module layout.
     *
     * Loads component translations, queries the ranking helper, normalises
     * missing statistics, and enables the shared module stylesheet on HTML
     * responses. Several instances can reuse the same registered asset
     * without a duplicate-name registration error.
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
            throw new \RuntimeException('SportsManagement Events Ranking requires the Joomla site application.', 500);
        }

        $language = $app->getLanguage();
        $tag = $language->getTag();

        $language->load('mod_sportsmanagement_eventsranking', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $tag, true);
        $language->load('com_sportsmanagement', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement_countries', JPATH_ADMINISTRATOR, $tag, true);

        $database = $this->joomlaDatabase();
        $rankingData = $this->getHelperFactory()
            ->getHelper('EventsRankingHelper')
            ->getData($data['params'], $database);

        $data['rankingData'] = is_array($rankingData) ? $rankingData : [];

        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            $this->useEventsRankingStylesheet($document);
        }

        return $data;
    }

    /**
     * Enable Events Ranking CSS through Joomla's shared HTML asset registry.
     *
     * Each module instance receives the active site document. Only the
     * first instance registers the versioned stylesheet; every instance
     * activates the same asset name so its rendered layout is styled.
     * The caller restricts this operation to HtmlDocument responses.
     *
     * @param HtmlDocument $document Active Joomla site HTML response.
     * @return void
     */
    private function useEventsRankingStylesheet(HtmlDocument $document): void
    {
        $assets = $document->getWebAssetManager();
        $name = 'mod_sportsmanagement_eventsranking';

        if (!$assets->assetExists('style', $name)) {
            $assets->registerStyle(
                $name,
                'modules/mod_sportsmanagement_eventsranking/css/mod_sportsmanagement_eventsranking.css',
                ['version' => 'auto']
            );
        }

        $assets->useStyle($name);
    }

    /**
     * Resolve the Joomla database service used by the Events Ranking helper.
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
            throw new \RuntimeException('SportsManagement database service is unavailable.', 500);
        }

        return $database;
    }
}
