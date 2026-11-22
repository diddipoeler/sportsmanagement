<?php
/**
 * Joomla 5/6 administrator handball.net import model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\ParameterType;

/**
 * Native Joomla 5/6 administrator model for the handball.net import screen.
 */
final class JlexthandballnetModel extends SportsManagementListModel
{
    /**
     * Initialise the handball.net importer with Joomla MVC services.
     *
     * Retain the native list-model configuration and allow the import
     * workflow a longer execution window when server policy permits it.
     * A configured unlimited execution time is left unchanged.
     *
     * @param array $config Joomla MVC model settings.
     * @param MVCFactoryInterface|null $factory Optional MVC factory.
     * @return void
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $maxImportTime = 480;
        $currentLimit = (int) ini_get('max_execution_time');

        if ($currentLimit > 0 && $currentLimit < $maxImportTime) {
            @set_time_limit($maxImportTime);
        }
    }

    /**
     * Preserve the historical importer data-loading hook.
     *
     * The current native handball.net adapter has no legacy table cache;
     * return null instead of attempting an unsupported data load.
     *
     * @return null No legacy dataset is available.
     */
    public function _loadData()
    {
        return null;
    }

    /**
     * Preserve the historical importer data-initialisation hook.
     *
     * The Joomla 5/6 list model manages state initialization; the old
     * importer hook therefore performs no additional initialization.
     *
     * @return null Legacy initialization is intentionally unavailable.
     */
    public function _initData()
    {
        return null;
    }

    /**
     * Retain the legacy handball.net project-type entry point.
     *
     * Project-type discovery has not yet been migrated to the native
     * importer; this compatibility stub returns null rather than
     * performing a legacy unscoped project query.
     *
     * @param mixed $project_id Historical project identifier.
     * @return null Project-type discovery is not implemented here.
     */
    public function getProjectType($project_id = 0)
    {
        return null;
    }

    /**
     * Retain the legacy project-team lookup entry point.
     *
     * The native handball.net adapter does not yet implement team lookup;
     * returning null makes the missing migration explicit to callers.
     *
     * @param mixed $project_id Historical project identifier.
     * @param mixed $division_id Historical division identifier.
     * @return null Team discovery is not implemented by this adapter.
     */
    public function getProjectteams($project_id = 0, $division_id = 0)
    {
        return null;
    }

    /**
     * Retrieve the league country for one validated SportsManagement project.
     *
     * Accept only positive integer IDs or decimal strings representable as
     * PHP integers. Bind the project ID to Joomla's query builder and return
     * false for missing projects, invalid input or database failures. Log
     * database details server-side rather than displaying SQL to users.
     *
     * @param mixed $project_id Project identifier from the import workflow.
     * @return mixed Country code from the league, or false when unavailable.
     */
    public function getCountry($project_id = 0)
    {
        if (!is_int($project_id) && !is_string($project_id)) {
            return false;
        }

        $rawId = trim((string) $project_id);

        if ($rawId === '' || preg_match('/^[0-9]+$/D', $rawId) !== 1) {
            return false;
        }

        $digits = ltrim($rawId, '0');
        $maximum = (string) PHP_INT_MAX;

        if ($digits === '' || strlen($digits) > strlen($maximum)
            || (strlen($digits) === strlen($maximum) && strcmp($digits, $maximum) > 0)) {
            return false;
        }

        $projectId = (int) $digits;

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('l.country'))
            ->from($db->quoteName('#__sportsmanagement_league', 'l'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.league_id') . ' = ' . $db->quoteName('l.id')
            )
            ->where($db->quoteName('p.id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);

        try {
            $db->setQuery($query, 0, 1);
            $country = $db->loadResult();

            return $country !== null ? $country : false;
        } catch (\Throwable $e) {
            \Joomla\CMS\Log\Log::add(
                'SportsManagement handball.net country lookup failed: ' . $e->getMessage(),
                \Joomla\CMS\Log\Log::ERROR,
                'com_sportsmanagement'
            );
            $this->administratorApplication()->enqueueMessage(
                \Joomla\CMS\Language\Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
                'error'
            );

            return false;
        }
    }
}
