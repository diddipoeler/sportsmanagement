<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Nextmatch;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\MatchTimeHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\NextmatchModel;
use Diddipoeler\Component\SportsManagement\Site\Model\NextmatchViewDataModel;
use Diddipoeler\Component\SportsManagement\Site\Model\PlaygroundModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Joomla 5/6 MVC view for the next-match page.
 *
 * Presentation compatibility remains enabled until the migrated tmpl files no
 * longer call the historical helper classes.
 */
final class HtmlView extends SportsManagementProjectHtmlView
{
    public ?NextmatchModel $model = null;
    public ?object $match = null;
    public ?object $playground = null;
    public array $tableconfig = [];
    public array $overallevents = [];
    public array $teams = [];
    public array $referees = [];
    public array $games = [];
    public array $gamesteams = [];
    public array $previousx = [];
    public array $allteams = [];
    public array $matchcommentary = [];
    public array $gesamtspiele = [];
    public array $statgames = [];
    public array $alloverevents = [];
    public string $newmatchtext = '';
    public string $oldmatchtext = '';
    public $homeranked = null;
    public $awayranked = null;
    public $chances = null;
    public $home_highest_home_win = null;
    public $away_highest_home_win = null;
    public $home_highest_home_def = null;
    public $away_highest_home_def = null;
    public $home_highest_away_win = null;
    public $away_highest_away_win = null;
    public $home_highest_away_def = null;
    public $away_highest_away_def = null;
    public array $output = [];

    protected function prepareProjectContext(): void
    {
        $model = $this->getModel();

        if ($model instanceof NextmatchModel) {
            $model->setDatabaseSelector($model->getDatabaseSelector());
            $this->match = $model->getMatch();
        }

        parent::prepareProjectContext();
    }

    protected function prepareView(): void
    {
        $model = $this->getModel();
        if (!$model instanceof NextmatchModel) {
            throw new \RuntimeException('Nextmatch view requires NextmatchModel.', 500);
        }
        $this->model = $model;

        $databaseSelector = $model->getDatabaseSelector();
        $model->setDatabaseSelector($databaseSelector);
        $this->match ??= $model->getMatch();

        $viewDataModel = new NextmatchViewDataModel();
        $viewDataModel->setDatabaseSelector($databaseSelector);
        $playgroundModel = new PlaygroundModel();
        $playgroundModel->setDatabaseSelector($databaseSelector);

        $this->getDocument()->getWebAssetManager()->registerAndUseScript(
            'com_sportsmanagement.nextmatch',
            Uri::root(true) . '/components/com_sportsmanagement/assets/js/smsportsmanagement.js',
            ['version' => 'auto']
        );

        $projectId = (int) ($this->project->id ?? 0);
        $this->tableconfig = $model->getTemplateConfig('ranking');
        $this->overallevents = $viewDataModel->getProjectEvents($projectId);
        $this->alloverevents = $viewDataModel->getProjectEventTotals($projectId);

        if (!isset($this->overallconfig['seperator'])) {
            $this->overallconfig['seperator'] = ':';
        }

        $this->config['extended_cols'] = $this->overallconfig['extended_cols'] ?? 0;
        $this->config['show_project_kunena_link'] = $this->overallconfig['show_project_kunena_link'] ?? 0;
        $this->config['table_class'] = (string) ($this->config['table_class'] ?? 'table');

        if ($this->match) {
            $this->prepareMatchContext($model, $viewDataModel, $playgroundModel, $databaseSelector);
        }

        $this->buildHeadToHeadStats();

        $pageTitle = Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_PAGE_TITLE');
        if (isset($this->teams[0], $this->teams[1])) {
            $pageTitle .= ': ' . (string) ($this->teams[0]->name ?? '')
                . ' ' . Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_VS') . ' '
                . (string) ($this->teams[1]->name ?? '');
        }

        $this->headertitle = $pageTitle;
        $this->getDocument()->setTitle($pageTitle);
    }

    private function prepareMatchContext(
        NextmatchModel $model,
        NextmatchViewDataModel $viewDataModel,
        PlaygroundModel $playgroundModel,
        int $databaseSelector
    ): void {
        $this->newmatchtext = $this->relatedMatchText(
            $viewDataModel,
            (int) ($this->match->new_match_id ?? 0)
        );
        $this->oldmatchtext = $this->relatedMatchText(
            $viewDataModel,
            (int) ($this->match->old_match_id ?? 0)
        );

        $this->teams = $model->getMatchTeams() ?: [];
        $this->referees = $model->getReferees();
        PlaygroundModel::$cfg_which_database = $databaseSelector === 1 ? 1 : 0;
        $this->playground = PlaygroundModel::getPlayground((int) ($this->match->playground_id ?? 0));
        $this->homeranked = $model->getHomeRanked();
        $this->awayranked = $model->getAwayRanked();
        $this->chances = $model->getChances();
        $this->home_highest_home_win = $model->getHomeHighestHomeWin();
        $this->away_highest_home_win = $model->getAwayHighestHomeWin();
        $this->home_highest_home_def = $model->getHomeHighestHomeDef();
        $this->away_highest_home_def = $model->getAwayHighestHomeDef();
        $this->home_highest_away_win = $model->getHomeHighestAwayWin();
        $this->away_highest_away_win = $model->getAwayHighestAwayWin();
        $this->home_highest_away_def = $model->getHomeHighestAwayDef();
        $this->away_highest_away_def = $model->getAwayHighestAwayDef();
        $this->games = $model->getGames();
        $this->gamesteams = $model->getTeamsFromMatches($this->games, $this->config);
        $this->previousx = $model->getPreviousX($this->config);

        foreach ($model->getProjectTeams(0) as $team) {
            $projectTeamId = (int) ($team->projectteamid ?? 0);
            if ($projectTeamId > 0) {
                $this->allteams[$projectTeamId] = $team;
            }
        }

        $this->matchcommentary = $viewDataModel->getMatchCommentary((int) $this->match->id);
    }

    private function relatedMatchText(NextmatchViewDataModel $viewDataModel, int $matchId): string
    {
        if ($matchId <= 0) {
            return '';
        }

        $match = $viewDataModel->getMatchText($matchId);
        if (!$match) {
            return '';
        }

        $matchTime = MatchTimeHelper::format($match, $this->config, $this->overallconfig, $this->project);
        $matchDate = HTMLHelper::date(
            (string) ($match->match_date ?? ''),
            Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_GAMES_DATE')
        );

        return trim(
            $matchDate . ' ' . $matchTime . ', '
            . (string) ($match->t1name ?? '') . ' - ' . (string) ($match->t2name ?? '')
        );
    }

    private function buildHeadToHeadStats(): void
    {
        if (!$this->games || !isset($this->teams[0])) {
            return;
        }

        $focusTeamId = (int) ($this->teams[0]->id ?? 0);

        foreach ($this->games as $game) {
            if (!isset($game->team1_result, $game->team2_result)) {
                continue;
            }

            $league = (string) ($game->leaguename ?? '');
            if (!isset($this->gesamtspiele[$league])) {
                $this->gesamtspiele[$league] = (object) [
                    'gesamtspiele' => 0,
                    'gewonnen' => 0,
                    'verloren' => 0,
                    'unentschieden' => 0,
                    'plustore' => 0,
                    'minustore' => 0,
                    'localwin' => 0,
                    'localdraw' => 0,
                    'locallost' => 0,
                    'awaywin' => 0,
                    'awaydraw' => 0,
                    'awaylost' => 0,
                ];
            }

            $stats = $this->gesamtspiele[$league];
            $stats->gesamtspiele++;
            $homeResult = (int) $game->team1_result;
            $awayResult = (int) $game->team2_result;

            if ((int) ($game->team1_id ?? 0) === $focusTeamId) {
                if ($homeResult > $awayResult) {
                    $stats->gewonnen++;
                    $stats->localwin++;
                } elseif ($homeResult < $awayResult) {
                    $stats->verloren++;
                    $stats->locallost++;
                } else {
                    $stats->unentschieden++;
                    $stats->localdraw++;
                }
                $stats->plustore += $homeResult;
                $stats->minustore += $awayResult;
                $this->incrementScore('home', $homeResult, $awayResult);
                $this->incrementScore('gesamt', $homeResult, $awayResult);
            } elseif ((int) ($game->team2_id ?? 0) === $focusTeamId) {
                if ($awayResult > $homeResult) {
                    $stats->gewonnen++;
                    $stats->awaywin++;
                } elseif ($awayResult < $homeResult) {
                    $stats->verloren++;
                    $stats->awaylost++;
                } else {
                    $stats->unentschieden++;
                    $stats->awaydraw++;
                }
                $stats->plustore += $awayResult;
                $stats->minustore += $homeResult;
                $this->incrementScore('away', $homeResult, $awayResult);
                $this->incrementScore('gesamt', $awayResult, $homeResult);
            }
        }
    }

    private function incrementScore(string $bucket, int $home, int $away): void
    {
        $key = $home . '-' . $away;
        $this->statgames[$bucket][$key] = (int) ($this->statgames[$bucket][$key] ?? 0) + 1;
    }
}
