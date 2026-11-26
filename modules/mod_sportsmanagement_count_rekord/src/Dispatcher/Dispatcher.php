<?php
/**
 * Joomla 5/6 dispatcher for mod_sportsmanagement_count_rekord.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementCountRekord\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\Database\DatabaseInterface;

final class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Retrieve the counting and record statistics required by the module.
     *
     * Obtains the Joomla database, calls the module helper and guarantees an array for template iteration.
     *
     * Optional database and helper failures are logged by exception class
     * and return empty template records so the surrounding page stays usable.
     *
     * @return array|false Layout data or false if rendering is skipped.
     * @throws \RuntimeException If the site client is unavailable.
     */
    protected function getLayoutData(): array|false
    {
        $data = parent::getLayoutData();

        if ($data === false) {
            return false;
        }

        $app = $this->getApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement CountRekord requires the Joomla site application.', 500);
        }

        try {
            $database = $this->joomlaDatabase();
            $records = $this->getHelperFactory()
                ->getHelper('CountRekordHelper')
                ->getData($data['params'], $data['module'], $database);
        } catch (\Throwable $e) {
            \Joomla\CMS\Log\Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', \Joomla\CMS\Log\Log::ERROR, 'com_sportsmanagement');
            $records = [];
        }

        $data['list'] = is_array($records) ? $records : [];

        return $data;
    }

    /**
     * Resolve the Joomla database service used by the Count Rekord helper.
     *
     * Retrieves the database through Joomla's dependency injection container
     * and validates the interface before executing helper queries.
     * Converts dependency lookup failures into a descriptive module error
     * while preserving the original throwable for diagnostics.
     *
     * @return DatabaseInterface Database connection for module queries.
     * @throws \RuntimeException When the database service is unavailable.
     */
    private function joomlaDatabase(): DatabaseInterface
    {
        try {
            $database = Factory::getContainer()->get(DatabaseInterface::class);
        } catch (\Throwable $exception) {
            throw new \RuntimeException('SportsManagement CountRekord database service could not be resolved.', 500, $exception);
        }

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement CountRekord database service is unavailable.', 500);
        }

        return $database;
    }
}
