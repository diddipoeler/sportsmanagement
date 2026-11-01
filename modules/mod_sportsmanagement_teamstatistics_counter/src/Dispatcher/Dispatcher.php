<?php
/**
 * Joomla 5/6 dispatcher for the SportsManagement Team Statistics Counter module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementTeamStatisticsCounter\Site\Dispatcher;

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
     * Prepare the team statistics counters for site rendering.
     *
     * Queries the module helper using Joomla's database service, ensures the output is an iterable array and registers the optional HTML stylesheet.
     * The method rejects administrator execution and preserves Joomla's
     * original layout context when rendering is skipped.
     *
     * @return array|false Template variables, or false when skipped.
     * @throws \RuntimeException When the site context or database is unavailable.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Team Statistics Counter requires the Joomla site application.', 500);
        }

        $app->getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR, null, true);

        $joomlaDatabase = $this->joomlaDatabase();
        $statistics = $this->getHelperFactory()
            ->getHelper('TeamStatisticsCounterHelper')
            ->getData($data['params'], $joomlaDatabase);

        $data['data'] = is_array($statistics) ? $statistics : [];

        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            $document->getWebAssetManager()->registerAndUseStyle(
                'mod_sportsmanagement_teamstatistics_counter',
                'modules/mod_sportsmanagement_teamstatistics_counter/css/mod_sportsmanagement_teamstatistics_counter.css'
            );
        }

        return $data;
    }

    /**
     * Obtain a valid Joomla database service for Team Statistics Counter queries.
     *
     * Retrieves the registered DatabaseInterface from the service container
     * and rejects a missing or incompatible instance before helper execution.
     *
     * @return DatabaseInterface Database connection used by the module.
     * @throws \RuntimeException When the Joomla database service is unavailable.
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
