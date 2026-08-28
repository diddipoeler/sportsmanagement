<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Player;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ExtendedFormHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;
use Diddipoeler\Component\SportsManagement\Site\Model\PersonModel;
use Diddipoeler\Component\SportsManagement\Site\Model\PlayerMatchDataModel;
use Diddipoeler\Component\SportsManagement\Site\Model\PlayerModel;
use Diddipoeler\Component\SportsManagement\Site\Model\PlayerStatisticsModel;
use Diddipoeler\Component\SportsManagement\Site\Model\PlayerTimeModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** Native Joomla 5/6 player view while retaining the established template contract. */
final class HtmlView extends SportsManagementProjectHtmlView
{
    public ?object $person = null;
    public string $nickname = '';
    public array $teamPlayers = [];
    public bool $isContactDataVisible = true;
    public $checkextrafields = false;
    public $extrafields = [];
    public ?object $teamPlayer = null;
    public array $historyPlayer = [];
    public array $historyPlayerStaff = [];
    public array $AllEvents = [];
    public bool $showediticon = false;
    public $stats = [];
    public array $games = [];
    public array $teams = [];
    public $gamesevents = [];
    public $gamesstats = [];
    public $projectstats = [];
    public $extended = null;
    public $person_parent_positions = null;
    public $person_position = null;
    public string $hasDescription = '';
    public bool $hasExtendedData = false;
    public bool $hasStatus = false;
    public string $playername = '';

    public function __construct($config = [])
    {
        parent::__construct($config);

        // Player layouts have not yet been moved to site/tmpl/player.
        $this->addTemplatePath(JPATH_SITE . '/components/com_sportsmanagement/views/player/tmpl');
    }

    protected function prepareView(): void
    {
        $playerModel = $this->getModel();
        if (!$playerModel instanceof PlayerModel) {
            throw new \RuntimeException('Player view requires PlayerModel.', 500);
        }

        $databaseSelector = $this->databaseSelector === 1 ? 1 : 0;
        $playerModel->setDatabaseSelector($databaseSelector);

        $playerMatchDataModel = new PlayerMatchDataModel();
        $playerMatchDataModel->setDatabaseSelector($databaseSelector);
        $playerStatisticsModel = new PlayerStatisticsModel();
        $playerStatisticsModel->setDatabaseSelector($databaseSelector);
        $playerTimeModel = new PlayerTimeModel();
        $playerTimeModel->setDatabaseSelector($databaseSelector);

        $nativeProject = $playerModel->getProject();
        if ($nativeProject) {
            $this->project = $nativeProject;
        }

        $this->person = PersonModel::getPerson(0, $databaseSelector, 1);
        $nickname = (string) ($this->person->nickname ?? '');
        $this->nickname = $nickname !== '' ? "'" . $nickname . "'" : '';
        $this->teamPlayers = $playerModel->getTeamPlayers();

        $contactTeamOnly = !empty($this->config['show_contact_team_member_only']);
        $this->isContactDataVisible = PersonModel::isContactDataVisible($contactTeamOnly);

        if (!$this->isContactDataVisible && $contactTeamOnly) {
            $userId = (int) (Factory::getApplication()->getIdentity()->id ?? 0);
            $userSeasonTeamIds = $userId > 0
                ? PersonModel::_getProjectTeamIds4UserId($userId)
                : [];
            $playerSeasonTeamIds = [];

            foreach ($this->teamPlayers as $playerTeam) {
                $seasonTeamId = (int) ($playerTeam->team_id ?? 0);
                if ($seasonTeamId > 0) {
                    $playerSeasonTeamIds[$seasonTeamId] = $seasonTeamId;
                }
            }

            $this->isContactDataVisible = (bool) array_intersect(
                array_map('intval', $userSeasonTeamIds),
                array_values($playerSeasonTeamIds)
            );
        }

        $this->config['show_players_layout'] = $this->config['show_players_layout'] ?? 'no_tabs';

        if (!isset($this->overallconfig['person_events'])) {
            $this->overallconfig['person_events'] = $this->loadPersonEventIds($playerModel);
        }

        $this->checkextrafields = \sportsmanagementHelper::checkUserExtraFields(
            'frontend',
            $databaseSelector
        );

        if ($this->checkextrafields && $this->person) {
            $this->extrafields = \sportsmanagementHelper::getUserExtraFields(
                (int) $this->person->id,
                'frontend',
                $databaseSelector
            );
        }

        $teamPlayer = null;
        $currentProjectTeamId = 0;

        foreach ($this->teamPlayers as $candidate) {
            $teamPlayer ??= $candidate;

            if ((int) ($candidate->published ?? 0) === 1) {
                $currentProjectTeamId = (int) ($candidate->projectteam_id ?? 0);
                $teamPlayer = $candidate;
                break;
            }
        }

        if ($currentProjectTeamId > 0 && isset($this->teamPlayers[$currentProjectTeamId])) {
            $teamPlayer = $this->teamPlayers[$currentProjectTeamId];
        }

        $sportstype = !empty($this->config['show_plcareer_sportstype'])
            ? (int) ($this->project->sports_type_id ?? 0)
            : 0;

        $this->teamPlayer = $teamPlayer;
        $this->historyPlayer = $playerModel->getPlayerHistory(
            $sportstype,
            $this->config['historyorder'] ?? 'ASC',
            1
        );
        $this->historyPlayerStaff = $playerModel->getPlayerHistory(
            $sportstype,
            $this->config['historyorder'] ?? 'ASC',
            2
        );
        $this->AllEvents = $playerModel->getAllEvents($sportstype);
        $this->showediticon = PersonModel::getAllowed($this->config['edit_own_player'] ?? 0);
        $this->stats = $playerModel->getProjectStats();

        if (!empty($this->config['show_gameshistory'])) {
            $this->games = $playerMatchDataModel->getGames($this->teamPlayers);
            $this->teams = [];

            foreach ($playerModel->getProjectTeams() as $projectTeam) {
                $projectTeamId = (int) ($projectTeam->projectteamid ?? 0);
                if ($projectTeamId > 0) {
                    $this->teams[$projectTeamId] = $projectTeam;
                }
            }

            $this->gamesevents = $playerMatchDataModel->getGamesEvents(
                $this->teamPlayers,
                !empty($this->config['show_events_as_sum'])
            );
            $this->gamesstats = $playerStatisticsModel->getPlayerStatsByGame();
        }

        if (!empty($this->config['show_career_stats'])) {
            $this->stats = $playerStatisticsModel->getStats();
            $this->projectstats = $playerStatisticsModel->getPlayerStatsByProject($sportstype);
        }

        $this->extended = $this->person
            ? ExtendedFormHelper::load((string) ($this->person->extended ?? ''), 'player')
            : null;

        $personPosition = null;
        if ($this->extended) {
            $this->person_parent_positions = $this->extended->getValue('COM_SPORTSMANAGEMENT_EXT_PERSON_PARENT_POSITIONS');
            $personPosition = $this->extended->getValue('COM_SPORTSMANAGEMENT_EXT_PERSON_POSITION');
        }

        if (!$personPosition && $teamPlayer) {
            $personPosition = match ($teamPlayer->position_name ?? '') {
                'COM_SPORTSMANAGEMENT_SOCCER_P_DEFENDER' => 'hp2',
                'COM_SPORTSMANAGEMENT_SOCCER_P_FORWARD' => 'hp14',
                'COM_SPORTSMANAGEMENT_SOCCER_P_GOALKEEPER' => 'hp1',
                'COM_SPORTSMANAGEMENT_SOCCER_P_MIDFIELDER' => 'hp7',
                default => null,
            };
        }

        $this->person_position = $personPosition;
        $this->hasDescription = (string) ($teamPlayer->notes ?? '');
        $this->hasExtendedData = $this->hasExtendedValues();
        $this->hasStatus = $teamPlayer && (
            (int) ($teamPlayer->injury ?? 0) > 0
            || (int) ($teamPlayer->suspension ?? 0) > 0
            || (int) ($teamPlayer->away ?? 0) > 0
        );

        $this->playername = $this->person
            ? PersonNameFormatter::format(
                null,
                (string) ($this->person->firstname ?? ''),
                (string) ($this->person->nickname ?? ''),
                (string) ($this->person->lastname ?? ''),
                $this->config['name_format'] ?? 0
            )
            : '';

        $title = Text::sprintf('COM_SPORTSMANAGEMENT_PLAYER_INFORMATION', $this->playername);
        $this->headertitle = $title;
        $document = $this->getDocument();
        $document->setTitle($title);
        $document->getWebAssetManager()->registerAndUseStyle(
            'com_sportsmanagement.player',
            Uri::root(true) . '/components/com_sportsmanagement/assets/css/player.css'
        );

        $this->config['table_class'] = $this->config['table_class'] ?? 'table';

        // Existing player layouts use getModel() for participation calculations.
        $this->setModel($playerTimeModel, true);
    }

    private function loadPersonEventIds(PlayerModel $model): array
    {
        $db = $model->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_eventtype'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('name') . ' ASC');

        $sportsTypeId = (int) ($this->project->sports_type_id ?? 0);
        if ($sportsTypeId > 0) {
            $query->where($db->quoteName('sports_type_id') . ' = ' . $sportsTypeId);
        }

        try {
            $db->setQuery($query);
            return array_map('intval', $db->loadColumn() ?: []);
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
            return [];
        }
    }

    private function hasExtendedValues(): bool
    {
        if (!$this->extended || !method_exists($this->extended, 'getFieldsets')) {
            return false;
        }

        foreach ($this->extended->getFieldsets() as $fieldset) {
            foreach ($this->extended->getFieldset($fieldset->name) as $field) {
                if (!empty($field->value)) {
                    return true;
                }
            }
        }

        return false;
    }
}
