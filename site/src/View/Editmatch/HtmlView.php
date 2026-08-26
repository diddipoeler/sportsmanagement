<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Editmatch;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EditmatchModel;
use Diddipoeler\Component\SportsManagement\Site\Service\EditmatchViewDataService;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/** Joomla 5/6 frontend view for match editing. */
final class HtmlView extends SportsManagementHtmlView
{
    use EditmatchEventViewTrait;
    use EditmatchStatsViewTrait;
    use EditmatchLineupViewTrait;

    private const NATIVE_LAYOUTS = ['default', 'edit', 'editreferees', 'editevents', 'editstats', 'editlineup'];

    private const LAYOUT_ALIASES = [
        'edit_3' => 'edit',
        'edit_4' => 'edit',
        'editreferees_3' => 'editreferees',
        'editreferees_4' => 'editreferees',
        'editevents_3' => 'editevents',
        'editevents_4' => 'editevents',
        'editstats_3' => 'editstats',
        'editstats_4' => 'editstats',
        'editlineup_3' => 'editlineup',
        'editlineup_4' => 'editlineup',
    ];

    public EditmatchModel $model;
    public object $project;
    public object $projectws;
    public ?object $roundws = null;
    public ?object $match = null;
    public ?object $item = null;
    public Form|false $form = false;
    public Form|false $extended = false;
    public array $lists = [];
    public array $singlematches = [];
    public array $positions = [];
    public array $table_config = ['alternative_legs' => ''];
    public int $project_id = 0;
    public int $eventsprojecttime = 0;
    public int $useeventtime = 0;
    public int $doubleevents = 0;
    public string $request_url = '';
    public string $view = 'editmatch';
    public object $pagination;
    public string $sortDirection = 'ASC';
    public string $sortColumn = 'mc.id';

    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function display($tpl = null)
    {
        $requestedLayout = strtolower($this->getLayout());
        $layout = self::LAYOUT_ALIASES[$requestedLayout] ?? $requestedLayout;

        if ($layout !== $requestedLayout) {
            $this->setLayout($layout);
        }

        if (!in_array($layout, self::NATIVE_LAYOUTS, true)) {
            throw new \RuntimeException('Unsupported SportsManagement editmatch layout: ' . $requestedLayout, 404);
        }

        $model = $this->getModel();

        if (!$model instanceof EditmatchModel) {
            throw new \RuntimeException('EditmatchModel is unavailable.', 500);
        }

        $this->model = $model;
        $service = $this->viewDataService();
        $this->prepareNativeContext($model, $service);

        if ($layout === 'editreferees') {
            $this->prepareRefereeLayout($service);
        } elseif ($layout === 'editevents') {
            $this->prepareEventLayout($service);
        } elseif ($layout === 'editstats') {
            $this->prepareStatsLayout($service);
        } elseif ($layout === 'editlineup') {
            $this->prepareLineupLayout($service);
        } else {
            $this->prepareEditLayout($service);
        }

        return parent::display($tpl);
    }

    private function prepareNativeContext(EditmatchModel $model, EditmatchViewDataService $service): void
    {
        $this->project_id = $this->input->getInt('p', 0);
        $project = $service->getProjectContext($this->project_id);

        if (!$project) {
            throw new \RuntimeException('SportsManagement project is unavailable.', 404);
        }

        $this->project = $project;
        $this->projectws = $project;
        $this->eventsprojecttime = (int) ($project->game_regular_time ?? 0);
        $this->useeventtime = (int) ($project->useeventtime ?? 0);
        $this->doubleevents = (int) ($project->double_events ?? 0);
        $this->table_config = [
            'alternative_legs' => (string) $this->params->get('alternative_legs', ''),
        ];

        $this->app->setUserState('com_sportsmanagement.pid', (int) $project->id);
        $this->app->setUserState('com_sportsmanagement.season_id', (int) ($project->season_id ?? 0));

        $this->match = $model->getData();

        if (!$this->match) {
            throw new \RuntimeException('SportsManagement match is unavailable.', 404);
        }

        $this->item = $this->match;
        $this->form = $model->getForm();
        $this->extended = $this->buildExtendedForm((string) ($this->match->extended ?? ''));

        $roundId = $this->input->getInt('r', (int) ($this->match->round_id ?? 0));
        $this->roundws = $service->getRound($roundId);
        $this->request_url = $this->uri->toString();
        $this->pagination = (object) ['total' => 0];
    }

    private function prepareEditLayout(EditmatchViewDataService $service): void
    {
        if (!$this->match) {
            return;
        }

        if ((string) ($this->project->sport_type_name ?? '') === 'COM_SPORTSMANAGEMENT_ST_GOLF_BILLARD') {
            $matchId = (int) $this->match->id;
            $this->singlematches = $this->model->getSingleMatchDatas($matchId);
            $this->lists['homeplayer'] = $service->getMatchPersons(
                (int) $this->match->projectteam1_id,
                $matchId
            );
            $this->lists['awayplayer'] = $service->getMatchPersons(
                (int) $this->match->projectteam2_id,
                $matchId
            );
            $this->getDocument()->getWebAssetManager()->registerAndUseScript(
                'com_sportsmanagement.editmatch-singlematch',
                'components/com_sportsmanagement/assets/js/editmatch-singlematch.js'
            );
        }

        $this->pagination = (object) ['total' => count($this->singlematches)];
        $this->prepareMatchRelationLists($service);
    }

    private function prepareRefereeLayout(EditmatchViewDataService $service): void
    {
        if (!$this->match) {
            return;
        }

        $matchId = (int) $this->match->id;
        $allReferees = $service->getRefereeRoster(0, $matchId);
        $inRoster = array_map('intval', array_keys($allReferees));
        $available = $service->getProjectReferees($inRoster, $this->project_id);
        $availableOptions = [];

        foreach ($available as $referee) {
            $availableOptions[] = HTMLHelper::_(
                'select.option',
                (int) $referee->value,
                $this->formatPersonName($referee)
                . ' - (' . strtolower(Text::_((string) ($referee->positionname ?? ''))) . ')'
            );
        }

        $this->lists['team_referees'] = HTMLHelper::_(
            'select.genericlist',
            $availableOptions,
            'roster[]',
            'style="font-size:12px;height:auto;min-width:15em;" class="inputbox" multiple="true" size="'
            . max(10, count($availableOptions)) . '"',
            'value',
            'text'
        );

        $projectPositions = $service->getProjectPositionsOptions($this->project_id, 3);
        $selectPositions = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_REF_FUNCTION')),
        ];
        $selectPositions = array_merge($selectPositions, array_values($projectPositions));
        $this->lists['projectpositions'] = HTMLHelper::_(
            'select.genericlist',
            $selectPositions,
            'project_position_id',
            'class="inputbox" size="1"',
            'value',
            'text'
        );

        if ($projectPositions === []) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_REF_POS'), Log::WARNING, 'jsmerror');
            $this->positions = [];
            return;
        }

        foreach (array_values($projectPositions) as $key => $position) {
            $assignedOptions = [];

            foreach ($service->getRefereeRoster((int) $position->value, $matchId) as $referee) {
                $assignedOptions[] = HTMLHelper::_(
                    'select.option',
                    (int) $referee->value,
                    $this->formatPersonName($referee)
                );
            }

            $this->lists['team_referees' . $key] = HTMLHelper::_(
                'select.genericlist',
                $assignedOptions,
                'position' . $key . '[]',
                'style="font-size:12px;height:auto;min-width:15em;" class="position-starters" multiple="true"',
                'value',
                'text'
            );
        }

        $this->positions = array_values($projectPositions);
        $this->getDocument()->getWebAssetManager()->registerAndUseScript(
            'com_sportsmanagement.editmatch-lists',
            'components/com_sportsmanagement/assets/js/editmatch-lists.js'
        );
    }

    private function prepareMatchRelationLists(EditmatchViewDataService $service): void
    {
        if (!$this->match) {
            return;
        }

        $matchId = (int) $this->match->id;
        $newMatchId = (int) ($this->match->new_match_id ?? 0);
        $oldMatchId = (int) ($this->match->old_match_id ?? 0);

        $oldMatches = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_OLD_MATCH')),
        ];
        $oldMatches = array_merge(
            $oldMatches,
            $this->formatRelationOptions($service->getMatchRelationsOptions($this->project_id, [$matchId, $newMatchId]))
        );

        $newMatches = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NEW_MATCH')),
        ];
        $newMatches = array_merge(
            $newMatches,
            $this->formatRelationOptions($service->getMatchRelationsOptions($this->project_id, [$matchId, $oldMatchId]))
        );

        $this->lists['old_match'] = HTMLHelper::_(
            'select.genericlist',
            $oldMatches,
            'old_match_id',
            'class="inputbox" size="1"',
            'value',
            'text',
            $oldMatchId
        );
        $this->lists['new_match'] = HTMLHelper::_(
            'select.genericlist',
            $newMatches,
            'new_match_id',
            'class="inputbox" size="1"',
            'value',
            'text',
            $newMatchId
        );
        $this->lists['count_result'] = HTMLHelper::_(
            'select.booleanlist',
            'count_result',
            'class="btn btn-primary"',
            (int) ($this->match->count_result ?? 0)
        );

        $teamWon = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_NO_TEAM')),
            HTMLHelper::_('select.option', '1', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_HOME_TEAM')),
            HTMLHelper::_('select.option', '2', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_AWAY_TEAM')),
            HTMLHelper::_('select.option', '3', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_LOSS_BOTH_TEAMS')),
            HTMLHelper::_('select.option', '4', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_WON_BOTH_TEAMS')),
        ];
        $this->lists['team_won'] = HTMLHelper::_(
            'select.genericlist',
            $teamWon,
            'team_won',
            'class="inputbox" size="1"',
            'value',
            'text',
            (int) ($this->match->team_won ?? 0)
        );
    }

    /** @param array<int,object> $rows */
    private function formatRelationOptions(array $rows): array
    {
        foreach ($rows as $row) {
            $date = trim((string) ($row->match_date ?? ''));
            $timezone = trim((string) ($row->timezone ?? '')) ?: 'Europe/Berlin';

            if ($date !== '') {
                try {
                    $dateObject = Factory::getDate($date, 'UTC');
                    $dateObject->setTimezone(new \DateTimeZone($timezone));
                    $label = $dateObject->format('Y-m-d H:i');
                } catch (\Throwable) {
                    $label = $date;
                }
            } else {
                $label = '';
            }

            $row->text = '(' . $label . ') - '
                . (string) ($row->t1_name ?? '')
                . ' - '
                . (string) ($row->t2_name ?? '');
        }

        return $rows;
    }

    private function formatPersonName(object $person): string
    {
        $firstname = trim((string) ($person->firstname ?? ''));
        $nickname = trim((string) ($person->nickname ?? ''));
        $lastname = trim((string) ($person->lastname ?? ''));
        $parts = array_values(array_filter([
            $firstname,
            $nickname !== '' ? "'" . $nickname . "'" : '',
            $lastname,
        ], static fn (string $part): bool => $part !== ''));

        return implode(' ', $parts);
    }

    private function buildExtendedForm(string $data): Form|false
    {
        $registry = new Registry();

        if ($data !== '') {
            $registry->loadString($data);
        }

        $form = Form::getInstance(
            'editmatch-extended',
            JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/assets/extended/match.xml',
            ['control' => 'extended'],
            false,
            '/config'
        );

        if (!$form) {
            return false;
        }

        $form->bind($registry);

        return $form;
    }

    private function viewDataService(): EditmatchViewDataService
    {
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        $selector = $this->input->getInt(
            'cfg_which_database',
            (int) $this->app->getUserState('com_sportsmanagement.cfg_which_database', 0)
        );
        $sportsDatabase = SportsManagementDatabaseResolver::resolve($joomlaDatabase, $selector);

        return new EditmatchViewDataService($joomlaDatabase, $sportsDatabase);
    }
}
