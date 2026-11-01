<?php
/**
 * Native Joomla 5/6 dispatcher for the first league overview module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementFirstLeagueOverview\Site\Dispatcher;

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
     * Populate the first-league overview layout with project and federation data.
     *
     * Loads component translations, resolves the Joomla database, normalises
     * empty helper results, and registers Bootstrap tabs and module CSS for
     * HTML documents. Non-HTML output retains the data without adding assets.
     *
     * @return array|false Joomla layout data or false when rendering is skipped.
     * @throws \RuntimeException Outside the site client or without a database.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement First League Overview requires the Joomla site application.', 500);
        }

        $language = $app->getLanguage();
        $tag = $language->getTag();

        $language->load('mod_sportsmanagement_firstleagueoverview', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $tag, true);
        $language->load('com_sportsmanagement', JPATH_SITE, $tag, true);
        $language->load('com_sportsmanagement_countries', JPATH_ADMINISTRATOR, $tag, true);

        $database = $this->joomlaDatabase();
        $overview = $this->getHelperFactory()
            ->getHelper('FirstLeagueOverviewHelper')
            ->getData($data['params'], $database);

        // Optional database results must not be assumed to be arrays.
        $overview = is_array($overview) ? $overview : [];

        $data['firstleagueoverview'] = is_array($overview['projects'] ?? null) ? $overview['projects'] : [];
        $data['federations'] = is_array($overview['federations'] ?? null) ? $overview['federations'] : [];

        $document = $app->getDocument();

        if (!$document instanceof HtmlDocument) {
            return $data;
        }

        $wam = $document->getWebAssetManager();
        $wam->useScript('bootstrap.tab');
        $wam->registerAndUseStyle(
            'mod_sportsmanagement_firstleagueoverview',
            'modules/mod_sportsmanagement_firstleagueoverview/css/mod_sportsmanagement_firstleagueoverview.css',
            ['version' => 'auto']
        );

        return $data;
    }

    /**
     * Resolve the Joomla database service used for overview queries.
     *
     * Ensures the container service implements the database interface before
     * passing it to the module helper, avoiding invalid dependency types.
     *
     * @return DatabaseInterface Database service for helper queries.
     * @throws \RuntimeException When the database service is unavailable.
     */
    private function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement First League Overview database service is unavailable.', 500);
        }

        return $database;
    }
}
