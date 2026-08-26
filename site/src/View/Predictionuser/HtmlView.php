<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Predictionuser;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\PredictionuserModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementPredictionHtmlView;
use Joomla\CMS\Language\Text;

final class HtmlView extends SportsManagementPredictionHtmlView
{
    public array $editProjects = [];
    public array $groupOptions = [];
    public bool $canChangeGroup = false;
    public bool $isPredictionAdmin = false;
    public int $memberID = 0;
    private PredictionuserModel $editorModel;

    protected function prepareView(): void
    {
        $model = $this->getModel();
        if (!$model instanceof PredictionuserModel) {
            throw new \RuntimeException('Prediction user editor requires PredictionuserModel.', 500);
        }
        $this->editorModel = $model;

        $this->config = $model->getEditConfig();
        $this->config += [
            'show_full_name' => 1,
            'allow_alias' => 1,
            'input_alias_length' => 30,
            'edit_slogan' => 1,
            'input_slogan_length' => 50,
            'edit_reminder' => 0,
            'edit_receipt' => 0,
            'edit_favteam' => 1,
            'edit_avatar_upload' => 1,
            'show_final4_tip' => 0,
        ];

        if (!$this->predictionGame) {
            throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_PRED_PREDICTION_NOT_EXISTING'), 404);
        }

        $this->predictionMember = $model->getEditableMember();
        $this->memberID = $model->getSelectedMemberNumericId();
        $this->predictionMemberID = $this->memberID;
        $this->projectID = $model->getProjectId();
        $this->roundID = $model->getRoundId();
        $this->isPredictionAdmin = $model->isAllowedAdmin();
        $this->allowedAdmin = $this->isPredictionAdmin;

        if ($this->memberID <= 0) {
            throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_SELECT_EXISTING_MEMBER'), 404);
        }
        if (!$model->canEditMember($this->predictionMember)) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $this->showediticon = false;
        $this->editProjects = $model->getEditProjects($this->predictionMember);
        $this->groupOptions = $model->getPredictionGroupOptions();
        $this->canChangeGroup = $model->canChangeGroup();
        $this->headertitle = Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_TITLE');
        $this->getDocument()->setTitle($this->headertitle);
    }

    public function displayName(): string
    {
        $name = !empty($this->config['show_full_name'])
            ? (string) ($this->predictionMember->name ?? '')
            : (string) ($this->predictionMember->username ?? '');
        if ($name === '') {
            $name = (string) ($this->predictionMember->username ?? '');
        }
        return $name;
    }

    public function registerDate(): string
    {
        $value = (string) ($this->predictionMember->pmRegisterDate ?? $this->predictionMember->registerDate ?? '');
        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return '';
        }
        return substr($value, 0, 10);
    }

    public function registerTime(): string
    {
        $value = (string) ($this->predictionMember->pmRegisterDate ?? $this->predictionMember->registerDate ?? '');
        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return '';
        }
        return substr($value, 11, 8);
    }
}
