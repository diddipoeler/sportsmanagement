<?php
/**
 * Joomla 5/6 dispatcher for the SportsManagement TrainingsData module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) 2015 diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementTrainingsData\Site\Dispatcher;

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
     * Retrieve and display the configured training information.
     *
     * Loads the shared component translations, obtains training records with the database-backed helper and registers the module stylesheet for HTML output.
     *
     * @return array|false Layout data or false if rendering is skipped.
     * @throws \RuntimeException If the site application or database is unavailable.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement TrainingsData requires the Joomla site application.', 500);
        }

        $app->getLanguage()->load('com_sportsmanagement', JPATH_SITE, null, true);

        $database = $this->joomlaDatabase();
        $trainingsdata = $this->getHelperFactory()
            ->getHelper('TrainingsDataHelper')
            ->getData($data['params'], $database);

        $data['trainingsdata'] = is_array($trainingsdata) ? $trainingsdata : [];

        $document = $app->getDocument();

        if ($document instanceof HtmlDocument) {
            $document->getWebAssetManager()->registerAndUseStyle(
                'mod_sportsmanagement_trainingsdata',
                'modules/mod_sportsmanagement_trainingsdata/css/mod_sportsmanagement_trainingsdata.css'
            );
        }

        return $data;
    }

    /**
     * Resolve the Joomla database dependency for the TrainingsData helper.
     *
     * Uses the dependency injection container and verifies that the resolved
     * service implements DatabaseInterface before any queries are performed.
     *
     * @return DatabaseInterface Valid database connection for module queries.
     * @throws \RuntimeException When no compatible database service exists.
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
