<?php
/**
 * Native Joomla 5/6 administrator list view for project matches.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Matches;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtraSelectOptionsHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Model\MatchesModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectModel;
use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

/** Native Joomla 5/6 administrator list view for project matches. */
final class HtmlView extends BaseHtmlView
{
    /**
     * Resolve the active backend Joomla application for match administration.
     *
     * Uses the component application resolver so request parameters, current
     * identity and stored project/round selections are read from the live
     * administrator client, never from an independent site application.
     *
     * @return CMSApplicationInterface Active administrator request context.
     */
    private static function administratorApplication(): CMSApplicationInterface
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }

    public array $items = [];
    public array $matches = [];
    public $pagination;
    public $state;
    public $filterForm;
    public array $activeFilters = [];
    public $projectws = null;
    public $roundws = null;
    public array $ress = [];
    public array $lists = [];
    public array $playgrounds = [];
    public array $selectlist = [];
    public $user;
    public $app;
    public $document;
    public $model;
    public $templateConfig = null;
    public string $view = 'matches';
    public string $request_url = '';
    public string $sortDirection = 'ASC';
    public string $sortColumn = 'mc.match_date';
    public string $table_data_class = 'table table-striped';
    public string $table_data_div = '';
    public string $dragable_group = '';
    public int $projectteamsel = 0;
    public int $project_id = 0;
    public int $project_art_id = 0;
    public int $rid = 0;
    public int $modalwidth = 900;
    public int $modalheight = 600;
    public int $prefill = 0;

    /**
     * Render backend fixtures for the selected project, division and round.
     *
     * Loads the native match model, validates errors, prepares season,
     * project-team, round and mass-add controls, and passes row and template
     * data to Joomla's layout. Keeps the empty-project state usable and
     * activates shared CSS/JS assets only for HTML documents.
     *
     * @param mixed $tpl Optional Joomla layout override.
     * @return void Delegates the response to the Joomla parent view.
     * @throws \RuntimeException For unavailable models or failed data reads.
     */
    public function display($tpl = null)
    {
        $app = self::administratorApplication();
        $this->app = $app;
        $input = $this->app->getInput();
        $this->document = $this->getDocument();
        $this->model = $this->getModel();

        if (!$this->model instanceof MatchesModel) {
            throw new \RuntimeException('Matches model could not be loaded.', 500);
        }

        if (!class_exists('sportsmanagementHelper', false)) {
            require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';
        }

        // Transitional fallback: match-specific row layouts are still read from
        // the historical tmpl directory until they are split into native layouts.
        $this->addTemplatePath(
            JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/matches/tmpl'
        );

        $this->state = $this->get('State');
        $this->items = $this->get('Items') ?: [];
        $this->pagination = $this->get('Pagination');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters') ?: [];
        $this->sortDirection = (string) $this->state->get('list.direction', 'ASC');
        $this->sortColumn = (string) $this->state->get('list.ordering', 'mc.match_date');
        $this->projectteamsel = (int) $this->state->get('context.project_team_id', 0);
        $this->project_id = (int) $this->state->get('context.project_id', 0);
        $this->rid = (int) $this->state->get('context.round_id', 0);
        $this->request_url = Uri::getInstance()->toString();
        $this->user = $this->app->getIdentity();

        $this->assertNoModelErrors();

        $params = ComponentHelper::getParams($this->option);
        $this->modalheight = (int) $params->get('modal_popup_height', 600);
        $this->modalwidth = (int) $params->get('modal_popup_width', 900);
        $this->prefill = (int) $params->get('use_prefilled_match_roster', 0);
        $this->projectws = $this->model->getProject($this->project_id);

        if (!$this->projectws) {
            $this->roundws = (object) [
                'id' => 0,
                'round_date_first' => null,
                'name' => '',
                'project_id' => $this->project_id,
            ];
            $this->lists = $this->emptyLists();
            $this->addToolbar(false);
            parent::display($tpl);
            return;
        }

        $this->projectws->sports_type_name = (string) (
            $this->projectws->sports_type_name
            ?? $this->projectws->sport_type_name
            ?? ''
        );
        $this->project_art_id = (int) ($this->projectws->project_art_id ?? 0);

        $seasonId = (int) ($this->projectws->season_id ?? 0);
        if ($seasonId > 0) {
            $this->model->setState('context.season_id', $seasonId);
            $this->app->setUserState($this->option . '.season_id', $seasonId);
        }
        $this->app->setUserState($this->option . '.pid', $this->project_id);
        if ($this->rid > 0) {
            $this->app->setUserState($this->option . '.rid', $this->rid);
        }

        $this->roundws = $this->model->getRound($this->rid)
            ?: (object) [
                'id' => 0,
                'round_date_first' => null,
                'name' => '',
                'project_id' => $this->project_id,
            ];
        $this->ress = $this->model->getRounds($this->project_id);
        $this->lists = $this->buildRoundLists($this->ress);
        $this->lists['project_change_rounds'] = $this->ress;

        $allProjectTeams = $this->model->getProjectTeamOptions($this->project_id);
        $this->lists['projectteams'] = $this->withTeamPlaceholder($allProjectTeams);

        foreach ($this->items as $row) {
            $homeDivision = (int) ($row->divhomeid ?? 0);
            $awayDivision = (int) ($row->divawayid ?? 0);
            $divisionId = $homeDivision > 0 && $homeDivision === $awayDivision ? $homeDivision : 0;
            $key = 'teams_' . $divisionId;

            if (!isset($this->lists[$key])) {
                $this->lists[$key] = $this->withTeamPlaceholder(
                    $this->model->getProjectTeamOptions($this->project_id, $divisionId)
                );
            }

            $this->model->checkMatchPicturePath((int) ($row->id ?? 0));
        }

        $this->lists['match_result_type'] = [
            HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_RT')),
            HTMLHelper::_('select.option', 1, Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_OT')),
            HTMLHelper::_('select.option', 2, Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_SO')),
        ];

        $articles = [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_ARTICLE'))];
        $articleRows = \sportsmanagementHelper::getArticleList((int) ($this->projectws->category_id ?? 0));
        if ($articleRows) {
            $articles = array_merge($articles, $articleRows);
        }
        $this->lists['articles'] = $articles;

        $this->lists['divisions'] = array_merge(
            [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DIVISION'))],
            $this->model->getDivisionOptions($this->project_id)
        );
        $this->playgrounds = array_merge(
            [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PLAYGROUND'))],
            $this->model->getPlaygroundOptions($allProjectTeams)
        );

        $extraSelectOptions = new ExtraSelectOptionsHelper();
        foreach ($this->model->getMatchTableColumns() as $field => $definition) {
            $selectOptions = $extraSelectOptions->getOptions('matches', (string) $field);
            if ($selectOptions) {
                $this->selectlist[$field] = array_merge(
                    [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'))],
                    $selectOptions
                );
            }
        }

        $this->matches = $this->model->prepareItems($this->items);
        $this->lists += [
            'search_mode' => '',
            'createTypes' => '',
            'addToRound' => '',
            'autoPublish' => '',
        ];

        $config = ProjectModel::getTemplateConfig($this->project_id, 'backend_matches');
        $this->templateConfig = $config ?: null;

        $massadd = preg_replace('/_[34]$/', '', strtolower((string) $this->getLayout())) === 'massadd'
            || $input->getInt('massadd', 0) === 1;
        if ($massadd) {
            $this->buildMassAddLists();
            $input->set('massadd', 1);
        }
        $this->setLayout('default');
        $this->addToolbar($massadd);

        $this->registerMatchesAssets();

        parent::display($tpl);
    }

    /**
     * Stop match-list rendering without exposing underlying SQL/model errors.
     *
     * Joomla model diagnostics can include queries, table names and driver
     * details. Log only their number and show the translated generic error.
     * With no model errors, preserve the normal match-rendering path.
     *
     * @return void
     * @throws \RuntimeException If the match model reports errors.
     */
    private function assertNoModelErrors(): void
    {
        $errors = $this->get('Errors');

        if (!$errors) {
            return;
        }

        Log::add(
            __METHOD__ . ': matches model reported ' . count((array) $errors) . ' errors.',
            Log::ERROR,
            'com_sportsmanagement'
        );

        throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 500);
    }

    /**
     * Enable Joomla match-list CSS and JavaScript on an HTML document.
     *
     * Multiple instances or layout renders may share a WebAssetManager.
     * Register each fixed asset name only once, yet mark both as used for
     * every render. Retain the core dependency for matches.js and skip
     * non-HTML response documents without attempting asset registration.
     *
     * @return void
     */
    private function registerMatchesAssets(): void
    {
        $document = $this->getDocument();

        if (!$document instanceof HtmlDocument) {
            return;
        }

        $assets = $document->getWebAssetManager();
        $style = 'com_sportsmanagement.matches.form';

        if (!$assets->assetExists('style', $style)) {
            $assets->registerStyle(
                $style,
                'administrator/components/com_sportsmanagement/assets/css/form_control.css'
            );
        }

        $assets->useStyle($style);
        $script = 'com_sportsmanagement.matches.admin';

        if (!$assets->assetExists('script', $script)) {
            $assets->registerScript(
                $script,
                'administrator/components/com_sportsmanagement/assets/js/matches.js',
                [],
                [],
                ['core']
            );
        }

        $assets->useScript($script);
    }

    /**
     * Configure match administration and bulk-fixture toolbar actions.
     *
     * Bulk creation shows a return link to the selected project and round.
     * Normal editing offers publish, unpublish, result counting, short-save
     * and add-match actions through Joomla's native toolbar helpers.
     *
     * @param bool $massadd True when the mass-add editor is selected.
     * @return void
     */
    private function addToolbar(bool $massadd): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_TITLE'), 'calendar');

        if ($massadd) {
            ToolbarHelper::back(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_CANCEL_MATCHADD'),
                Route::_('index.php?option=com_sportsmanagement&view=matches&pid=' . $this->project_id . '&rid=' . $this->rid, false)
            );
            return;
        }

        ToolbarHelper::back(
            'JPREV',
            Route::_('index.php?option=com_sportsmanagement&view=rounds&pid=' . $this->project_id, false)
        );
        ToolbarHelper::publish('match.insertgooglecalendar', 'JLIB_HTML_CALENDAR', true);
        ToolbarHelper::publish('matches.count_result_yes', 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_INCL', true);
        ToolbarHelper::unpublish('matches.count_result_no', 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_INCL', true);
        ToolbarHelper::publish('matches.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('matches.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::apply('matches.saveshort');
        ToolbarHelper::link(
            Route::_('index.php?option=com_sportsmanagement&view=matches&layout=massadd&massadd=1&pid=' . $this->project_id . '&rid=' . $this->rid, false),
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_MATCHES'),
            'new'
        );
        ToolbarHelper::addNew('match.addmatch', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_ADD_MATCH'));
    }

    /**
     * Format round names and dates for both Joomla round-selector controls.
     *
     * Keeps the selected round and the data-jsm-round-select attribute used
     * by native JavaScript, while retaining historical second-list markup.
     *
     * @param array $rounds Project round objects with names, IDs and dates.
     * @return array<string,string> Rendered normal and alternate round lists.
     */
    private function buildRoundLists(array $rounds): array
    {
        $options = [];
        foreach ($rounds as $round) {
            $dateRange = \sportsmanagementHelper::convertDate($round->round_date_first, 1)
                . ' - ' . \sportsmanagementHelper::convertDate($round->round_date_last, 1);
            $options[] = HTMLHelper::_(
                'select.option',
                (int) $round->id,
                sprintf('%s (%s)', $round->name, $dateRange)
            );
        }

        $selected = (int) ($this->roundws->id ?? 0);
        return [
            'project_rounds' => HTMLHelper::_(
                'select.genericlist',
                $options,
                'rid',
                'class="form-select" data-jsm-round-select',
                'value',
                'text',
                $selected
            ),
            'project_rounds2' => HTMLHelper::_(
                'select.genericlist',
                $options,
                'rid',
                'class="form-select"',
                'value',
                'text',
                $selected
            ),
        ];
    }

    /**
     * Add the translated team or person placeholder to match selection options.
     *
     * Person-based sports use the person label and all other project types
     * use the team label, preserving the supplied option order and values.
     *
     * @param array $teams Joomla option objects for the current project.
     * @return array Placeholder followed by original selection options.
     */
    private function withTeamPlaceholder(array $teams): array
    {
        $label = $this->project_art_id === 3
            ? Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PERSON')
            : Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM');

        return array_merge([HTMLHelper::_('select.option', 0, $label)], $teams);
    }

    /**
     * Build mass-add type, add-to-round and auto-publish form selectors.
     *
     * Preserves the legacy POST field names and Joomla-native radio/select
     * controls required by the administrator bulk match-creation workflow.
     *
     * @return void
     */
    private function buildMassAddLists(): void
    {
        $createOptions = [
            HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD')),
            HTMLHelper::_('select.option', 1, Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_1')),
            HTMLHelper::_('select.option', 2, Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_2')),
        ];
        $this->lists['createTypes'] = HTMLHelper::_(
            'select.genericlist',
            $createOptions,
            'ct[]',
            'class="form-select" data-jsm-create-type',
            'value',
            'text',
            1,
            'ct'
        );

        $yesNo = [
            HTMLHelper::_('select.option', 0, Text::_('JNO')),
            HTMLHelper::_('select.option', 1, Text::_('JYES')),
        ];
        $this->lists['addToRound'] = HTMLHelper::_(
            'select.radiolist',
            $yesNo,
            'addToRound',
            'class="form-check-input"',
            'value',
            'text',
            0
        );
        $this->lists['autoPublish'] = HTMLHelper::_(
            'select.radiolist',
            $yesNo,
            'autoPublish',
            'class="form-check-input"',
            'value',
            'text',
            1
        );
    }

    /**
     * Supply safe empty template selectors when no project is selected.
     *
     * Retains all round, team, result, article, division and mass-add keys
     * consumed by native and legacy match templates, preventing warnings
     * when the matches view has no valid project context.
     *
     * @return array<string,mixed> Empty form-control values.
     */
    private function emptyLists(): array
    {
        return [
            'project_rounds' => '',
            'project_rounds2' => '',
            'project_change_rounds' => [],
            'projectteams' => [],
            'match_result_type' => [],
            'articles' => [],
            'divisions' => [],
            'search_mode' => '',
            'createTypes' => '',
            'addToRound' => '',
            'autoPublish' => '',
        ];
    }
}
