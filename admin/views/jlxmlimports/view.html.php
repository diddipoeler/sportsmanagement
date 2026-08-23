<?php
/**
 * SportsManagement XML import view compatibility implementation for Joomla 5/6.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

class sportsmanagementViewJLXMLImports extends sportsmanagementView
{
    public function init($tpl = null)
    {
        $app = Factory::getApplication();
        $lang = $app->getLanguage();
        $jinput = $app->getInput();
        $option = $jinput->getCmd('option', 'com_sportsmanagement');
        $this->filter_season = $jinput->getInt('filter_season', 0);

        $model = $this->createAdminModel('Jlxmlimport');
        $this->document->addScript(
            Uri::root(true) . '/administrator/components/' . $option . '/assets/js/jlxmlimports.js'
        );

        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TITLE_1_3');
        $this->icon = 'xmlimports';
        $uri = Uri::getInstance();

        $this->config = ComponentHelper::getParams('com_media');
        $this->upload_maxsize = $this->config->get('upload_maxsize', '200');
        $this->request_url = $uri->toString();
        $this->projektfussballineuropa = $model->getDataUpdateImportID();

        $languageParts = explode('-', $lang->getTag());
        $country = JSMCountries::convertIso2to3((string) ($languageParts[1] ?? 'DE'));
        $this->country = $country;

        $seasonModel = $this->createAdminModel('Seasons');
        $seasons = $seasonModel->getSeasons(true);

        $countries = JSMCountries::getCountryOptions();
        $this->countries = HTMLHelper::_(
            'select.genericlist',
            $countries,
            'country',
            'class="form-select" size="1"',
            'value',
            'text',
            $country
        );

        $agegroupOptions = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP')),
        ];
        $agegroupModel = $this->createAdminModel('Agegroups');
        $agegroups = $agegroupModel->getAgeGroups('', 0);

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

        switch ($this->getLayout()) {
            case 'form':
            case 'form_3':
            case 'form_4':
                $this->_displayForm($tpl);
                break;

            case 'update':
            case 'update_3':
            case 'update_4':
                $this->_displayUpdate($tpl);
                break;

            case 'info':
            case 'info_3':
            case 'info_4':
                $this->_displayInfo($tpl);
                break;

            case 'selectpage':
            case 'selectpage_3':
            case 'selectpage_4':
                $this->_displaySelectpage($tpl);
                break;
        }
    }

    private function _displayForm($tpl)
    {
        $starttime = microtime(true);
        $app = Factory::getApplication();
        $post = $app->getInput()->post->getArray();
        $jinput = $app->getInput();
        $option = $jinput->getCmd('option', 'com_sportsmanagement');
        $model = $this->createAdminModel(
            'Jlxmlimport',
            ['dbo' => sportsmanagementHelper::getDBConnection()]
        );
        $data = $model->getData($post);
        $uploadArray = $app->getUserState($option . 'uploadArray', []);

        $value = isset($data['project']->timezone) ? $data['project']->timezone : 321;
        $zones = DateTimeZone::listIdentifiers();
        $projectid = $jinput->getInt('project_id', 0);
        $lists['timezone'] = HTMLHelper::_(
            'select.genericList',
            $zones,
            'timezone',
            'class="form-select"',
            'value',
            'text',
            $value
        );

        $this->option = $option;
        $this->whichfile = $app->getUserState($option . 'whichfile');
        $this->projectidimport = $app->getUserState($option . 'projectidimport');
        $this->uploadArray = $uploadArray;
        $this->starttime = $starttime;
        $this->countries = JSMCountries::getCountryOptions();
        $this->xml = $data;

        $this->leagues = $this->createAdminModel('Leagues')->getLeagues();
        $this->seasons = $this->createAdminModel('Seasons')->getSeasons();
        $this->sportstypes = $this->createAdminModel('Sportstypes')->getSportsTypes();
        $this->admins = $model->getUserList(false);
        $this->editors = $model->getUserList(false);
        $this->templates = $model->getTemplateList();

        $templateOptions = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TEMPLATES_USEOWN')),
        ];
        $templateOptions = array_merge($templateOptions, $this->templates ?: []);
        $lists['templates'] = $templateOptions;

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
        $this->import_version = $model->import_version;
        $this->show_debug_info = ComponentHelper::getParams($option)->get('show_debug_info', 0);

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
            : $this->state->get('filter.search_agegroup');
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

        $this->document->addScript(
            Uri::root(true) . '/administrator/components/com_sportsmanagement/assets/js/sm_functions.js'
        );
        $siteName = (string) $app->get('sitename', '');
        $js = "registerproject('" . Uri::base() . "','" . $projectid . "','" . addslashes($siteName) . "','1');\n";
        $this->document->addScriptDeclaration($js);
        $this->setLayout('form');
    }

    private function _displayUpdate($tpl)
    {
        $app = Factory::getApplication();
        $post = $app->getInput()->post->getArray();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement');
        $model = $this->createAdminModel('Jlxmlimport');
        $data = $model->getData($post);

        $this->xml = $data;
        $this->importData = $model->getDataUpdate();
        $this->projektfussballineuropa = $model->getDataUpdateImportID();
        $this->option = $option;

        $stylelink = '<link rel="stylesheet" href="'
            . Uri::root()
            . 'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css" type="text/css" />\n';
        $this->document->addCustomTag($stylelink);
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TITLE_1_4');
        $this->icon = 'xmlimport';
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=cpanel');
        $this->setLayout('update');
    }

    private function _displayInfo($tpl)
    {
        $app = Factory::getApplication();
        $jinput = $app->getInput();
        $option = $jinput->getCmd('option', 'com_sportsmanagement');
        $starttime = microtime(true);
        $model = $this->createAdminModel(
            'Jlxmlimport',
            ['dbo' => sportsmanagementHelper::getDBConnection()]
        );
        $data = $jinput->post->getArray();

        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TITLE_3_3');
        $this->icon = 'xmlimport';
        $this->starttime = $starttime;
        $this->importData = $model->importData($data);
        $this->postData = $data;
        $this->option = $option;

        ToolbarHelper::divider();
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=projects');
        $this->setLayout('info');
    }

    private function _displaySelectpage($tpl)
    {
        $app = Factory::getApplication();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement');
        $uri = Uri::getInstance();
        $model = $this->createAdminModel('Jlxmlimport');
        $lists = [];

        $this->request_url = $uri->toString();
        $this->selectType = $app->getUserState($option . 'selectType');
        $this->recordID = $app->getUserState($option . 'recordID');
        $this->option = $option;

        switch ((string) $this->selectType) {
            case '10':
                $this->clubs = $model->getNewClubListSelect();
                $options = [
                    HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_CLUB')),
                ];
                $options = array_merge($options, $this->clubs ?: []);
                $lists['clubs'] = HTMLHelper::_(
                    'select.genericlist',
                    $options,
                    'clubID',
                    'class="form-select select-club" onchange="javascript:insertNewClub(\'' . $this->recordID . '\')"',
                    'value',
                    'text',
                    0
                );
                break;

            case '9':
                $this->clubsteams = $model->getClubAndTeamListSelect();
                $options = [
                    HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_CLUB_AND_TEAM')),
                ];
                $options = array_merge($options, $this->clubsteams ?: []);
                $lists['clubsteams'] = HTMLHelper::_(
                    'select.genericlist',
                    $options,
                    'teamID',
                    'class="form-select select-team" onchange="javascript:insertClubAndTeam(\'' . $this->recordID . '\')"',
                    'value',
                    'text',
                    0
                );
                break;

            case '8':
                $this->statistics = $this->createAdminModel('Statistics')->getStatisticListSelect();
                $options = [
                    HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_STATISTIC')),
                ];
                $options = array_merge($options, $this->statistics ?: []);
                $lists['statistics'] = HTMLHelper::_(
                    'select.genericlist',
                    $options,
                    'statisticID',
                    'class="form-select select-statistic" onchange="javascript:insertStatistic(\'' . $this->recordID . '\')"',
                    'value',
                    'text',
                    0
                );
                break;

            case '7':
                $this->parentpositions = $this->createAdminModel('Positions')->getParentsPositions();
                $options = [
                    HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_PARENT_POSITION')),
                ];
                $options = array_merge($options, $this->parentpositions ?: []);
                $lists['parentpositions'] = HTMLHelper::_(
                    'select.genericlist',
                    $options,
                    'parentPositionID',
                    'class="form-select select-parentposition" onchange="javascript:insertParentPosition(\'' . $this->recordID . '\')"',
                    'value',
                    'text',
                    0
                );
                break;

            case '6':
                $this->positions = $this->createAdminModel('Positions')->getPositionListSelect();
                $options = [
                    HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_POSITION')),
                ];
                $options = array_merge($options, $this->positions ?: []);
                $lists['positions'] = HTMLHelper::_(
                    'select.genericlist',
                    $options,
                    'positionID',
                    'class="form-select select-position" onchange="javascript:insertPosition(\'' . $this->recordID . '\')"',
                    'value',
                    'text',
                    0
                );
                break;

            case '5':
                $this->events = $this->createAdminModel('Eventtypes')->getEventList();
                $options = [
                    HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_EVENT')),
                ];
                $options = array_merge($options, $this->events ?: []);
                $lists['events'] = HTMLHelper::_(
                    'select.genericlist',
                    $options,
                    'eventID',
                    'class="form-select select-event" onchange="javascript:insertEvent(\'' . $this->recordID . '\')"',
                    'value',
                    'text',
                    0
                );
                break;

            case '4':
                $this->playgrounds = $this->createAdminModel('Playgrounds')->getPlaygroundListSelect();
                $options = [
                    HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_PLAYGROUND')),
                ];
                $options = array_merge($options, $this->playgrounds ?: []);
                $lists['playgrounds'] = HTMLHelper::_(
                    'select.genericlist',
                    $options,
                    'playgroundID',
                    'class="form-select select-playground" onchange="javascript:insertPlayground(\'' . $this->recordID . '\')"',
                    'value',
                    'text',
                    0
                );
                break;

            case '3':
                $this->persons = $this->createAdminModel('Players')->getPersonListSelect();
                $options = [
                    HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_PERSON')),
                ];
                $options = array_merge($options, $this->persons ?: []);
                $lists['persons'] = HTMLHelper::_(
                    'select.genericlist',
                    $options,
                    'personID',
                    'class="form-select select-person" onchange="javascript:insertPerson(\'' . $this->recordID . '\')"',
                    'value',
                    'text',
                    0
                );
                break;

            case '2':
                $this->clubs = $this->createAdminModel('Clubs')->getClubListSelect();
                $options = [
                    HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_CLUB')),
                ];
                $options = array_merge($options, $this->clubs ?: []);
                $lists['clubs'] = HTMLHelper::_(
                    'select.genericlist',
                    $options,
                    'clubID',
                    'class="form-select select-club" onchange="javascript:insertClub(\'' . $this->recordID . '\')"',
                    'value',
                    'text',
                    0
                );
                break;

            case '1':
            default:
                $this->teams = $this->createAdminModel('Teams')->getTeamListSelect();
                $this->clubs = $this->createAdminModel('Clubs')->getClubListSelect();
                $options = [
                    HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_TEAM')),
                ];
                $options = array_merge($options, $this->teams ?: []);
                $lists['teams'] = HTMLHelper::_(
                    'select.genericlist',
                    $options,
                    'teamID',
                    'class="form-select select-team" onchange="javascript:insertTeam(\'' . $this->recordID . '\')"',
                    'value',
                    'text',
                    0
                );
                break;
        }

        $this->lists = $lists;
        $this->document->setTitle(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ASSIGN_TITLE'));
        $this->setLayout('selectpage');
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
