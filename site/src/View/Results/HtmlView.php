<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Results;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ExtendedFormHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\ResultsModel;
use Diddipoeler\Component\SportsManagement\Site\Model\ResultsViewDataModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** Native Joomla 5/6 display view for regular results pages. */
final class HtmlView extends SportsManagementHtmlView
{
    public $state = null;
    public ?object $project = null;
    public ?object $division = null;
    public array $overallconfig = [];
    public array $config = [];
    public array $matches = [];
    public array $teams = [];
    public array $favteams = [];
    public array $projectevents = [];
    public array $eventsByMatch = [];
    public array $substitutionsByMatch = [];
    public array $refereesByMatch = [];
    public array $rounds = [];
    public array $roundsoption = [];
    public array $lists = [];
    public $pagination = null;
    public $rssfeeditems = null;
    public bool $showediticon = false;
    public bool $isAllowed = false;
    public bool $commentsEnabled = false;
    public int $roundid = 0;
    public string $roundcode = '';
    public int $cfg_which_database = 0;
    public int $season_id = 0;
    public int $modalheight = 600;
    public int $modalwidth = 900;
    public string $view = 'results';
    public string $divclasscontainer = 'container-fluid';
    public string $divclassrow = 'row-fluid';

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->addTemplatePath(JPATH_SITE . '/components/com_sportsmanagement/tmpl/globalviews');
    }

    public function display($tpl = null)
    {
        /** @var ResultsModel $model */
        $model = $this->getModel();
        if (!$model instanceof ResultsModel) {
            throw new \RuntimeException('Results view requires ResultsModel.', 500);
        }

        $this->cfg_which_database = $this->input->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
        $this->state = $model->getState();
        $this->project = $model->getProject();
        $this->overallconfig = $model->getOverallConfig();
        $this->config = array_merge($this->overallconfig, $model->getTemplateConfig('results'));
        $this->divclasscontainer = (string) ($this->config['divclasscontainer'] ?? 'container-fluid');
        $this->divclassrow = (string) ($this->config['divclassrow'] ?? 'row-fluid');
        $this->modalheight = (int) $this->params->get('modal_popup_height', 600);
        $this->modalwidth = (int) $this->params->get('modal_popup_width', 900);

        if (!$this->project) {
            $this->app->enqueueMessage(
                Text::_('Error: ProjectID was not submitted in URL or selected project was not found in database!'),
                'error'
            );
            parent::display($tpl);
            return;
        }

        $this->season_id = (int) ($this->project->season_id ?? $this->input->getInt('s', 0));
        $this->roundid = max(0, $this->input->getInt('r', 0));
        if ($this->roundid <= 0) {
            $this->roundid = max(0, (int) ($this->project->current_round ?? 0));
        }

        $this->matches = array_values((array) ($model->getData() ?: []));
        $this->pagination = $model->getPagination();
        $this->division = $model->getDivision($this->cfg_which_database);
        $this->roundcode = $model->getRoundCode($this->roundid);
        $this->roundsoption = $model->getRoundOptions('ASC');
        $this->rounds = $model->getRounds('ASC');
        $this->teams = $model->getProjectTeamsIndexed((int) ($this->division->id ?? 0));
        $this->favteams = array_values(array_map('intval', $model->getFavTeams()));
        $this->showediticon = $model->getShowEditIcon((int) ($this->project->editorgroup ?? 0));
        $this->isAllowed = $model->isAllowed($this->cfg_which_database, (int) ($this->project->editorgroup ?? 0));

        if (!isset($this->config['switch_home_guest'])) {
            $this->config['switch_home_guest'] = 0;
        }
        if (!isset($this->config['show_dnp_teams_icons'])) {
            $this->config['show_dnp_teams_icons'] = 0;
        }
        if (!isset($this->config['show_results_ranking'])) {
            $this->config['show_results_ranking'] = 0;
        }

        $matchIds = array_values(array_filter(array_map(
            static fn (object $match): int => (int) ($match->id ?? 0),
            $this->matches
        )));

        $viewData = new ResultsViewDataModel();
        $viewData->setDatabaseSelector($this->cfg_which_database);
        $viewData->setProjectId((int) $this->project->id);

        if (!empty($this->config['show_events'])) {
            $this->projectevents = $model->getProjectEvents();
            $this->eventsByMatch = $viewData->getMatchEvents($matchIds);
            if (!empty($this->config['use_tabs_events'])) {
                $this->substitutionsByMatch = $viewData->getMatchSubstitutions($matchIds);
            }
        }

        if (!empty($this->config['show_referee'])) {
            $this->refereesByMatch = $viewData->getMatchReferees(
                $matchIds,
                !empty($this->project->teams_as_referees)
            );
        }

        $this->commentsEnabled = !empty($this->config['show_comments_count'])
            && ((!empty($this->config['show_project_kunena_link']) && ComponentHelper::isEnabled('com_kunena'))
                || ComponentHelper::isEnabled('com_jcomments'));

        if (!empty($this->overallconfig['show_project_rss_feed'])) {
            $extended = ExtendedFormHelper::load((string) ($this->project->extended ?? ''), 'project');
            $rssLink = $extended
                ? (string) $extended->getValue('COM_SPORTSMANAGEMENT_PROJECT_RSS_FEED_LIVE_RESULTS', null, '')
                : '';
            if ($rssLink !== '') {
                $this->rssfeeditems = $model->getRssFeeds(
                    $rssLink,
                    (int) ($this->overallconfig['rssitems'] ?? 5)
                );
            }
        }

        $this->prepareAssets();
        $this->prepareDocument();

        parent::display($tpl);
    }

    private function prepareAssets(): void
    {
        $base = Uri::root(true);
        $wa = $this->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle(
            'com_sportsmanagement.results',
            $base . '/components/com_sportsmanagement/assets/css/results.css'
        );

        if (is_file(JPATH_SITE . '/components/com_sportsmanagement/assets/js/smsportsmanagement.js')) {
            $wa->registerAndUseScript(
                'com_sportsmanagement.results.site',
                $base . '/components/com_sportsmanagement/assets/js/smsportsmanagement.js'
            );
        }

        if (!\defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER')) {
            $external = $this->params->get('cfg_dbprefix') || $this->params->get('cfg_which_database');
            \define(
                'COM_SPORTSMANAGEMENT_PICTURE_SERVER',
                $external ? (string) $this->params->get('cfg_which_database_server', '') : Uri::root()
            );
        }
    }

    private function prepareDocument(): void
    {
        $title = Text::_('COM_SPORTSMANAGEMENT_RESULTS_PAGE_TITLE');
        if ($this->project && trim((string) ($this->project->name ?? '')) !== '') {
            $title .= ': ' . (string) $this->project->name;
        }
        $this->getDocument()->setTitle($title);

        if ($this->project) {
            $feed = 'index.php?option=com_sportsmanagement&view=results&p=' . (int) $this->project->id . '&format=feed&type=rss';
            $this->getDocument()->addHeadLink(
                Route::_($feed),
                'alternate',
                'rel',
                [
                    'type' => 'application/rss+xml',
                    'title' => Text::_('COM_SPORTSMANAGEMENT_RESULTS_RSSFEED'),
                ]
            );
        }
    }
}
