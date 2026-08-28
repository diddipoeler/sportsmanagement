<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlxmlimports;

\defined('_JEXEC') or die;

use DateTimeZone;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\CountryOptionsHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use RuntimeException;

/** Native Joomla 5/6 XML import workflow view. */
final class HtmlView extends BaseHtmlView
{
    public $filter_season = 0;
    public $config;
    public $upload_maxsize = '200';
    public $request_url = '';
    public $projektfussballineuropa;
    public $country = 'DEU';
    public $countryFlag = '';
    public $countries = [];
    public $agegroup = '';
    public $seasons = [];
    public $templates = [];
    public $title = '';
    public $icon = 'xmlimports';
    public $sidebar = '';
    public $option = 'com_sportsmanagement';
    public $whichfile;
    public $projectidimport;
    public $uploadArray = [];
    public $starttime = 0.0;
    public $xml = [];
    public $leagues = [];
    public $sportstypes = [];
    public $admins = [];
    public $editors = [];
    public $teams = [];
    public $clubs = [];
    public $clubsteams = [];
    public $events = [];
    public $positions = [];
    public $parentpositions = [];
    public $playgrounds = [];
    public $persons = [];
    public $statistics = [];
    public $OldCountries = [];
    public $import_version = '';
    public $show_debug_info = 0;
    public $search_agegroup = [];
    public $agegroup_id = 0;
    public $master_template = 0;
    public $lists = [];
    public $importData = [];
    public $postData = [];
    public $selectType;
    public $recordID;

    public function display($tpl = null)
    {
        $layout = preg_replace('/_(?:3|4|5)$/', '', (string) $this->getLayout()) ?: 'default';
        $this->setLayout($layout);
        $this->addTemplatePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/footer/tmpl');

        $this->init($tpl);

        if ($layout !== 'selectpage' && $this->title !== '') {
            ToolbarHelper::title($this->title, $this->icon);
        }

        parent::display($tpl);
    }

    private function init($tpl = null): void
    {
        $app = Factory::getApplication();
        $lang = $app->getLanguage();
        $input = $app->getInput();
        $this->option = $input->getCmd('option', 'com_sportsmanagement');
        $this->filter_season = $input->getInt('filter_season', 0);

        $model = $this->createAdminModel('Jlxmlimport');
        $this->getDocument()->addScript(
            Uri::root(true) . '/administrator/components/' . $this->option . '/assets/js/jlxmlimports.js'
        );

        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TITLE_1_3');
        $this->icon = 'xmlimports';
        $this->config = ComponentHelper::getParams('com_media');
        $this->upload_maxsize = $this->config->get('upload_maxsize', '200');
        $this->request_url = Uri::getInstance()->toString();
        $this->projektfussballineuropa = $model->getDataUpdateImportID();
        $this->templates = $model->getTemplateList();

        $database = (new SportsManagementDatabaseResolver())->resolve();
        $languageParts = explode('-', $lang->getTag());
        $this->country = CountryOptionsHelper::iso2To3(
            $database,
            (string) ($languageParts[1] ?? 'DE')
        );
        $countryOptions = CountryOptionsHelper::getOptions($database);
        $this->countryFlag = CountryOptionsHelper::getFlag($database, $this->country);
        $this->countries = HTMLHelper::_(
            'select.genericlist',
            $countryOptions,
            'country',
            'class="form-select" size="1"',
            'value',
            'text',
            $this->country
        );

        $agegroupOptions = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP')),
        ];
        $agegroups = $this->createAdminModel('Agegroups')->getAgeGroups('', 0);
        if ($agegroups) {
            $agegroupOptions = array_merge($agegroupOptions, $agegroups);
        }
        $this->agegroup = HTMLHelper::_(
            'select.genericlist',
            $agegroupOptions,
            'agegroup',
            'class="form-select" size="1"',
            'value',
            'text',
            0
        );

        $seasonOptions = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SEASON_SELECT')),
        ];
        $seasons = $this->createAdminModel('Seasons')->getSeasons(true);
        $seasonOptions = array_merge($seasonOptions, $seasons ?: []);
        $this->seasons = HTMLHelper::_(
            'select.genericlist',
            $seasonOptions,
            'seasons',
            'class="form-select" size="1"',
            'value',
            'text',
            0
        );

        match ($this->getLayout()) {
            'form' => $this->displayForm(),
            'update' => $this->displayUpdate(),
            'info' => $this->displayInfo(),
            'selectpage' => $this->displaySelectpage(),
            default => null,
        };
    }

    private function displayForm(): void
    {
        $this->starttime = microtime(true);
        $app = Factory::getApplication();
        $input = $app->getInput();
        $post = $input->post->getArray();
        $model = $this->createAdminModel('Jlxmlimport');
        $data = $model->getData($post);
        $lists = [];

        $timezone = isset($data['project']->timezone) ? $data['project']->timezone : 321;
        $zones = DateTimeZone::listIdentifiers();
        $projectId = $input->getInt('project_id', 0);
        $lists['timezone'] = HTMLHelper::_(
            'select.genericList',
            $zones,
            'timezone',
            'class="form-select"',
            'value',
            'text',
            $timezone
        );

        $this->whichfile = $app->getUserState($this->option . 'whichfile');
        $this->projectidimport = $app->getUserState($this->option . 'projectidimport');
        $this->uploadArray = $app->getUserState($this->option . 'uploadArray', []);
        $this->countries = CountryOptionsHelper::getOptions((new SportsManagementDatabaseResolver())->resolve());
        $this->xml = $data;

        $this->leagues = $this->createAdminModel('Leagues')->getLeagues();
        $this->seasons = $this->createAdminModel('Seasons')->getSeasons();
        $this->sportstypes = $this->createAdminModel('Sportstypes')->getSportsTypes();
        $this->admins = $model->getUserList(false);
        $this->editors = $model->getUserList(false);
        $this->templates = $model->getTemplateList();
        $lists['templates'] = array_merge(
            [HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TEMPLATES_USEOWN'))],
            $this->templates ?: []
        );

        $this->teams = $this->createAdminModel('Teams')->getTeamListSelect();
        $this->clubs = $this->createAdminModel('Clubs')->getClubListSelect();
        $this->events = $this->createAdminModel('Eventtypes')->getEventList();
        $positionsModel = $this->createAdminModel('Positions');
        $this->positions = $positionsModel->getPositionListSelect();
        $this->parentpositions = $positionsModel->getParentsPositions();
        $this->playgrounds = $this->createAdminModel('Playgrounds')->getPlaygroundListSelect();
        $this->persons = $this->createAdminModel('Players')->getPersonListSelect();
        $this->statistics = $this->createAdminModel('Statistics')->getStatisticListSelect();
        $this->OldCountries = $model->getCountryByOldid();
        $this->import_version = (string) $model->import_version;
        $this->show_debug_info = ComponentHelper::getParams($this->option)->get('show_debug_info', 0);

        $agegroupOptions = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP')),
        ];
        $agegroups = $this->createAdminModel('Agegroups')->getAgeGroups('', 0);
        if ($agegroups) {
            $agegroupOptions = array_merge($agegroupOptions, $agegroups);
            $this->search_agegroup = $agegroups;
        }

        $projectData = $data['project'] ?? null;
        $this->agegroup_id = !empty($projectData->agegroup_id)
            ? $projectData->agegroup_id
            : $this->defaultStateValue('filter.search_agegroup', 0);
        $this->master_template = !empty($projectData->master_template)
            ? $projectData->master_template
            : 0;
        $lists['agegroup'] = $agegroupOptions;
        $lists['agegroup2'] = HTMLHelper::_(
            'select.genericlist',
            $agegroupOptions,
            'filter_search_agegroup',
            'class="form-select" style="width:140px" onchange="this.form.submit();"',
            'value',
            'text',
            $this->agegroup_id
        );
        $this->lists = $lists;

        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TITLE_2_3');
        $this->icon = 'xmlimport';
        ToolbarHelper::custom(
            'jlxmlimport.insert',
            'upload',
            'upload',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_START_BUTTON'),
            false
        );
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=cpanel');

        $this->getDocument()->addScript(
            Uri::root(true) . '/administrator/components/com_sportsmanagement/assets/js/sm_functions.js'
        );
        $siteName = (string) $app->get('sitename', '');
        $this->getDocument()->addScriptDeclaration(
            "registerproject('" . Uri::base() . "','" . $projectId . "','" . addslashes($siteName) . "','1');\n"
        );
    }

    private function displayUpdate(): void
    {
        $app = Factory::getApplication();
        $post = $app->getInput()->post->getArray();
        $model = $this->createAdminModel('Jlxmlimport');
        $data = $model->getData($post);

        $this->xml = $data;
        $this->importData = $model->getDataUpdate();
        $this->projektfussballineuropa = $model->getDataUpdateImportID();
        $this->getDocument()->addStyleSheet(
            Uri::root(true) . '/administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'
        );
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TITLE_1_4');
        $this->icon = 'xmlimport';
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=cpanel');
    }

    private function displayInfo(): void
    {
        $input = Factory::getApplication()->getInput();
        $data = $input->post->getArray();
        $model = $this->createAdminModel('Jlxmlimport');

        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TITLE_3_3');
        $this->icon = 'xmlimport';
        $this->starttime = microtime(true);
        $this->importData = $model->importData($data);
        $this->postData = $data;
        ToolbarHelper::divider();
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=projects');
    }

    private function displaySelectpage(): void
    {
        $app = Factory::getApplication();
        $model = $this->createAdminModel('Jlxmlimport');
        $lists = [];

        $this->request_url = Uri::getInstance()->toString();
        $this->selectType = $app->getUserState($this->option . 'selectType');
        $this->recordID = $app->getUserState($this->option . 'recordID');

        switch ((string) $this->selectType) {
            case '10':
                $this->clubs = $model->getNewClubListSelect();
                $lists['clubs'] = $this->selectionList(
                    $this->clubs,
                    'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_CLUB',
                    'clubID',
                    'class="form-select select-club" onchange="javascript:insertNewClub(\'' . $this->recordID . '\')"'
                );
                break;
            case '9':
                $this->clubsteams = $model->getClubAndTeamListSelect();
                $lists['clubsteams'] = $this->selectionList(
                    $this->clubsteams,
                    'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_CLUB_AND_TEAM',
                    'teamID',
                    'class="form-select select-team" onchange="javascript:insertClubAndTeam(\'' . $this->recordID . '\')"'
                );
                break;
            case '8':
                $this->statistics = $this->createAdminModel('Statistics')->getStatisticListSelect();
                $lists['statistics'] = $this->selectionList(
                    $this->statistics,
                    'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_STATISTIC',
                    'statisticID',
                    'class="form-select select-statistic" onchange="javascript:insertStatistic(\'' . $this->recordID . '\')"'
                );
                break;
            case '7':
                $this->parentpositions = $this->createAdminModel('Positions')->getParentsPositions();
                $lists['parentpositions'] = $this->selectionList(
                    $this->parentpositions,
                    'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_PARENT_POSITION',
                    'parentPositionID',
                    'class="form-select select-parentposition" onchange="javascript:insertParentPosition(\'' . $this->recordID . '\')"'
                );
                break;
            case '6':
                $this->positions = $this->createAdminModel('Positions')->getPositionListSelect();
                $lists['positions'] = $this->selectionList(
                    $this->positions,
                    'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_POSITION',
                    'positionID',
                    'class="form-select select-position" onchange="javascript:insertPosition(\'' . $this->recordID . '\')"'
                );
                break;
            case '5':
                $this->events = $this->createAdminModel('Eventtypes')->getEventList();
                $lists['events'] = $this->selectionList(
                    $this->events,
                    'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_EVENT',
                    'eventID',
                    'class="form-select select-event" onchange="javascript:insertEvent(\'' . $this->recordID . '\')"'
                );
                break;
            case '4':
                $this->playgrounds = $this->createAdminModel('Playgrounds')->getPlaygroundListSelect();
                $lists['playgrounds'] = $this->selectionList(
                    $this->playgrounds,
                    'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_PLAYGROUND',
                    'playgroundID',
                    'class="form-select select-playground" onchange="javascript:insertPlayground(\'' . $this->recordID . '\')"'
                );
                break;
            case '3':
                $this->persons = $this->createAdminModel('Players')->getPersonListSelect();
                $lists['persons'] = $this->selectionList(
                    $this->persons,
                    'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_PERSON',
                    'personID',
                    'class="form-select select-person" onchange="javascript:insertPerson(\'' . $this->recordID . '\')"'
                );
                break;
            case '2':
                $this->clubs = $this->createAdminModel('Clubs')->getClubListSelect();
                $lists['clubs'] = $this->selectionList(
                    $this->clubs,
                    'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_CLUB',
                    'clubID',
                    'class="form-select select-club" onchange="javascript:insertClub(\'' . $this->recordID . '\')"'
                );
                break;
            case '1':
            default:
                $this->teams = $this->createAdminModel('Teams')->getTeamListSelect();
                $this->clubs = $this->createAdminModel('Clubs')->getClubListSelect();
                $lists['teams'] = $this->selectionList(
                    $this->teams,
                    'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_TEAM',
                    'teamID',
                    'class="form-select select-team" onchange="javascript:insertTeam(\'' . $this->recordID . '\')"'
                );
                break;
        }

        $this->lists = $lists;
        $this->getDocument()->setTitle(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ASSIGN_TITLE'));
    }

    private function selectionList($items, string $label, string $name, string $attributes): string
    {
        $options = [HTMLHelper::_('select.option', 0, Text::_($label))];
        $options = array_merge($options, $items ?: []);

        return HTMLHelper::_(
            'select.genericlist',
            $options,
            $name,
            $attributes,
            'value',
            'text',
            0
        );
    }

    private function defaultStateValue(string $key, mixed $default = null): mixed
    {
        $model = $this->getModel();

        if ($model && method_exists($model, 'getState')) {
            return $model->getState($key, $default);
        }

        return $default;
    }

    private function createAdminModel(string $name, array $config = []): object
    {
        $model = Factory::getApplication()
            ->bootComponent('com_sportsmanagement')
            ->getMVCFactory()
            ->createModel($name, 'Administrator', $config);

        if ($model === null) {
            throw new RuntimeException('SportsManagement model not found: ' . $name, 500);
        }

        return $model;
    }
}
