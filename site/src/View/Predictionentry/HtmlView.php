<?php
/**
 * Native Joomla 5/6 frontend SportsManagement Predictionentry view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Predictionentry;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\PredictionentryModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementPredictionHtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class HtmlView extends SportsManagementPredictionHtmlView
{
    public bool $isPredictionMember = false;
    public bool $isNotApprovedMember = false;
    public bool $isNewMember = false;
    public bool $tippEntryDone = false;
    public bool $canEnterTips = false;
    public bool $roundExtrasEditable = false;
    public array $matches = [];
    public array $projectOptions = [];
    public array $roundOptions = [];
    public object $entryProject;
    public object $roundExtras;
    public int $jokerCount = 0;
    public string $websiteName = '';
    private PredictionentryModel $entryModel;

    /**
     * Prepare the prediction entry form and verify current membership.
     * Requires PredictionentryModel and an existing prediction game, then
     * loads project, round, member and entry configuration. Hides match
     * entries for users without permission to submit predictions.
     * @return void
     * @throws \RuntimeException If the model or prediction game is unavailable.
     */
    protected function prepareView(): void
    {
        $model = $this->getModel();
        if (!$model instanceof PredictionentryModel) {
            throw new \RuntimeException('Prediction entry view requires PredictionentryModel.', 500);
        }
        $this->entryModel = $model;
        if (!$this->predictionGame) {
            throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_PRED_PREDICTION_NOT_EXISTING'), 404);
        }
        $this->config = $model->getEntryConfig();
        $this->configavatar = $model->getPredictionTemplateConfig('predictionusers');
        $this->predictionMember = $model->getEntryMember();
        $this->predictionMemberID = $model->getSelectedMemberNumericId();
        $this->projectID = $model->getProjectId();
        $this->roundID = $model->getRoundId();
        $this->entryProject = $model->getEntryProject() ?: (object) [];
        $this->predictionProjectS = $model->getPredictionProjects();
        $this->predictionProjects = $this->predictionProjectS;
        $this->projectOptions = $model->getProjectOptions();
        $this->roundOptions = $model->getRoundOptions();
        $this->isPredictionMember = $model->isCurrentUserMember();
        $this->isNotApprovedMember = $model->isNotApprovedCurrentMember();
        $this->isNewMember = $model->isNewMemberRequest();
        $this->tippEntryDone = $model->isEntryDoneRequest();
        $this->canEnterTips = $model->canActAsEntryMember($this->predictionMember);
        $this->websiteName = (string) $this->app->get('sitename', '');
        $this->headertitle = Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_SECTION_TITLE');
        $this->getDocument()->setTitle(Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_TITLE'));

        if ($this->allowedAdmin) {
            $this->lists['predictionMembers'] = $this->buildMemberSelector($model);
        }

        if (!$this->canEnterTips) {
            $this->matches = [];
            $this->roundExtras = (object) [];
            return;
        }

        $this->matches = $model->getEntryMatches();
        $this->roundExtras = $model->getRoundExtras();
        $this->jokerCount = $model->getMemberProjectJokerCount();
        $this->roundExtrasEditable = $model->isRoundExtrasEditable($this->matches);
    }

    /**
     * Generate the prediction help text for the selected game's scoring mode.
     * @return string Model-generated explanation of scoring and entries.
     */
    public function helpText(): string
    {
        return $this->entryModel->createHelpText((int) ($this->entryProject->mode ?? 0));
    }

    /**
     * Build the administrator's prediction member selection dropdown.
     * Marks unapproved members and selects the currently active member.
     * @param PredictionentryModel $model Native prediction entry model.
     * @return string HTML for the Joomla member selector.
     */
    private function buildMemberSelector(PredictionentryModel $model): string
    {
        $options = [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_PRED_SELECT_MEMBER'))];
        foreach ($model->getMemberOptions() as $member) {
            $label = (string) ($member->text ?? '');
            if (isset($member->approved) && (int) $member->approved !== 1) {
                $label .= ' (' . Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NOT_MEMBER_INFO_04') . ')';
            }
            $options[] = HTMLHelper::_('select.option', (int) ($member->value ?? 0), $label);
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
