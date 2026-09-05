<?php
/**
 * Native Joomla 5/6 access checks for frontend result editing.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Throwable;

/**
 * Joomla 5/6 access checks for frontend result editing.
 */
final class ResultsAccessModel extends SportsManagementProjectModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
    }

    public function setProjectId(int $projectId): void
    {
        $this->projectId = max(0, $projectId);
    }

    public function isAllowed(int $editorGroup = 0): bool
    {
        $user = $this->siteApplication()->getIdentity();
        if ((int) $user->id <= 0) {
            return false;
        }

        $project = $this->getProject();
        if (!$project) {
            return false;
        }

        $hasAction = $user->authorise('results.saveshort', 'com_sportsmanagement');
        $isProjectAdmin = (int) $user->id === (int) ($project->admin ?? 0);
        $isProjectEditor = (int) $user->id === (int) ($project->editor ?? 0);

        if ($hasAction && ($isProjectAdmin || $isProjectEditor)) {
            return true;
        }

        if ($editorGroup > 0) {
            return in_array($editorGroup, array_map('intval', $user->getAuthorisedGroups()), true);
        }

        return false;
    }

    public function isMatchAdmin(int $matchId, int $userId = 0): bool
    {
        if ($matchId <= 0) {
            return false;
        }

        if ($userId <= 0) {
            $userId = (int) $this->siteApplication()->getIdentity()->id;
        }

        if ($userId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt1')
                . ' ON ' . $db->quoteName('m.projectteam1_id') . ' = ' . $db->quoteName('pt1.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt2')
                . ' ON ' . $db->quoteName('m.projectteam2_id') . ' = ' . $db->quoteName('pt2.id')
            )
            ->where($db->quoteName('m.id') . ' = ' . $matchId)
            ->where(
                '(' . $db->quoteName('pt1.admin') . ' = ' . $userId
                . ' OR ' . $db->quoteName('pt2.admin') . ' = ' . $userId . ')'
            );

        if ($this->projectId > 0) {
            $query->where(
                '(' . $db->quoteName('pt1.project_id') . ' = ' . $this->projectId
                . ' OR ' . $db->quoteName('pt2.project_id') . ' = ' . $this->projectId . ')'
            );
        }

        try {
            $db->setQuery($query);
            return (int) $db->loadResult() > 0;
        } catch (Throwable) {
            return false;
        }
    }

    public function isTeamEditor(int $userId = 0): bool
    {
        if ($this->projectId <= 0) {
            return false;
        }

        if ($userId <= 0) {
            $userId = (int) $this->siteApplication()->getIdentity()->id;
        }

        if ($userId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('pt.admin'))
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_match', 'm')
                . ' ON (' . $db->quoteName('m.projectteam1_id') . ' = ' . $db->quoteName('pt.id')
                . ' OR ' . $db->quoteName('m.projectteam2_id') . ' = ' . $db->quoteName('pt.id') . ')'
            )
            ->where($db->quoteName('pt.project_id') . ' = ' . $this->projectId)
            ->where($db->quoteName('pt.admin') . ' = ' . $userId);

        try {
            $db->setQuery($query, 0, 1);
            return (int) $db->loadResult() > 0;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Match the legacy edit contract: project/editor-group access grants all
     * selected matches; otherwise each selected match must be administered by
     * the current user through one of its project teams.
     */
    public function canEditMatches(array $matchIds): bool
    {
        $matchIds = array_values(array_unique(array_filter(
            array_map('intval', $matchIds),
            static fn (int $id): bool => $id > 0
        )));

        if (!$matchIds) {
            return false;
        }

        $project = $this->getProject();
        if (!$project) {
            return false;
        }

        if ($this->isAllowed((int) ($project->editorgroup ?? 0))) {
            return true;
        }

        $userId = (int) $this->siteApplication()->getIdentity()->id;
        foreach ($matchIds as $matchId) {
            if (!$this->isMatchAdmin($matchId, $userId)) {
                return false;
            }
        }

        return true;
    }
}
