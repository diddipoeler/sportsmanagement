<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Rankingalltime;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\RankingalltimeCalculatorModel;
use Diddipoeler\Component\SportsManagement\Site\Model\RankingalltimeModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

/** Joomla 5/6 site view for the all-time ranking. */
final class HtmlView extends SportsManagementProjectHtmlView
{
    public array $ranking_order = [];
    public array $projectids = [];
    public array $projectnames = [];
    public string $project_ids = '';
    public array $teams = [];
    public array $matches = [];
    public array $ranking = [];
    public array $tableconfig = [];
    public array $currentRanking = [];
    public array $colors = [];
    public array $divisions = [];
    public string $action = '';

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->addTemplatePath(JPATH_SITE . '/components/com_sportsmanagement/views/rankingalltime/tmpl');
    }

    protected function prepareView(): void
    {
        $dataModel = $this->getModel();
        if (!$dataModel instanceof RankingalltimeModel) {
            throw new \RuntimeException('All-time ranking view requires RankingalltimeModel.', 500);
        }

        $comeFromMenu = $this->mergeActiveMenuParameters();
        $this->ranking_order = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) ($this->config['ranking_order'] ?? ''))
        )));

        $databaseSelector = $this->input->getInt('cfg_which_database', 0);
        $dataModel->setDatabaseSelector($databaseSelector);

        $calculator = new RankingalltimeCalculatorModel();
        $calculator->setDatabaseSelector($databaseSelector);

        $this->getDocument()->getWebAssetManager()->registerAndUseScript(
            'com_sportsmanagement.site.core',
            'components/com_sportsmanagement/assets/js/smsportsmanagement.js'
        );

        $this->projectids = $dataModel->getProjectIds();
        $this->projectnames = $dataModel->getProjectNames();
        $this->project_ids = implode(',', $this->projectids);

        $teamRows = $dataModel->getAllTeams($this->projectids);
        $this->teams = $dataModel->initialiseTeams($teamRows);
        $calculator->_teams = $teamRows;
        $calculator->teams = $this->teams;
        $calculator->project_ids_array = $this->projectids;
        $calculator->project_ids = $this->project_ids;

        RankingalltimeCalculatorModel::$rankingalltimetips[] = Text::_(
            'Wir verarbeiten ' . count($this->projectids) . ' Projekte/Saisons !'
        );
        RankingalltimeCalculatorModel::$rankingalltimetips[] = Text::_(
            'Wir verarbeiten ' . count($this->teams) . ' Vereine !'
        );

        $forceRankingCache = (bool) ComponentHelper::getParams('com_sportsmanagement')->get('force_ranking_cache', 0);
        if ($forceRankingCache) {
            $this->matches = [];
            $calculator->_matches = [];
        } else {
            $this->matches = $dataModel->getAllMatches($this->projectids);
            $calculator->_matches = $this->matches;
            RankingalltimeCalculatorModel::$rankingalltimetips[] = Text::_(
                'Wir verarbeiten ' . count($this->matches) . ' Spiele !'
            );
        }

        $useNegPoints = (int) ($this->config['use_negpoints_ranking_all_time'] ?? 0);
        $this->ranking = $calculator->getAllTimeRanking($useNegPoints);
        $this->tableconfig = $calculator->getAllTimeParams($comeFromMenu, $this->config);
        $this->currentRanking = $calculator->getCurrentRanking($this->ranking_order);
        $this->config = $calculator->getAllTimeParams($comeFromMenu, $this->config);

        $this->action = $this->uri->toString();
        $this->colors = $dataModel->parseColors((string) ($this->config['colors'] ?? ''));
        $this->getDocument()->setTitle(Text::_('COM_SPORTSMANAGEMENT_RANKINGALLTIME_PAGE_TITLE'));

        $this->warnings = RankingalltimeCalculatorModel::$rankingalltimewarnings;
        $this->tips = RankingalltimeCalculatorModel::$rankingalltimetips;
        $this->notes = RankingalltimeCalculatorModel::$rankingalltimenotes;
    }

    private function mergeActiveMenuParameters(): bool
    {
        $menu = $this->app->getMenu();
        $item = $menu->getActive();

        if (!$item || (($item->query['view'] ?? '') !== 'rankingalltime')) {
            return false;
        }

        foreach ($menu->getParams((int) $item->id)->toArray() as $key => $value) {
            $this->config[$key] = $value;
        }

        return true;
    }
}
