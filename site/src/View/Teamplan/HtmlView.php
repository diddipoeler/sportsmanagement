<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Teamplan;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Legacy\TeamplanCommentsFacade;
use Diddipoeler\Component\SportsManagement\Site\Legacy\TeamplanHtmlFacade;
use Diddipoeler\Component\SportsManagement\Site\Legacy\TeamplanProjectFacade;
use Diddipoeler\Component\SportsManagement\Site\Model\TeamplanModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Native Joomla 5/6 HTML view for the team plan.
 *
 * Existing tmpl files remain in the historical template directory while the
 * MVC class itself is resolved through the component namespace. Their remaining
 * global helper names are isolated through narrow facades/lazy presentation
 * helpers until the tmpl files themselves are fully migrated.
 */
final class HtmlView extends SportsManagementProjectHtmlView
{
    public array $rounds = [];
    public array $teams = [];
    public array $favteams = [];
    public int $ptid = 0;
    public array $projectevents = [];
    public array $matches = [];
    public array $matches_refering = [];
    public array $matchesperround = [];
    public $document;

    public function __construct($config = [])
    {
        $config['template_path'] = JPATH_SITE . '/components/com_sportsmanagement/views/teamplan/tmpl';
        parent::__construct($config);

        $this->initialisePresentationCompatibilityConstants();

        // Keep the historical template call surface stable while routing the
        // former project-model methods through the narrow native facade.
        if (!class_exists('sportsmanagementModelProject', false)) {
            class_alias(TeamplanProjectFacade::class, 'sportsmanagementModelProject');
        }

        // Replace the historical site/helpers/html.php class for teamplan with
        // the small Joomla 5/6 facade required by its two existing tmpl files.
        if (!class_exists('sportsmanagementHelperHtml', false)) {
            class_alias(TeamplanHtmlFacade::class, 'sportsmanagementHelperHtml');
        }

        // The teamplan template only needs CreateInstance() and
        // showMatchCommentIcon(). Do not load the historical comments helper.
        if (!class_exists('sportsmanagementModelComments', false)) {
            class_alias(TeamplanCommentsFacade::class, 'sportsmanagementModelComments');
        }

        $this->document = $this->getDocument();
    }

    protected function prepareView(): void
    {
        /** @var TeamplanModel $model */
        $model = $this->getModel();
        if (!$model instanceof TeamplanModel) {
            throw new \RuntimeException('Teamplan view requires TeamplanModel.', 500);
        }

        TeamplanProjectFacade::setModel($model);
        TeamplanHtmlFacade::$project = $this->project;

        $this->document->addScript(
            Uri::root(true) . '/components/com_sportsmanagement/assets/js/smsportsmanagement.js'
        );
        $this->document->addScript(
            Uri::root(true) . '/components/com_sportsmanagement/assets/js/printPreview.js'
        );

        if (!empty($this->config['show_date_image'])) {
            $this->document->addStyleSheet(
                Uri::base() . 'components/com_sportsmanagement/assets/css/calendar.css'
            );
        }

        if ($this->project && (int) ($this->project->id ?? 0) > 0) {
            $ordering = (string) ($this->config['plan_order'] ?? 'ASC');
            $this->rounds = $model->getPlanRounds($ordering);
            $this->teams = $model->getPlanTeams();
            TeamplanHtmlFacade::$teams = $this->teams;
            $this->favteams = $model->getPlanFavTeams();
            $this->division = $model->getPlanDivision();
            $this->ptid = $model->getProjectTeamId();
            $this->projectevents = $model->getPlanProjectEvents();
            $this->matches = $model->getMatches($this->config);
            $this->matches_refering = $model->getMatchesRefering($this->config);
            $this->matchesperround = $model->getMatchesPerRound($this->config, $this->rounds);
        }

        if ($this->ptid > 0 && isset($this->teams[$this->ptid])) {
            $pageTitleSubject = (string) ($this->teams[$this->ptid]->name ?? '');
        } else {
            $pageTitleSubject = (string) ($this->project->name ?? '');
        }

        $pageTitle = Text::sprintf('COM_SPORTSMANAGEMENT_TEAMPLAN_PAGE_TITLE', $pageTitleSubject);
        $this->headertitle = $pageTitle;
        $this->document->setTitle($pageTitle);
        $this->config['table_class'] = (string) ($this->config['table_class'] ?? 'table');
    }

    private function initialisePresentationCompatibilityConstants(): void
    {
        if (!\defined('JSM_PATH')) {
            \define('JSM_PATH', 'components/com_sportsmanagement');
        }
        if (!\defined('COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS')) {
            \define('COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS', $this->params->get('boostrap_div_class'));
        }
        if (!\defined('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE')) {
            \define('COM_SPORTSMANAGEMENT_CFG_WHICH_DATABASE', $this->params->get('cfg_which_database'));
        }
        if (!\defined('COM_SPORTSMANAGEMENT_LOAD_BOOTSTRAP')) {
            \define('COM_SPORTSMANAGEMENT_LOAD_BOOTSTRAP', $this->params->get('cfg_load_bootstrap'));
        }
        if (!\defined('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO')) {
            \define('COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO', $this->params->get('show_query_debug_info'));
        }
    }
}
