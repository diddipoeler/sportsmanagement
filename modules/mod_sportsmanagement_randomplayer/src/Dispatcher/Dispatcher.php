<?php
/**
 * Joomla 5/6 dispatcher for the SportsManagement Random Player module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementRandomPlayer\Site\Dispatcher;

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
     * Select a random player and populate the native module template.
     *
     * Loads component translations, keeps missing helper output iterable and registers the HTML stylesheet.
     *
     * Optional database and helper failures are logged without private
     * diagnostics and produce empty results instead of interrupting the page.
     * @return array|false Module layout variables, or false when skipped.
     * @throws \RuntimeException On an invalid application client.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement RandomPlayer requires the Joomla site application.', 500);
        }

        $language = $app->getLanguage();
        $tag = $language->getTag();

        $language->load('mod_sportsmanagement_randomplayer', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $tag, true);
        $language->load('com_sportsmanagement', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement_countries', JPATH_ADMINISTRATOR, $tag, true);

        // The native layout consumes the structured player result returned by the helper.
        $data['params']->set('layout', 'native');

        try {
            $database = $this->joomlaDatabase();
            $players = $this->getHelperFactory()
                ->getHelper('RandomPlayerHelper')
                ->getData($data['params'], $database);
        } catch (\Throwable $e) {
            \Joomla\CMS\Log\Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', \Joomla\CMS\Log\Log::ERROR, 'com_sportsmanagement');
            $players = [];
        }

        // The native template expects an iterable list, even for empty results.
        $data['list'] = is_array($players) ? $players : [];

        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            // Several module instances can share the same Joomla document.
            $assets = $document->getWebAssetManager();

            if (!$assets->assetExists('style', 'mod_sportsmanagement_randomplayer')) {
                $assets->registerStyle(
                    'mod_sportsmanagement_randomplayer',
                    'modules/mod_sportsmanagement_randomplayer/css/mod_sportsmanagement_randomplayer.css',
                    ['version' => 'auto']
                );
            }

            $assets->useStyle('mod_sportsmanagement_randomplayer');
        }

        return $data;
    }

    /**
     * Get the Joomla database service used by the RandomPlayer helper.
     *
     * Resolves the service from the dependency injection container and checks
     * its interface before allowing database queries to run.
     *
     * @return DatabaseInterface Valid database service.
     * @throws \RuntimeException When the database service is unavailable.
     */
    private function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement RandomPlayer database service is unavailable.', 500);
        }

        return $database;
    }
}
