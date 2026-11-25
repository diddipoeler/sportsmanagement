<?php
/**
 * Native Joomla 5/6 administrator DFB-key schedule import view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextdfbkeyimport;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextdfbkeyimportModel;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

/** Native Joomla 5/6 administrator view for the DFB-key schedule importer. */
final class HtmlView extends BaseHtmlView
{
    public int $project_id = 0;
    public int $division_id = 0;
    public array $newmatchdays = [];
    public int $dfbteams = 0;
    public array $lists = [];
    public array $import = [];
    public int $division = 0;
    public string $request_url = '';
    public string $table_data_class = 'table table-striped';
    public string $view = 'jlextdfbkeyimport';
    public array $tips = [];
    public array $notes = [];

    /**
     * Initialise the native DFB-key import view and register shared administrator footer and list-header template paths.
     * @param array $config Joomla view configuration.
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->addTemplatePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/footer/tmpl');
        $this->addTemplatePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/listheader/tmpl');
    }

    /**
     * Resolve the selected project and division from the request and stored context, normalise the legacy layout suffix and dispatch to the corresponding import preparation method. Render the parent layout only when preparation permits it.
     * @param string|null $tpl Optional template name.
     * @return mixed False when rendering is suppressed, otherwise the method completes without an explicit return value.
     * @throws \RuntimeException If the assigned model is not the DFB-key import model.
     */
    public function display($tpl = null)
    {
        $app = self::administratorApplication();
        $input = $app->getInput();
        $storedProjectId = (int) $app->getUserState(
            'com_sportsmanagement.pid',
            (int) $app->getUserState('com_sportsmanagement.project_id', 0)
        );

        $this->project_id = $input->getInt('pid', $input->getInt('projectid', $storedProjectId));
        $this->division_id = $input->getInt('divisionid');
        $this->request_url = Uri::getInstance()->toString();

        $layout = preg_replace('/_(?:3|4|5)$/', '', (string) $this->getLayout()) ?: 'default';
        $this->setLayout($layout);

        $model = $this->getModel();
        if (!$model instanceof JlextdfbkeyimportModel) {
            throw new \RuntimeException('DFB-key import view requires JlextdfbkeyimportModel.', 500);
        }

        $render = match ($layout) {
            'default_createdays' => $this->prepareCreatedays($model),
            'default_firstmatchday' => $this->prepareFirstMatchday($model),
            'default_savematchdays' => $this->prepareSaveMatchdays($model),
            'default_getdivision' => $this->prepareGetDivision($model),
            default => $this->prepareDefault($model),
        };

        if (!$render) {
            return false;
        }

        parent::display($tpl);
    }

    /**
     * Inspect the selected project and its scheduling prerequisites. Redirect to division selection, existing project or match management, or the appropriate round-creation or first-matchday step depending on the current data.
     * @param JlextdfbkeyimportModel $model DFB-key import model.
     * @return bool False because this step redirects instead of rendering.
     */
    private function prepareDefault(JlextdfbkeyimportModel $model): bool
    {
        $app = self::administratorApplication();
        $projectType = $model->getProjectType($this->project_id);

        if ($projectType === 'DIVISIONS_LEAGUE' && !$this->division_id) {
            $app->redirect(
                'index.php?option=com_sportsmanagement&view=jlextdfbkeyimport&layout=default_getdivision&pid=' . $this->project_id
            );
            return false;
        }

        $model->checkTable();

        if ($this->project_id <= 0) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_1'), Log::WARNING, 'jsmerror');
            $app->redirect('index.php?option=com_sportsmanagement&view=projects');
            return false;
        }

        $projectTeams = $model->getProjectteams($this->project_id, $this->division_id);
        if (!$projectTeams) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_5'), Log::WARNING, 'jsmerror');
            $app->redirect('index.php?option=com_sportsmanagement&view=projectteams');
            return false;
        }

        $dfbTeams = count($projectTeams);
        if (!$model->getDFBKey($dfbTeams, 'FIRST')) {
            $country = $model->getCountry($this->project_id);
            $app->redirect(
                'index.php?option=com_sportsmanagement&view=projects&return=jlextdfbkeyimporterror6'
                . '&dfbteams=' . $dfbTeams . '&dfbcountry=' . rawurlencode((string) $country)
            );
            return false;
        }

        if ($model->getMatchdays($this->project_id)) {
            $matchCount = $model->getMatches($this->project_id, $this->division_id);
            if ($matchCount > 0) {
                Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_2'), Log::WARNING, 'jsmerror');
                Log::add(
                    Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_7', $matchCount),
                    Log::WARNING,
                    'jsmerror'
                );
                $app->redirect('index.php?option=com_sportsmanagement&view=rounds');
                return false;
            }

            $app->redirect(
                'index.php?option=com_sportsmanagement&view=jlextdfbkeyimport'
                . '&layout=default_firstmatchday&pid=' . $this->project_id . '&divisionid=' . $this->division_id
            );
            return false;
        }

        $app->redirect(
            'index.php?option=com_sportsmanagement&view=jlextdfbkeyimport'
            . '&layout=default_createdays&pid=' . $this->project_id . '&divisionid=' . $this->division_id
        );

        return false;
    }

    /**
     * Load the DFB schedule key for the number of selected project teams and prepare the administrator toolbar and stylesheet for creating matchdays.
     * @param JlextdfbkeyimportModel $model DFB-key import model.
     * @return bool True to render the layout.
     */
    private function prepareCreatedays(JlextdfbkeyimportModel $model): bool
    {
        $projectTeams = $model->getProjectteams($this->project_id, $this->division_id);
        $this->newmatchdays = $projectTeams
            ? (array) ($model->getDFBKey(count($projectTeams), 'ALL') ?: [])
            : [];

        $this->addImporterStylesheet();
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_MATCHDAY_INFO_1'), 'dfbkey');
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=projects');
        ToolbarHelper::save('jlextdfbkeyimport.save', 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_INSERT_ROUNDS');
        ToolbarHelper::divider();

        return true;
    }

    /**
     * Prepare the project-team selection list and first-matchday DFB schedule key, then configure the toolbar for assigning the initial fixtures.
     * @param JlextdfbkeyimportModel $model DFB-key import model.
     * @return bool True to render the layout.
     */
    private function prepareFirstMatchday(JlextdfbkeyimportModel $model): bool
    {
        $projectTeams = $model->getProjectteams($this->project_id, $this->division_id);
        $options = [HTMLHelper::_('select.option', '0', '- ' . Text::_('Select projectteams') . ' -')];

        if ($projectTeams) {
            $options = array_merge($options, $projectTeams);
        }

        $this->dfbteams = max(0, count($options) - 1);
        $this->lists = [
            'projectteams' => $options,
            'dfbday' => $model->getDFBKey($this->dfbteams, 'FIRST') ?: [],
        ];

        $this->addImporterStylesheet();
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_1'), 'dfbkey');
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=projects');
        ToolbarHelper::apply('jlextdfbkeyimport.apply', 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_INSERT_FIRST_DAY');
        ToolbarHelper::divider();

        return true;
    }

    /**
     * Restore the first-matchday selection from administrator user state, resolve its project and division context and generate the schedule preview and insertion toolbar.
     * @param JlextdfbkeyimportModel $model DFB-key import model.
     * @return bool True to render the layout.
     */
    private function prepareSaveMatchdays(JlextdfbkeyimportModel $model): bool
    {
        $app = self::administratorApplication();
        $post = $app->getUserState('com_sportsmanagement.first_post', []);
        $post = is_array($post) ? $post : [];
        $this->division_id = $app->getInput()->getInt(
            'divisionid',
            (int) ($post['divisionid'] ?? 0)
        );
        $this->project_id = $this->project_id ?: (int) ($post['projectid'] ?? 0);
        $this->import = (array) ($model->getSchedule($post, $this->project_id, $this->division_id) ?: []);

        $this->addImporterStylesheet();
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_SAVE_MATCHDAY_INFO_1'), 'dfbkey');
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=projects');
        ToolbarHelper::save('jlextdfbkeyimport.insert', 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_INSERT_MATCHDAYS');
        ToolbarHelper::divider();

        return true;
    }

    /**
     * Load the available project divisions, prepend the selection prompt and configure the toolbar for continuing with a selected division.
     * @param JlextdfbkeyimportModel $model DFB-key import model.
     * @return bool True to render the layout.
     */
    private function prepareGetDivision(JlextdfbkeyimportModel $model): bool
    {
        $projectDivisions = $model->getDivisions($this->project_id);
        $divisions = [HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DIVISION'))];

        if ($projectDivisions) {
            $divisions = array_merge($divisions, $projectDivisions);
        }

        $this->division = 0;
        $this->lists = ['divisions' => $divisions];
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=projects');
        ToolbarHelper::save(
            'jlextdfbkeyimport.getdivisionfirst',
            'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_USE_DIVISION'
        );

        return true;
    }

    /**
     * Register and activate the DFB import stylesheet through Joomla's Web Asset Manager when the active document is an HTML document.
     * @return void
     */
    private function addImporterStylesheet(): void
    {
        $document = $this->getDocument();

        if ($document instanceof HtmlDocument) {
            $document->getWebAssetManager()->registerAndUseStyle(
                'com_sportsmanagement.admin.dfbkey',
                'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css',
                ['version' => 'auto']
            );
        }
    }
    /**
     * Retrieve the Joomla administrator application from the DI container and verify that it belongs to the administrator client.
     * @return AdministratorApplication Active administrator application.
     * @throws \RuntimeException If the resolved application is not the administrator client.
     */
    private static function administratorApplication(): AdministratorApplication
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement DFB-key import view requires the Joomla administrator application.', 500);
        }

        return $app;
    }

}
