<?php
/**
 * Native Joomla 5/6 dispatcher for the TeamPlayers module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementTeamPlayers\Site\Dispatcher;

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
     * Load the team roster and player records for the native Joomla layout.
     *
     * Normalises incomplete helper data and reuses CSS across instances,
     * activating optional carousel JavaScript only for template C. The Joomla site
     * client is verified before changing module layout parameters and only
     * valid scalar carousel settings can enable optional JavaScript.
     *
     * @return array|false Module layout variables, or false when skipped.
     * @throws \RuntimeException On invalid application or helper services.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement TeamPlayers requires the Joomla site application.', 500);
        }

        $data['params']->set('layout', 'native');

        $language = $app->getLanguage();
        $language->load('com_sportsmanagement', JPATH_SITE, null, true);
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

        $database = $this->joomlaDatabase();
        $payload = $this->getHelperFactory()
            ->getHelper('TeamPlayersHelper')
            ->getData($data['params'], $database);

        $payload = is_array($payload) ? $payload : [];

        // Preserve the layout contract even if a helper returns incomplete data.
        $data['project'] = is_object($payload['project'] ?? null) ? $payload['project'] : null;
        $data['players'] = is_array($payload['players'] ?? null) ? $payload['players'] : [];
        $data['roster'] = is_array($payload['roster'] ?? null) ? $payload['roster'] : [];

        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            $this->useRosterAssets($document, $data['params']->get('template', 'L') === 'C');
        }

        return $data;
    }

    /**
     * Get the Joomla database service used by the TeamPlayers helper.
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
            throw new \RuntimeException('SportsManagement TeamPlayers database service is unavailable.', 500);
        }

        return $database;
    }
    /**
     * Activate roster CSS with carousel JavaScript only for template C.
     *
     * Reuse Joomla's shared HTML WebAssetManager across module instances
     * without repeating registrations. Preserve the original asset sources,
     * cache options, deferred attributes and required dependencies.
     *
     * @param HtmlDocument $document Current Joomla site HTML response.
     * List-only module instances never register carousel.js. Mixed list and
     * carousel rosters therefore reuse CSS but retain separate script modes.
     *
     * @param bool $carousel Whether the carousel template is selected.
     * @return void
     */
    private function useRosterAssets(HtmlDocument $document, bool $carousel): void
    {
        $assets = $document->getWebAssetManager();
        $name = 'mod_sportsmanagement_teamplayers.native';
        if (!$assets->assetExists('style', $name)) {
            $assets->registerStyle($name,
                'modules/mod_sportsmanagement_teamplayers/css/native.css', ['version' => 'auto']
            );
        }
        $assets->useStyle($name);
        if (!$carousel) {
            return;
        }
        if (!$assets->assetExists('script', $name)) {
            $assets->registerScript($name,
                'modules/mod_sportsmanagement_teamplayers/js/native.js', ['version' => 'auto'], ['defer' => true], []
            );
        }
        $assets->useScript($name);
    }


}
