<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Native Joomla 5/6 administrator model for the handball.net import screen.
 */
final class JlexthandballnetModel extends SportsManagementListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $maxImportTime = 480;
        $currentLimit = (int) ini_get('max_execution_time');

        if ($currentLimit > 0 && $currentLimit < $maxImportTime) {
            @set_time_limit($maxImportTime);
        }
    }

    /** Legacy compatibility hook. */
    public function _loadData()
    {
        return null;
    }

    /** Legacy compatibility hook. */
    public function _initData()
    {
        return null;
    }

    /** Legacy compatibility hook. */
    public function getProjectType($project_id = 0)
    {
        return null;
    }

    /** Legacy compatibility hook. */
    public function getProjectteams($project_id = 0, $division_id = 0)
    {
        return null;
    }

    public function getCountry($project_id = 0)
    {
        $projectId = (int) $project_id;

        if ($projectId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('l.country'))
            ->from($db->quoteName('#__sportsmanagement_league', 'l'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.league_id') . ' = ' . $db->quoteName('l.id')
            )
            ->where($db->quoteName('p.id') . ' = ' . $projectId);

        try {
            $db->setQuery($query, 0, 1);
            $country = $db->loadResult();

            return $country !== null ? $country : false;
        } catch (\Throwable $e) {
            $this->administratorApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }
}
