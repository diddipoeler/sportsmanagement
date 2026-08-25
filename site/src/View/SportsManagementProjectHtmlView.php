<?php
namespace Diddipoeler\Component\SportsManagement\Site\View;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementProjectModel;
use Diddipoeler\Component\SportsManagement\Site\Service\LegacyPresentationLoader;
use Joomla\CMS\Uri\Uri;

abstract class SportsManagementProjectHtmlView extends SportsManagementHtmlView
{
    public ?object $project = null;
    public ?object $division = null;
    public array $overallconfig = [];
    public array $config = [];
    public array $notes = [];
    public array $tips = [];
    public array $warnings = [];
    public string $headertitle = '';
    public string $divclasscontainer = 'container-fluid';
    public string $divclassrow = 'row-fluid';
    public string $view = '';
    public int $modalheight = 600;
    public int $modalwidth = 900;
    public float $jsmseitenaufbau = 0.0;

    private bool $presentationDependenciesLoaded = false;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->addTemplatePath(JPATH_SITE . '/components/com_sportsmanagement/tmpl/globalviews');
    }

    public function display($tpl = null)
    {
        $started = microtime(true);
        $this->loadPresentationDependencies();
        $this->prepareProjectContext();
        $this->prepareView();
        $this->jsmseitenaufbau = round(microtime(true) - $started, 6);
        parent::display($tpl);
    }

    protected function prepareView(): void
    {
    }

    protected function prepareProjectContext(): void
    {
        $model = $this->getModel();
        if (!$model instanceof SportsManagementProjectModel) {
            throw new \RuntimeException('SportsManagement project view requires a SportsManagementProjectModel.', 500);
        }

        $this->view = strtolower($this->input->getCmd('view', $this->getName()));
        $this->project = $model->getProject();
        $this->overallconfig = $model->getOverallConfig();
        $this->config = array_merge($this->overallconfig, $model->getTemplateConfig($this->view));
        $this->divclasscontainer = (string) ($this->config['divclasscontainer'] ?? 'container-fluid');
        $this->divclassrow = (string) ($this->config['divclassrow'] ?? 'row-fluid');
        $this->modalheight = (int) $this->params->get('modal_popup_height', 600);
        $this->modalwidth = (int) $this->params->get('modal_popup_width', 900);

        if (!\defined('COM_SPORTSMANAGEMENT_SHOW_VIEW')) {
            \define('COM_SPORTSMANAGEMENT_SHOW_VIEW', ucfirst($this->view));
        }
    }

    private function loadPresentationDependencies(): void
    {
        if ($this->presentationDependenciesLoaded) {
            return;
        }

        $this->presentationDependenciesLoaded = true;

        LegacyPresentationLoader::register();

        if (!\defined('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO')) {
            \define('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO', (int) $this->params->get('show_debug_info', 0));
        }
        if (!\defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER')) {
            $external = $this->params->get('cfg_dbprefix') || $this->params->get('cfg_which_database');
            \define('COM_SPORTSMANAGEMENT_PICTURE_SERVER', $external ? (string) $this->params->get('cfg_which_database_server', '') : Uri::root());
        }

        $base = Uri::root(true);
        $wa = $this->getDocument()->getWebAssetManager();
        $wa->useScript('jquery')
            ->registerAndUseStyle(
                'com_sportsmanagement.site.extended-base',
                $base . '/administrator/components/com_sportsmanagement/assets/css/extended-1.1.css'
            )
            ->registerAndUseStyle(
                'com_sportsmanagement.site.admin-style',
                $base . '/administrator/components/com_sportsmanagement/assets/css/style.css'
            )
            ->registerAndUseStyle(
                'com_sportsmanagement.site.admin-stylebox',
                $base . '/administrator/components/com_sportsmanagement/assets/css/stylebox.css'
            )
            ->registerAndUseStyle(
                'com_sportsmanagement.site.extended',
                $base . '/administrator/components/com_sportsmanagement/assets/css/extended_4.css'
            )
            ->registerAndUseStyle(
                'com_sportsmanagement.site.stylebox',
                $base . '/administrator/components/com_sportsmanagement/assets/css/stylebox_4.css'
            )
            ->registerAndUseStyle(
                'com_sportsmanagement.site.modalwithoutjs',
                $base . '/components/com_sportsmanagement/assets/css/modalwithoutjs.css'
            )
            ->registerAndUseStyle(
                'com_sportsmanagement.site.jcemediabox',
                $base . '/components/com_sportsmanagement/assets/css/jcemediabox.css'
            )
            ->registerAndUseScript(
                'com_sportsmanagement.site.jcemediabox',
                $base . '/components/com_sportsmanagement/assets/js/jcemediabox.js',
                [],
                [],
                ['jquery']
            );
    }
}
