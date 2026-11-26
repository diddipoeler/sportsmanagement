<?php
/**
 * Base Joomla 5/6 project HTML view for the SportsManagement site application.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementProjectModel;
use Diddipoeler\Component\SportsManagement\Site\Service\LegacyPresentationLoader;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Uri\Uri;

abstract class SportsManagementProjectHtmlView extends SportsManagementHtmlView
{
    public ?object $project = null;
    public object|int|null $division = null;
    public array $overallconfig = [];
    public array $config = [];
    public array $notes = [];
    public array $tips = [];
    public array $warnings = [];
    public array $debug = [];
    public string $headertitle = '';
    public string $divclasscontainer = 'container-fluid';
    public string $divclassrow = 'row';
    public string $view = '';
    public int $modalheight = 600;
    public int $modalwidth = 900;
    public float $jsmseitenaufbau = 0.0;

    private bool $presentationDependenciesLoaded = false;

    /**
     * Initialise the shared project view and add the common frontend template directory.
     * @param array $config Joomla view configuration.
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->addTemplatePath(JPATH_SITE . '/components/com_sportsmanagement/tmpl/globalviews');
    }

    /**
     * Load shared presentation dependencies, debug information, project configuration and required assets before preparing the concrete view. Record preparation time and delegate template rendering to Joomla.
     * @param string|null $tpl Optional template name.
     * @return void
     */
    public function display($tpl = null)
    {
        $started = microtime(true);
        $this->loadPresentationDependencies();
        $this->prepareDebugEntries();
        $this->prepareProjectContext();
        $this->loadPresentationAssets();
        $this->prepareView();
        $this->jsmseitenaufbau = round(microtime(true) - $started, 6);
        parent::display($tpl);
    }

    /**
     * Provide an optional preparation hook for concrete project views. The base implementation intentionally performs no additional work.
     * @return void
     */
    protected function prepareView(): void
    {
    }

    /**
     * Keep legacy helper/model aliases available for project views whose
     * templates have not yet been fully migrated. Native views can override
     * this and return false once their complete rendering path is namespaced.
     */
    protected function requiresLegacyPresentationDependencies(): bool
    {
        return true;
    }

    /**
     * Legacy project views may still rely on globally available jQuery.
     * Native views should opt out and declare jQuery only on the assets that
     * actually require it.
     */
    protected function requiresJquery(): bool
    {
        return true;
    }

    /**
     * Legacy project views may still use the JCE MediaBox image presentation.
     * Native views can opt out unless their selected modal mode requires it.
     */
    protected function requiresJceMediaBox(): bool
    {
        return true;
    }

    /**
     * Require a project model, resolve the current view and project, merge overall and view-specific template settings and initialise layout classes, modal dimensions and the legacy view constant.
     * @return void
     * @throws \RuntimeException If the assigned model is not a SportsManagement project model.
     */
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
        $this->divclassrow = (string) ($this->config['divclassrow'] ?? 'row');
        $this->modalheight = (int) $this->params->get('modal_popup_height', 600);
        $this->modalwidth = (int) $this->params->get('modal_popup_width', 900);

        if (!\defined('COM_SPORTSMANAGEMENT_SHOW_VIEW')) {
            \define('COM_SPORTSMANAGEMENT_SHOW_VIEW', ucfirst($this->view));
        }
    }

    /**
     * Initialize compatibility dependencies and shared presentation constants.
     * Load legacy aliases once per view instance, preserving existing template
     * overrides. Activate common styles only on the active HTML document and
     * reuse definitions already registered by another project view.
     * @return void
     */
    private function loadPresentationDependencies(): void
    {
        if (!$this->presentationDependenciesLoaded) {
            if ($this->requiresLegacyPresentationDependencies()) {
                LegacyPresentationLoader::register();
            }

            $this->presentationDependenciesLoaded = true;
        }

        if (!\defined('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO')) {
            \define('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO', (int) $this->params->get('show_debug_info', 0));
        }
        if (!\defined('COM_SPORTSMANAGEMENT_PICTURE_SERVER')) {
            $external = $this->params->get('cfg_dbprefix') || $this->params->get('cfg_which_database');
            \define('COM_SPORTSMANAGEMENT_PICTURE_SERVER', $external ? (string) $this->params->get('cfg_which_database_server', '') : Uri::root());
        }

        $document = $this->getDocument();

        if (!$document instanceof HtmlDocument) {
            return;
        }

        $base = Uri::root(true);
        $wa = $document->getWebAssetManager();

        foreach ([
            'extended-base' => '/administrator/components/com_sportsmanagement/assets/css/extended-1.1.css',
            'admin-style' => '/administrator/components/com_sportsmanagement/assets/css/style.css',
            'admin-stylebox' => '/administrator/components/com_sportsmanagement/assets/css/stylebox.css',
            'extended' => '/administrator/components/com_sportsmanagement/assets/css/extended_4.css',
            'stylebox' => '/administrator/components/com_sportsmanagement/assets/css/stylebox_4.css',
            'modalwithoutjs' => '/components/com_sportsmanagement/assets/css/modalwithoutjs.css',
        ] as $suffix => $path) {
            $name = 'com_sportsmanagement.site.' . $suffix;
            if (!$wa->assetExists('style', $name)) {
                $wa->registerStyle($name, $base . $path);
            }
            $wa->useStyle($name);
        }
    }

    /**
     * Reset the debug collection and copy legacy helper success messages only when debugging and legacy presentation dependencies are enabled and the helper is loaded.
     * @return void
     */
    private function prepareDebugEntries(): void
    {
        $this->debug = [];

        if (!\defined('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO') || !COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            return;
        }

        if (!$this->requiresLegacyPresentationDependencies() || !class_exists('sportsmanagementHelper', false)) {
            return;
        }

        $this->debug = (array) (\sportsmanagementHelper::$_success_text ?? []);
    }

    /**
     * Activate optional jQuery and JCE MediaBox assets on HTML documents.
     * Reuses the document's registry across views and repeated renders,
     * preserving the original script dependency and stylesheet paths.
     * @return void
     */
    private function loadPresentationAssets(): void
    {
        $document = $this->getDocument();

        if (!$document instanceof HtmlDocument) {
            return;
        }

        $wa = $document->getWebAssetManager();

        if ($this->requiresJquery()) {
            $wa->useScript('jquery');
        }

        if (!$this->requiresJceMediaBox()) {
            return;
        }

        $base = Uri::root(true);
        $name = 'com_sportsmanagement.site.jcemediabox';

        if (!$wa->assetExists('style', $name)) {
            $wa->registerStyle($name, $base . '/components/com_sportsmanagement/assets/css/jcemediabox.css');
        }
        if (!$wa->assetExists('script', $name)) {
            $wa->registerScript(
                $name,
                $base . '/components/com_sportsmanagement/assets/js/jcemediabox.js',
                [],
                [],
                ['jquery']
            );
        }

        $wa->useStyle($name);
        $wa->useScript($name);
    }
}
