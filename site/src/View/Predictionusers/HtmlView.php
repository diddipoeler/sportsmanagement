<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Predictionusers;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\PredictionusersModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementPredictionHtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class HtmlView extends SportsManagementPredictionHtmlView
{
    public bool $isPredictionMember = false;
    public bool $canViewProfile = false;
    public array $memberStats = [];
    public array $favouriteTeams = [];
    public array $championTips = [];
    public array $final4Tips = [];
    public array $pointsChart = [];
    public array $rankingChart = [];
    public array $projectOptions = [];
    public int $pointsChartMax = 0;
    public int $rankingMemberMax = 0;
    private PredictionusersModel $usersModel;

    protected function prepareView(): void
    {
        $model = $this->getModel();
        if (!$model instanceof PredictionusersModel) {
            throw new \RuntimeException('Prediction users view requires PredictionusersModel.', 500);
        }
        $this->usersModel = $model;

        $this->config = array_merge($model->getPredictionTemplateConfig('predictionranking'), $this->config);
        $this->config += [
            'show_photo' => 1,
            'show_image_from' => 'prediction',
            'show_register_date' => 1,
            'show_fav_team' => 1,
            'show_slogan' => 1,
            'show_lasttip' => 1,
            'show_user_profile' => 0,
            'show_ranking' => 1,
            'show_totalpoints' => 1,
            'show_lastpoints' => 1,
            'show_counttipps' => 1,
            'show_averagepoints' => 1,
            'show_toptipps' => 1,
            'show_difftipps' => 1,
            'show_tendtipps' => 1,
            'show_flash_statistic_points' => 1,
            'show_flash_statistic_ranks' => 1,
            'show_full_name' => 1,
        ];

        if (!$this->predictionGame) {
            $this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_PRED_PREDICTION_NOT_EXISTING'), 'error');
            return;
        }

        $this->predictionMember = $model->getPredictionMember();
        $this->predictionMemberID = $model->getSelectedMemberNumericId();
        $this->projectID = $model->getProjectId();
        $this->roundID = $model->getRoundId();
        $this->isPredictionMember = $model->isPredictionMember();
        $this->allowedAdmin = $model->isAllowedAdmin();
        $this->showediticon = !empty($this->predictionMember->user_id)
            && $model->isAllowedAdmin((int) $this->predictionMember->user_id);
        $this->canViewProfile = $model->canViewMemberProfile($this->predictionMember);

        $this->lists['predictionMembers'] = $this->buildMemberSelector($model);
        $this->projectOptions = $model->getProjectOptions(true);
        $this->headertitle = Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_TITLE');
        $this->getDocument()->setTitle($this->headertitle);

        if ($this->predictionMemberID <= 0 || !$this->canViewProfile) {
            return;
        }

        $this->memberStats = $model->getMemberStats($this->predictionMember);
        $this->favouriteTeams = $model->getFavouriteTeams($this->predictionMember);
        $this->championTips = $model->getChampionTips($this->predictionMember);
        $this->final4Tips = $model->getFinal4Tips($this->predictionMember);
        $this->pointsChart = $model->getPointsChartData($this->predictionMember);
        $this->rankingChart = $model->getRankingChartData($this->predictionMember);

        foreach ($this->pointsChart as $row) {
            $this->pointsChartMax = max($this->pointsChartMax, (int) ($row['value'] ?? 0));
        }
        foreach ($this->rankingChart as $row) {
            $this->rankingMemberMax = max($this->rankingMemberMax, (int) ($row['members'] ?? 0));
        }
    }

    public function memberAvatar(): string
    {
        if (empty($this->config['show_photo']) || $this->predictionMemberID <= 0) {
            return '';
        }

        $name = $this->memberDisplayName();
        $picture = $this->usersModel->getAvatarPath($this->predictionMember, $this->config);
        if ($picture === '') {
            return '';
        }

        if (!preg_match('#^https?://#i', $picture)
            && !is_file(JPATH_ROOT . '/' . ltrim($picture, '/'))) {
            $picture = 'images/com_sportsmanagement/database/placeholders/placeholder_150_2.png';
        }

        return HTMLHelper::image($picture, $name, ['title' => $name, 'class' => 'img-fluid']);
    }

    public function memberDisplayName(): string
    {
        $name = !empty($this->config['show_full_name'])
            ? (string) ($this->predictionMember->name ?? '')
            : (string) ($this->predictionMember->username ?? '');

        if ($name === '') {
            $name = (string) ($this->predictionMember->username ?? '');
        }

        return $name;
    }

    public function memberNameLink(): string
    {
        $name = htmlspecialchars($this->memberDisplayName(), ENT_QUOTES, 'UTF-8');
        $userId = (int) ($this->predictionMember->user_id ?? 0);
        if ($userId <= 0 || !class_exists('sportsmanagementHelperRoute')) {
            return $name;
        }

        $mode = (int) ($this->config['show_user_profile'] ?? 0);
        if ($mode === 1 && method_exists('sportsmanagementHelperRoute', 'getContactRoute')) {
            return HTMLHelper::link(\sportsmanagementHelperRoute::getContactRoute($userId), $name);
        }
        if ($mode === 2 && method_exists('sportsmanagementHelperRoute', 'getUserProfileRouteCBE')) {
            $link = \sportsmanagementHelperRoute::getUserProfileRouteCBE(
                $userId,
                $this->predictionGameID,
                $this->predictionMember->pmID ?? $this->predictionMemberID
            );
            return HTMLHelper::link($link, $name);
        }

        return $name;
    }

    private function buildMemberSelector(PredictionusersModel $model): string
    {
        $options = [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_PRED_SELECT_MEMBER'))];
        foreach ($model->getMemberOptions($this->config) as $member) {
            $options[] = HTMLHelper::_('select.option', (int) $member->value, (string) $member->text);
        }

        return HTMLHelper::_(
            'select.genericlist',
            $options,
            'uid',
            'class="form-select inputbox" onchange="this.form.submit();"',
            'value',
            'text',
            $this->predictionMemberID
        );
    }
}
