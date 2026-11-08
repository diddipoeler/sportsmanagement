<?php
/**
 * Joomla 5/6 dispatcher for mod_sportsmanagement_navigation_menu.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementNavigationMenu\Site\Dispatcher;

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
     * Build the navigation menu context for Joomla's site module renderer.
     *
     * Loads component translations, queries native navigation data and
     * registers CSS and deferred scripts without allowing helper results
     * to overwrite Joomla's module context. Invalid module database
     * selections return false before any navigation links are rendered.
     *
     * @return array|false Layout variables, or false if Joomla skips rendering.
     * @throws \RuntimeException When called outside the site or without a database.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Navigation Menu requires the Joomla site application.', 500);
        }

        $language = $app->getLanguage();
        $tag = $language->getTag();

        $language->load('mod_sportsmanagement_navigation_menu', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $tag, true);
        $language->load('com_sportsmanagement', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement_countries', JPATH_ADMINISTRATOR, $tag, true);

        $joomlaDatabase = $this->joomlaDatabase();

        $payload = $this->getHelperFactory()
            ->getHelper('NativeNavigationMenuHelper')
            ->getData($data['params'], $app, $joomlaDatabase);

        $payload = is_array($payload) ? $payload : [];

        if ($payload === []) {
            // Invalid database configuration must not render with missing
            // navigation selectors or an unintentionally chosen database.
            return false;
        }

        // Keep the Joomla-provided module, params and layout immutable.
        unset($payload['module'], $payload['params'], $payload['layout']);

        foreach ($payload as $key => $value) {
            $data[$key] = $value;
        }

        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            $assets = $document->getWebAssetManager();
            $assets->registerAndUseStyle(
                'mod_sportsmanagement_navigation_menu',
                'modules/mod_sportsmanagement_navigation_menu/css/mod_sportsmanagement_navigation_menu.css',
                ['version' => 'auto']
            );
            $assets->registerAndUseScript(
                'mod_sportsmanagement_navigation_menu',
                'modules/mod_sportsmanagement_navigation_menu/js/mod_sportsmanagement_navigation_menu.js',
                ['version' => 'auto'],
                ['defer' => true]
            );
        }

        return $data;
    }

    /**
     * Resolve the Joomla database service required by the Navigation Menu helper.
     *
     * Reads the database dependency from Joomla's service container and
     * checks its interface before passing it to the module's query methods.
     *
     * @return DatabaseInterface Connection for module data queries.
     * @throws \RuntimeException If no compatible database service exists.
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
