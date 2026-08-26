<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Predictionranking;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\PredictionrankingModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementPredictionHtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pagination\Pagination;

final class HtmlView extends SportsManagementPredictionHtmlView
{
    public array $rankingRows = [];
    public array $projectOptions = [];
    public array $roundOptions = [];
    public array $groupOptions = [];
    public array $configentries = [];
    public array $mapconfig = [];
    public ?object $rankingProject = null;
    public ?Pagination $pagination = null;
    public ?string $predictionKmlUrl = null;
    public int $limit = 0;
    public int $limitstart = 0;
    public int $ausgabestart = 0;
    public int $ausgabeende = 0;
    public int $fromRoundID = 0;
    public int $toRoundID = 0;
    public int $groupRanking = 0;
    public int $rankingType = 0;

    protected function prepareView(): void
    {
        $model = $this->getModel();
        if (!$model instanceof PredictionrankingModel) {
            throw new \RuntimeException('Prediction ranking view requires PredictionrankingModel.', 500);
        }

        $this->headertitle = Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_TITLE');
        if (!$this->predictionGame) {
            $this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_PRED_PREDICTION_NOT_EXISTING'), 'error');
            return;
        }

        $this->projectID = $model->getProjectId();
        $this->roundID = $model->getRoundId();
        $this->fromRoundID = $model->getFromRoundId();
        $this->toRoundID = $model->getToRoundId();
        $this->groupRanking = $model->getGroupRanking();
        $this->rankingType = $model->getRankingType();
        $this->rankingProject = $model->getRankingProject();
        $this->configentries = $model->getPredictionTemplateConfig('predictionentry');
        $this->config = array_merge($this->configentries, $this->config);
        $this->config += [
            'table_class' => 'table',
            'table_class_responsive' => 'table-responsive',
            'show_rankingnav' => 1,
            'show_all_user' => 0,
            'show_all_user_google_map' => 0,
            'show_user_icon' => 0,
            'show_user_icon_width' => 50,
            'show_pred_group' => 0,
            'show_tip_details' => 1,
            'show_champion_tip' => 0,
            'show_champion_tip_result' => 0,
            'show_champion_tip_club_logo' => 0,
            'champion_logo_size' => 'logo_big',
            'show_final4_tip' => 0,
            'show_final4_tip_result' => 0,
            'show_final4_tip_club_logo' => 0,
            'final4_logo_size' => 'logo_big',
            'show_average_points' => 1,
            'show_count_tips' => 1,
            'show_count_joker' => 1,
            'show_count_topptips' => 1,
            'show_count_difftips' => 0,
            'show_count_tendtipps' => 0,
            'show_help' => 0,
            'background_color_ranking' => '#6F7860',
            'link_name_to' => 0,
        ];

        $roundIds = $this->normaliseRoundIds($this->configentries['predictionroundid'] ?? null);
        $this->projectOptions = $model->getProjectOptions();
        $this->roundOptions = $model->getRoundOptions($roundIds ?: null);
        $this->groupOptions = $model->getPredictionGroupList();

        $data = $model->getRankingData($this->config, $this->configavatar);
        $this->rankingRows = $data['rows'];
        $this->pagination = $model->getPagination((int) $data['total']);
        $this->limit = $model->getLimit();
        $this->limitstart = $model->getLimitStart();
        $this->ausgabestart = $this->limitstart + 1;
        $this->ausgabeende = min((int) $data['total'], $this->limitstart + max(1, $this->limit));

        $this->lists['ranking_array'] = [
            HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_SINGLE_RANK')),
            HTMLHelper::_('select.option', 1, Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_GROUP_RANK')),
        ];
        $this->lists['type'] = [
            HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_FULL_RANKING')),
            HTMLHelper::_('select.option', 1, Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_FIRST_HALF')),
            HTMLHelper::_('select.option', 2, Text::_('COM_SPORTSMANAGEMENT_PRED_RANK_SECOND_HALF')),
        ];

        if (!empty($this->config['show_all_user_google_map'])) {
            $this->mapconfig = $model->getMapConfig();
            $this->predictionKmlUrl = $model->buildPredictionKml($this->configavatar);
        }

        $this->getDocument()->setTitle($this->headertitle);
    }

    private function normaliseRoundIds(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('intval', $value)));
        }
        if (is_string($value) && trim($value) !== '') {
            return array_values(array_filter(array_map('intval', preg_split('/[,;\s]+/', $value) ?: [])));
        }
        return [];
    }
}
