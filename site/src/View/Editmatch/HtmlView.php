<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Editmatch;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Legacy\LegacyBootstrap;
use Diddipoeler\Component\SportsManagement\Site\Model\EditmatchModel;
use Diddipoeler\Component\SportsManagement\Site\Service\EditmatchMatchFacade;
use Diddipoeler\Component\SportsManagement\Site\Service\EditmatchViewDataService;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Joomla 5/6 frontend view for match editing.
 *
 * The primary `edit` layout is native. The specialised referee, statistics,
 * events and lineup layouts stay behind an explicit legacy-view bridge until
 * their remaining data preparation is ported into namespaced services.
 */
final class HtmlView extends SportsManagementHtmlView
{
    public EditmatchModel $model;
    public object $project;
    public object $projectws;
    public ?object $roundws = null;
    public ?object $match = null;
    public Form|false $form = false;
    public Form|false $extended = false;
    public array $lists = [];
    public array $singlematches = [];
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
        $config['template_path'] = JPATH_SITE . '/components/com_sportsmanagement/views/editmatch/tmpl';
        parent::__construct($config);
    }

    public function display($tpl = null)
    {
        if ($this->getLayout() !== 'edit') {
            return $this->displayLegacy($tpl);
        }

        $model = $this->getModel();

        if (!$model instanceof EditmatchModel) {
            throw new \RuntimeException('EditmatchModel is unavailable.', 500);
        }

        $this->model = $model;
        $this->prepareNativeEdit($model);

        return parent::display($tpl);
    }

    private function prepareNativeEdit(EditmatchModel $model): void
    {
        $service = $this->viewDataService();
        $this->registerTemplateCompatibility($service);

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

        $this->form = $model->getForm();
        $this->extended = $this->buildExtendedForm((string) ($this->match->extended ?? ''));

        $roundId = $this->input->getInt('r', (int) ($this->match->round_id ?? 0));
        $this->roundws = $service->getRound($roundId);
        $this->request_url = $this->uri->toString();

        if ((string) ($project->sport_type_name ?? '') === 'COM_SPORTSMANAGEMENT_ST_GOLF_BILLARD') {
            $this->singlematches = $model->getSingleMatchDatas((int) $this->match->id);
        }

        $this->pagination = (object) ['total' => count($this->singlematches)];
        $this->prepareMatchRelationLists($service);
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
            $label = $date !== '' ? Factory::getDate($date)->format('Y-m-d H:i') : '';
            $row->text = '(' . $label . ') - '
                . (string) ($row->t1_name ?? '')
                . ' - '
                . (string) ($row->t2_name ?? '');
        }

        return $rows;
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

    private function registerTemplateCompatibility(EditmatchViewDataService $service): void
    {
        if (!class_exists('sportsmanagementModelEditMatch', false)) {
            class_alias(EditmatchModel::class, 'sportsmanagementModelEditMatch');
        }

        EditmatchMatchFacade::setService($service);

        if (!class_exists('sportsmanagementModelMatch', false)) {
            class_alias(EditmatchMatchFacade::class, 'sportsmanagementModelMatch');
        }
    }

    private function viewDataService(): EditmatchViewDataService
    {
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        $database = SportsManagementDatabaseResolver::resolve($joomlaDatabase, $this->databaseSelector);

        return new EditmatchViewDataService($database);
    }

    private function displayLegacy($tpl = null)
    {
        LegacyBootstrap::bootForView('editmatch');
        $legacyViewFile = JPATH_SITE . '/components/com_sportsmanagement/views/editmatch/view.html.php';

        if (!is_file($legacyViewFile)) {
            throw new \RuntimeException('SportsManagement legacy Editmatch view is unavailable.', 500);
        }

        require_once $legacyViewFile;

        if (!class_exists('sportsmanagementViewEditMatch', false)) {
            throw new \RuntimeException('SportsManagement legacy Editmatch view class is unavailable.', 500);
        }

        $legacy = new \sportsmanagementViewEditMatch([
            'template_path' => JPATH_SITE . '/components/com_sportsmanagement/views/editmatch/tmpl',
        ]);
        $model = $this->getModel();

        if ($model) {
            $legacy->setModel($model, true);
        }

        $legacy->setLayout($this->getLayout());

        return $legacy->display($tpl);
    }
}
