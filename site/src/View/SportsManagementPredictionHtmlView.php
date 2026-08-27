<?php
namespace Diddipoeler\Component\SportsManagement\Site\View;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementPredictionModel;
use Diddipoeler\Component\SportsManagement\Site\Service\LegacyPresentationLoader;
use Joomla\CMS\Uri\Uri;

abstract class SportsManagementPredictionHtmlView extends SportsManagementHtmlView
{
    public ?object $predictionGame = null;
    public object $predictionMember;
    public array $predictionProjects = [];
    public array $predictionProjectS = [];
    public array $overallconfig = [];
    public array $config = [];
    public array $configavatar = [];
    public array $lists = [];
    public array $notes = [];
    public array $tips = [];
    public array $warnings = [];
    public object $actJoomlaUser;
    public bool $allowedAdmin = false;
    public bool $showediticon = false;
    public string $headertitle = '';
    public string $divclasscontainer = 'container-fluid';
    public string $divclassrow = 'row-fluid';
    public string $view = '';
    public int $predictionGameID = 0;
    public int $predictionMemberID = 0;
    public int $projectID = 0;
    public int $roundID = 0;
    public int $predictionGroupID = 0;
    public int $databaseSelector = 0;
    public int $modalheight = 600;
    public int $modalwidth = 900;
    public float $jsmseitenaufbau = 0.0;
    protected SportsManagementPredictionModel $predictionModel;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->addTemplatePath(JPATH_SITE . '/components/com_sportsmanagement/tmpl/globalviews');
        $this->addTemplatePath(JPATH_SITE . '/components/com_sportsmanagement/tmpl/predictionheading');
        LegacyPresentationLoader::register();
        $this->loadPresentationAssets();
    }

    public function display($tpl = null)
    {
        $started = microtime(true);
        $this->preparePredictionContext();
        $this->prepareView();
        $this->jsmseitenaufbau = round(microtime(true) - $started, 6);
        parent::display($tpl);
    }

    public function scoreRuleExample(object $project, int $home, int $away, int $tipp, int $tippHome, int $tippAway, bool $joker = false): int
    {
        return $this->predictionModel->scoreRuleExample($project, $home, $away, $tipp, $tippHome, $tippAway, $joker);
    }

    protected function prepareView(): void
    {
    }

    protected function preparePredictionContext(): void
    {
        $model = $this->getModel();
        if (!$model instanceof SportsManagementPredictionModel) {
            throw new \RuntimeException('SportsManagement prediction view requires a SportsManagementPredictionModel.', 500);
        }

        $this->predictionModel = $model;
        $this->view = strtolower($this->input->getCmd('view', $this->getName()));
        $this->predictionGameID = $model->getPredictionGameId();
        $this->predictionMemberID = $model->getPredictionMemberId();
        $this->projectID = $model->getProjectId();
        $this->roundID = $model->getRoundId();
        $this->predictionGroupID = $model->getGroupId();
        $this->databaseSelector = $model->getDatabaseSelector() === 1 ? 1 : 0;
        $this->predictionGame = $model->getPredictionGame();
        $this->overallconfig = $model->getPredictionTemplateConfig('predictionoverall');
        $this->config = array_merge($this->overallconfig, $model->getPredictionTemplateConfig($this->view));
        $this->configavatar = $model->getPredictionTemplateConfig('predictionusers');
        $this->predictionMember = $model->getPredictionMember();
        $this->predictionProjects = $model->getPredictionProjects();
        $this->predictionProjectS = $this->predictionProjects;
        $this->actJoomlaUser = $this->app->getIdentity();
        $this->allowedAdmin = $model->isAllowedAdmin();
        $this->divclasscontainer = (string) ($this->config['divclasscontainer'] ?? 'container-fluid');
        $this->divclassrow = (string) ($this->config['divclassrow'] ?? 'row-fluid');
        $this->modalheight = (int) $this->params->get('modal_popup_height', 600);
        $this->modalwidth = (int) $this->params->get('modal_popup_width', 900);

        if (!\defined('COM_SPORTSMANAGEMENT_SHOW_VIEW')) {
            \define('COM_SPORTSMANAGEMENT_SHOW_VIEW', ucfirst($this->view));
        }
    }

    private function loadPresentationAssets(): void
    {
        if (!\defined('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO')) {
            \define('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO', (int) $this->params->get('show_debug_info', 0));
        }

        $document = $this->getDocument();
        $document->getWebAssetManager()->useScript('jquery');
        $document->addStyleSheet(Uri::root(true) . '/administrator/components/com_sportsmanagement/assets/css/extended-1.1.css');
        $document->addStyleSheet(Uri::root(true) . '/administrator/components/com_sportsmanagement/assets/css/style.css');
        $document->addStyleSheet(Uri::root(true) . '/administrator/components/com_sportsmanagement/assets/css/stylebox.css');
        $document->addStyleSheet(Uri::root(true) . '/administrator/components/com_sportsmanagement/assets/css/extended_4.css');
        $document->addStyleSheet(Uri::root(true) . '/administrator/components/com_sportsmanagement/assets/css/stylebox_4.css');
    }
}
