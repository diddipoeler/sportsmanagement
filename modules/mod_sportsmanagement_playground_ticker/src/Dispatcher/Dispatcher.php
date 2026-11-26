<?php
/**
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementPlaygroundTicker\Site\Dispatcher;

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
     * Populate the playground ticker with selected grounds and image URLs.
     *
     * Retrieves validated grounds and image-server settings, preserves the
     * native layout and enables the carousel only in its configured mode.
     * Helper or database failures leave an empty ticker and are logged
     * without exposing connection diagnostics.
     *
     * @return array|false Module layout variables, or false if skipped.
     * @throws \RuntimeException When called outside the Joomla site application.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement PlaygroundTicker requires the Joomla site application.', 500);
        }

        $app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);

        $data['playgrounds'] = [];
        $data['module']->picture_server = '';

        try {
            $helper = $this->getHelperFactory()->getHelper('PlaygroundTickerHelper');
            $database = $this->joomlaDatabase();
            $playgrounds = $helper->getData($data['params'], $app, $database);
            $data['module']->picture_server = $helper->getPictureServer($data['params'], $app);
            $data['playgrounds'] = is_array($playgrounds) ? $playgrounds : [];
        } catch (\Throwable $e) {
            \Joomla\CMS\Log\Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', \Joomla\CMS\Log\Log::ERROR, 'com_sportsmanagement');
        }

        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            $assets = $document->getWebAssetManager();
            // Multiple ticker instances share the same Joomla HTML document.
            if (!$assets->assetExists('style', 'mod_sportsmanagement_playground_ticker')) {
                $assets->registerStyle(
                    'mod_sportsmanagement_playground_ticker',
                    'modules/mod_sportsmanagement_playground_ticker/css/mod_sportsmanagement_playground_ticker.css',
                    ['version' => 'auto']
                );
            }

            $assets->useStyle('mod_sportsmanagement_playground_ticker');

            $rawMode = $data['params']->get('mode', 'L');
            if (is_string($rawMode) && strtoupper($rawMode) === 'B') {
                $assets->useScript('bootstrap.carousel');
            }
        }

        return $data;
    }

    /**
     * Obtain the Joomla database interface for Playground Ticker queries.
     *
     * Resolves the service from Joomla's dependency injection container and
     * rejects missing or incompatible services before invoking the helper.
     *
     * @return DatabaseInterface Valid database service.
     * @throws \RuntimeException If the database dependency is unavailable.
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
