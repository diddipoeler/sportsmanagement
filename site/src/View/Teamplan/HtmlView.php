<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Teamplan;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Legacy\LegacyBootstrap;
use Diddipoeler\Component\SportsManagement\Site\Model\TeamplanModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Native Joomla 5/6 HTML view for the team plan.
 *
 * Existing tmpl files remain in the historical template directory while the
 * MVC class itself is resolved through the component namespace. Their remaining
 * presentation-only legacy helpers are bootstrapped here until those template
 * calls are migrated as a separate step.
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

        // The native model is independent of legacy MVC. Keep the remaining
        // template-only helpers (comments, project presentation helpers, etc.)
        // isolated at the presentation boundary until the tmpl files are fully
        // migrated.
        LegacyBootstrap::bootForView('teamplan');

        $this->document = $this->getDocument();
    }

    protected function prepareView(): void
    {
        /** @var TeamplanModel $model */
        $model = $this->getModel();
        if (!$model instanceof TeamplanModel) {
            throw new \RuntimeException('Teamplan view requires TeamplanModel.', 500);
        }

        $this->document->addScript(
            Uri::root(true) . '/components/com_sportsmanagement/assets/js/smsportsmanagement.js'
        );
        $this->document->addScript(
            Uri::root(true) . '/components/com_sportsmanagement/assets/js/printPreview.js'
        );

        if (class_exists('sportsmanagementHelperHtml')) {
            \sportsmanagementHelperHtml::$project = $this->project;
        }

        if (!empty($this->config['show_date_image'])) {
            $this->document->addStyleSheet(
                Uri::base() . 'components/com_sportsmanagement/assets/css/calendar.css'
            );
        }

        if ($this->project && (int) ($this->project->id ?? 0) > 0) {
            $ordering = (string) ($this->config['plan_order'] ?? 'ASC');
            $this->rounds = $model->getPlanRounds($ordering);
            $this->teams = $model->getPlanTeams();
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
}
