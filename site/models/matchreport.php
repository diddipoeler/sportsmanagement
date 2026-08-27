<?php
/**
 * SportsManagement legacy compatibility adapter.
 *
 * The active Joomla 5/6 implementation lives in site/src/Model/MatchreportModel.php.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\MatchreportDataModel;
use Diddipoeler\Component\SportsManagement\Site\Model\MatchreportMatchDataModel;
use Diddipoeler\Component\SportsManagement\Site\Model\MatchreportModel;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

if (!class_exists(MatchreportModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/MatchreportModel.php';
}

if (!class_exists(MatchreportDataModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/MatchreportDataModel.php';
}

if (!class_exists(MatchreportMatchDataModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/MatchreportMatchDataModel.php';
}

/**
 * Compatibility shell for legacy views and third-party callers.
 */
class sportsmanagementModelMatchReport extends BaseDatabaseModel
{
    public int $matchid = 0;
    public ?object $match = null;
    public int $projectid = 0;
    public ?array $_playersevents = null;
    public ?array $_playersbasicstats = null;
    public ?array $_staffsbasicstats = null;

    private MatchreportModel $nativeModel;
    private MatchreportDataModel $dataModel;
    private MatchreportMatchDataModel $matchDataModel;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = Factory::getApplication()->getInput();
        $this->matchid = max(0, $input->getInt('mid', 0));
        $this->projectid = max(0, $input->getInt('p', 0));

        $this->nativeModel = new MatchreportModel($config, $factory);
        $this->dataModel = new MatchreportDataModel($config, $factory);
        $this->matchDataModel = new MatchreportMatchDataModel($config, $factory);
    }

    public function getDatabaseSelector(): int
    {
        return $this->nativeModel->getDatabaseSelector();
    }

    public function getProject()
    {
        return $this->nativeModel->getProject();
    }

    public function getProjectTeamById($projectTeamId)
    {
        $projectTeamId = (int) $projectTeamId;

        if ($projectTeamId <= 0) {
            return null;
        }

        foreach ($this->nativeModel->getProjectTeams(0) as $team) {
            if ((int) ($team->projectteamid ?? 0) === $projectTeamId) {
                return $team;
            }
        }

        return null;
    }

    public function getProjectStats($statId = 0, $positionId = 0)
    {
        return $this->nativeModel->getProjectStats($statId, (int) $positionId);
    }

    public function getMatchData($matchId = null)
    {
        $matchId = $matchId === null ? $this->matchid : (int) $matchId;
        return $this->match = $this->matchDataModel->getMatchData($matchId);
    }

    public function getMatchText($matchId)
    {
        return $this->matchDataModel->getMatchText((int) $matchId);
    }

    public function getMatchSingleData($matchId = null): array
    {
        return $this->dataModel->getMatchSingleData($matchId === null ? null : (int) $matchId);
    }

    public function getMatchReferees($matchId = null): array
    {
        return $this->dataModel->getMatchReferees($matchId === null ? null : (int) $matchId);
    }

    public function getMatchCommentary($matchId = null): array
    {
        return array_reverse(
            $this->dataModel->getMatchCommentary($matchId === null ? null : (int) $matchId)
        );
    }

    public function getMatchSubstitutions($matchId = null): array
    {
        return $this->dataModel->getMatchSubstitutions($matchId === null ? null : (int) $matchId);
    }

    public function getMatchEvents($matchId = null, $showComments = true, $sortDescending = false): array
    {
        return $this->dataModel->getMatchEvents(
            $matchId === null ? null : (int) $matchId,
            (bool) $showComments,
            (bool) $sortDescending
        );
    }

    public function getPlayground($playgroundId)
    {
        return $this->dataModel->getPlayground((int) $playgroundId);
    }

    public function getbillardplayer($positionName = 'COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_CAPTAIN', $projectTeamId = 0, $matchId = 0)
    {
        return $this->nativeModel->getbillardplayer((string) $positionName, (int) $projectTeamId, (int) $matchId);
    }

    public function checkMatchPlayerProjectPositionID(): void
    {
        $this->nativeModel->checkMatchPlayerProjectPositionID();
    }

    public function getClubinfo($clubId)
    {
        return $this->nativeModel->getClubinfo((int) $clubId);
    }

    public function getRound()
    {
        return $this->nativeModel->getRound();
    }

    public function getMatchPictures($folder)
    {
        return $this->nativeModel->getMatchPictures((string) $folder);
    }

    public function getMatchPositions($which = 'player')
    {
        return $this->nativeModel->getMatchPositions((string) $which);
    }

    public function getMatchPersons($which = 'player')
    {
        return $this->nativeModel->getMatchPersons((string) $which);
    }

    public function getEventTypes()
    {
        return $this->nativeModel->getEventTypes();
    }

    public function getMatchArticle($articleId = 0, $matchId = 0, $categoryId = 0)
    {
        return $this->nativeModel->getMatchArticle((int) $articleId, (int) $matchId, (int) $categoryId);
    }

    public function getMatchStats()
    {
        return $this->nativeModel->getMatchStats();
    }

    public function getPlayersStats()
    {
        return $this->_playersbasicstats = $this->nativeModel->getPlayersStats();
    }

    public function getPlayersEvents()
    {
        return $this->_playersevents = $this->nativeModel->getPlayersEvents();
    }

    public function getMatchStaffStats()
    {
        return $this->_staffsbasicstats = $this->nativeModel->getMatchStaffStats();
    }

    public function getPlaygroundSchema($schema, $which)
    {
        return $this->nativeModel->getPlaygroundSchema($schema, $which);
    }
}
