<?php
/**
 * Joomla 5/6 dispatcher for the SportsManagement Team Stats Ranking module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementTeamStatsRanking\Site\Dispatcher;

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
     * Prepare team statistics rankings for the frontend template.
     *
     * Loads translations and registers the shared stylesheet for HTML output.
     * Isolates optional database and helper failures and supplies the full
     * ranking payload with safe defaults, retaining Joomla-owned layout data.
     *
     * @return array|false Template context, or false when rendering is skipped.
     * @throws \RuntimeException When invoked outside Joomla's site client.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Team Stats Ranking requires the Joomla site application.', 500);
        }

        $language = $app->getLanguage();
        $tag = $language->getTag();

        $language->load('mod_sportsmanagement_teamstats_ranking', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $tag, true);
        $language->load('com_sportsmanagement', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement_countries', JPATH_ADMINISTRATOR, $tag, true);

        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            // A Joomla page may contain several instances of this module.
            // Reuse the same registered stylesheet for all of them.
            $assets = $document->getWebAssetManager();

            if (!$assets->assetExists('style', 'mod_sportsmanagement_teamstats_ranking')) {
                $assets->registerStyle(
                    'mod_sportsmanagement_teamstats_ranking',
                    'modules/mod_sportsmanagement_teamstats_ranking/css/mod_sportsmanagement_teamstats_ranking.css',
                    ['version' => 'auto']
                );
            }

            $assets->useStyle('mod_sportsmanagement_teamstats_ranking');
        }

        try {
            $joomlaDatabase = $this->joomlaDatabase();
            $helper = $this->getHelperFactory()->getHelper('TeamStatsRankingHelper');
            $payload = $helper->getData($data['params'], $joomlaDatabase);
        } catch (\Throwable $e) {
            \Joomla\CMS\Log\Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', \Joomla\CMS\Log\Log::ERROR, 'com_sportsmanagement');
            $payload = [];
        }

        // A failed or incomplete helper response must still define every
        // variable consumed by the template.
        $payload = is_array($payload) ? $payload : [];
        $payload['project'] = is_object($payload['project'] ?? null) ? $payload['project'] : null;
        $payload['stat'] = is_object($payload['stat'] ?? null) ? $payload['stat'] : null;
        $payload['ranking'] = is_array($payload['ranking'] ?? null) ? $payload['ranking'] : [];
        $payload['teams'] = is_array($payload['teams'] ?? null) ? $payload['teams'] : [];
        $payload['databaseSelector'] = ($payload['databaseSelector'] ?? null) === 1 ? 1 : 0;

        // Preserve the framework-provided module context and selected layout.
        unset($payload['module'], $payload['params'], $payload['layout']);

        return array_merge($data, $payload);
    }

    /**
     * Resolve the database dependency used by the team ranking helper.
     *
     * Obtains Joomla's registered DatabaseInterface service and rejects an
     * incompatible service before the ranking query is executed.
     *
     * @return DatabaseInterface Database connection for ranking queries.
     * @throws \RuntimeException If the database service is unavailable.
     */
    private function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement Team Stats Ranking database service is unavailable.', 500);
        }

        return $database;
    }
}
