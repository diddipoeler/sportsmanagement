<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Project;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectModel;
use Diddipoeler\Component\SportsManagement\Administrator\Service\ProjectPanelService;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Database\DatabaseInterface;

/** Native Joomla 5/6 administrator project edit and control-panel view. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;
    public $project;
    public $user;
    public array $lists = [];
    public array $notes = [];
    public $extended = null;
    public $extendeduser = null;
    public int $checkextrafields = 0;
    public int $count_projectdivisions = 0;
    public int $count_projectpositions = 0;
    public int $count_projectreferees = 0;
    public int $count_projectteams = 0;
    public int $count_matchdays = 0;
    public string $option = 'com_sportsmanagement';
    public string $view = 'project';
    public string $tmpl = '';

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $this->user = $app->getIdentity();
        $this->tmpl = $input->getCmd('tmpl', '');

        $model = $this->getModel();
        if (!$model instanceof ProjectModel) {
            throw new \RuntimeException('Project model could not be loaded.', 500);
        }

        $layout = strtolower((string) $this->getLayout());
        if (in_array($layout, ['panel', 'panel_3', 'panel_4'], true)) {
            $this->setLayout('panel');
            $this->displayPanel($model);
            parent::display($tpl);
            return;
        }

        $this->setLayout('edit');
        $this->displayEdit($model);
        parent::display($tpl);
    }

    private function displayPanel(ProjectModel $model): void
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $this->item = $this->get('Item');
        $this->state = $this->get('State');
        $this->project = $this->item;

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if (!$this->item || (int) ($this->item->id ?? 0) <= 0) {
            throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_ERROR'), 404);
        }

        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        $databaseSelector = $input->getInt(
            'cfg_which_database',
            (int) $app->getUserState($this->option . '.cfg_which_database', 0)
        );
        $sportsManagementDatabase = SportsManagementDatabaseResolver::resolve(
            $joomlaDatabase,
            $databaseSelector
        );
        $counts = (new ProjectPanelService($sportsManagementDatabase))->getCounts($this->item);

        $this->count_projectdivisions = (int) $counts['divisions'];
        $this->count_projectpositions = (int) $counts['positions'];
        $this->count_projectreferees = (int) $counts['referees'];
        $this->count_projectteams = (int) $counts['teams'];
        $this->count_matchdays = (int) $counts['rounds'];

        $app->setUserState($this->option . '.pid', (int) $this->item->id);
        $app->setUserState($this->option . '.season_id', (int) ($this->item->season_id ?? 0));
        $app->setUserState($this->option . '.project_art_id', (int) ($this->item->project_art_id ?? 0));
        $app->setUserState($this->option . '.sports_type_id', (int) ($this->item->sports_type_id ?? 0));

        if (ComponentHelper::getParams($this->option)->get('show_jsm_tips')) {
            $this->notes[] = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_NOTES');
        }

        ToolbarHelper::title((string) $this->item->name, 'project');
        ToolbarHelper::back(
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_TITLE'),
            Route::_('index.php?option=com_sportsmanagement&view=projects', false)
        );
        ToolbarHelper::back(
            'JSM Panel',
            Route::_('index.php?option=com_sportsmanagement&view=cpanel', false)
        );
    }

    private function displayEdit(ProjectModel $model): void
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $input->set('hidemainmenu', true);

        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }
        if (!$this->form || !$this->item) {
            throw new \RuntimeException('Project form could not be loaded.', 500);
        }

        if (!class_exists('sportsmanagementHelper', false)) {
            require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';
        }

        $isNew = (int) ($this->item->id ?? 0) === 0;
        $userId = (int) $this->user->id;

        if (empty($this->item->admin)) {
            $this->form->setValue('admin', null, $userId);
        }
        if (empty($this->item->editor)) {
            $this->form->setValue('editor', null, $userId);
        }

        $sportsTypeId = (int) ($this->item->sports_type_id ?? 0);
        $agegroupId = (int) ($this->item->agegroup_id ?? 0);
        $this->form->setValue('sports_type_id', 'request', $sportsTypeId);
        $this->form->setValue('agegroup_id', 'request', $agegroupId);

        $this->extended = \sportsmanagementHelper::getExtended(
            (string) ($this->item->extended ?? ''),
            'project'
        );
        $this->extendeduser = \sportsmanagementHelper::getExtendedUser(
            (string) ($this->item->extendeduser ?? ''),
            'project'
        );

        if ($isNew) {
            $this->form->setValue('start_date', null, '');
            $this->form->setValue('start_time', null, '18:00');
            $this->form->setValue('admin', null, $userId);
            $this->form->setValue('editor', null, $userId);
        } else {
            if ((string) ($this->item->start_date ?? '') === '0000-00-00') {
                $this->item->start_date = '';
                $this->form->setValue('start_date', null, '');
            }

            $picture = trim((string) ($this->item->picture ?? ''));
            if ($picture === '' || basename($picture) === '') {
                $this->item->picture = 'images/com_sportsmanagement/database/placeholders/placeholder_450_2.png';
                $this->form->setValue('picture', null, $this->item->picture);
            }
        }

        $this->checkextrafields = (int) \sportsmanagementHelper::checkUserExtraFields('backend', 0, 'project');
        if ($this->checkextrafields && !$isNew) {
            $this->lists['ext_fields'] = \sportsmanagementHelper::getUserExtraFields(
                (int) $this->item->id,
                'backend',
                0,
                'project'
            );
        }

        $favTeams = trim((string) ($this->item->fav_team ?? ''));
        $this->form->setValue('fav_team', null, $favTeams === '' ? [] : explode(',', $favTeams));
        $app->setUserState($this->option . '.itemname', (string) ($this->item->name ?? ''));

        ToolbarHelper::title(
            $isNew
                ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_ADD_NEW')
                : Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_EDIT', (string) $this->item->name),
            'project'
        );

        $articleComponent = (string) ComponentHelper::getParams($this->option)->get('which_article_component');
        if ($articleComponent === 'com_content') {
            ToolbarHelper::link(
                Route::_('index.php?option=com_categories&extension=com_content', false),
                Text::_('JCATEGORIES'),
                'featured'
            );
        } elseif ($articleComponent === 'com_k2') {
            ToolbarHelper::link(
                Route::_('index.php?option=com_k2&view=categories', false),
                Text::_('JCATEGORIES'),
                'featured'
            );
        }

        ToolbarHelper::apply('project.apply');
        ToolbarHelper::save('project.save');
        ToolbarHelper::save2new('project.save2new');
        ToolbarHelper::save2copy('project.save2copy');
        ToolbarHelper::cancel('project.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
