<?php
/**
 * Native Joomla 5/6 dispatcher for sport-type statistics.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementSportsTypeStatistics\Site\Dispatcher;

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
     * Load sports-type statistics for the module template.
     *
     * Loads multilingual component resources, requests statistics through the native helper and supplies a safe empty result when the database has no records.
     * The method rejects administrator execution and preserves Joomla's
     * original layout context when rendering is skipped.
     *
     * Optional database and helper failures are logged without private
     * diagnostics and produce empty results instead of interrupting the page.
     * @return array|false Template variables, or false when skipped.
     * @throws \RuntimeException When the site context is unavailable.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Sports Type Statistics requires the Joomla site application.', 500);
        }

        $language = $app->getLanguage();
        $tag = $language->getTag();

        $language->load('mod_sportsmanagement_sports_type_statistics', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $tag, true);
        $language->load('com_sportsmanagement', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement_countries', JPATH_ADMINISTRATOR, $tag, true);

        try {
            $database = $this->joomlaDatabase();
            $statistics = $this->getHelperFactory()
                ->getHelper('SportsTypeStatisticsHelper')
                ->getData($data['params'], $database);
        } catch (\Throwable $e) {
            \Joomla\CMS\Log\Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', \Joomla\CMS\Log\Log::ERROR, 'com_sportsmanagement');
            $statistics = [];
        }

        $data['statistics'] = is_array($statistics) ? $statistics : [];

        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            // Joomla renders multiple module instances into one asset registry.
            $assets = $document->getWebAssetManager();

            if (!$assets->assetExists('style', 'mod_sportsmanagement_sports_type_statistics')) {
                $assets->registerStyle(
                    'mod_sportsmanagement_sports_type_statistics',
                    'modules/mod_sportsmanagement_sports_type_statistics/css/mod_sportsmanagement_sports_type_statistics.css',
                    ['version' => 'auto']
                );
            }

            $assets->useStyle('mod_sportsmanagement_sports_type_statistics');
        }

        return $data;
    }

    /**
     * Obtain a valid Joomla database service for Sports Type Statistics queries.
     *
     * Retrieves the registered DatabaseInterface from Joomla's service
     * container. Converts container lookup failures into a clear module-level
     * exception, retains their causes for debugging, and rejects incompatible
     * services before any helper queries are executed.
     *
     * @return DatabaseInterface Database connection used by the module.
     * @throws \RuntimeException When the Joomla database service is unavailable.
     */
    private function joomlaDatabase(): DatabaseInterface
    {
        try {
            $database = Factory::getContainer()->get(DatabaseInterface::class);
        } catch (\Throwable $exception) {
            throw new \RuntimeException('SportsManagement database service could not be resolved.', 500, $exception);
        }

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement database service is unavailable.', 500);
        }

        return $database;
    }
}
