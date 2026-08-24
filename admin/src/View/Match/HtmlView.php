<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Match;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtendedFormHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Model\MatchModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator views for one match. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;
    public ?object $match = null;
    public ?object $project = null;
    public ?Form $extended = null;
    public array $oldMatchOptions = [];
    public array $newMatchOptions = [];
    public array $refereePositions = [];
    public array $availableReferees = [];
    public array $assignedReferees = [];
    public bool $teamsAsReferees = false;
    public int $projectId = 0;
    public string $tmpl = '';

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $input->set('hidemainmenu', true);

        $layout = preg_replace('/_[34]$/', '', strtolower((string) $this->getLayout()));
        $this->setLayout($layout ?: 'edit');

        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');
        $this->tmpl = $input->getCmd('tmpl', '');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if (!$this->form) {
            throw new \RuntimeException('Match form could not be loaded.', 500);
        }

        $model = $this->getModel();

        if (!$model instanceof MatchModel) {
            throw new \RuntimeException('Match view requires MatchModel.', 500);
        }

        $matchId = (int) ($this->item->id ?? $input->getInt('id', 0));
        $this->match = $model->getMatchData($matchId);

        if (!$this->match) {
            $this->match = (object) [
                'id' => $matchId,
                'project_id' => 0,
                'hometeam' => '',
                'awayteam' => '',
                'old_match_id' => 0,
                'new_match_id' => 0,
                'team1_legs' => 0,
                'team2_legs' => 0,
            ];
        }

        $this->projectId = (int) ($this->match->project_id ?? 0);

        if ($this->projectId <= 0) {
            $this->projectId = (int) $app->getUserState('com_sportsmanagement.pid', 0);
        }

        if ($this->projectId > 0) {
            $app->setUserState('com_sportsmanagement.pid', $this->projectId);
            $this->project = ProjectModel::getProject($this->projectId);
        }

        if ($this->getLayout() === 'editreferees') {
            $this->prepareReferees($model, $matchId);
            parent::display($tpl);
            return;
        }

        $oldMatchId = (int) ($this->item->old_match_id ?? $this->match->old_match_id ?? 0);
        $newMatchId = (int) ($this->item->new_match_id ?? $this->match->new_match_id ?? 0);
        $this->oldMatchOptions = $model->getMatchRelationsOptions($this->projectId, [$matchId, $newMatchId]);
        $this->newMatchOptions = $model->getMatchRelationsOptions($this->projectId, [$matchId, $oldMatchId]);

        $this->extended = (new ExtendedFormHelper())->load(
            'extended',
            'match',
            (string) ($this->item->extended ?? $this->match->extended ?? '')
        );

        $this->prepareScoreFields();
        $this->addToolbar($matchId <= 0);

        parent::display($tpl);
    }

    private function prepareReferees(MatchModel $model, int $matchId): void
    {
        if ($matchId <= 0 || $this->projectId <= 0) {
            throw new \RuntimeException('Match referee assignment requires an existing match and project.', 400);
        }

        $this->teamsAsReferees = (int) ($this->project->teams_as_referees ?? 0) === 1;
        $this->refereePositions = $model->getRefereePositions($this->projectId);
        $this->availableReferees = $model->getAvailableRefereeOptions(
            $this->projectId,
            $matchId,
            $this->teamsAsReferees
        );
        $this->assignedReferees = $model->getAssignedReferees(
            $this->projectId,
            $matchId,
            $this->teamsAsReferees
        );

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ER_TITLE'), 'users');
    }

    private function prepareScoreFields(): void
    {
        if ((int) ($this->project->use_legs ?? 0) === 1) {
            return;
        }

        $this->form->removeField('team1_legs');
        $this->form->removeField('team2_legs');
    }

    private function addToolbar(bool $isNew): void
    {
        ToolbarHelper::title(
            Text::_($isNew ? 'COM_SPORTSMANAGEMENT_MATCH_NEW' : 'COM_SPORTSMANAGEMENT_MATCH_EDIT'),
            'calendar'
        );
        ToolbarHelper::apply('match.apply');
        ToolbarHelper::save('match.save');
        ToolbarHelper::cancel(
            $this->tmpl === 'component' ? 'match.cancelmodal' : 'match.cancel',
            $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE'
        );
    }
}
