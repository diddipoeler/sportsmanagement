<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage jlxmlimports
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;

jimport('joomla.html.parameter.element.timezones');

/**
 * sportsmanagementViewJLXMLImports
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewJLXMLImports extends sportsmanagementView {

    /**
     * sportsmanagementViewJLXMLImports::init()
     * 
     * @param mixed $tpl
     * @return
     */
    public function init($tpl = null) {
        $app = Factory::getApplication();
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $filter_season = $jinput->getInt('filter_season', 0);
        $this->filter_season = $filter_season;

        // Get a refrence of the page instance in joomla
        $document = Factory::getDocument();
        $model = BaseDatabaseModel::getInstance('jlxmlimport', 'sportsmanagementmodel');
        $document->addScript(JUri::root(true) . '/administrator/components/' . $option . '/assets/js/jlxmlimports.js');

        //$app->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' getLayout <br><pre>'.print_r($this->getLayout(),true).'</pre>'),'');

        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TITLE_1_3');
        $this->icon = 'xmlimports';
        if (version_compare(JSM_JVERSION, '4', 'eq')) {
            $uri = JUri::getInstance();
        } else {
            $uri = Factory::getURI();
        }
        $config = ComponentHelper::getParams('com_media');
        $upload_maxsize = ComponentHelper::getParams('com_media')->get('upload_maxsize', '200');
        $post = $jinput->post->getArray(array());
        $files = $jinput->getString('files');
        $this->request_url = $uri->toString();
        $this->upload_maxsize = $upload_maxsize;
        $this->config = $config;
        $this->projektfussballineuropa = $model->getDataUpdateImportID();

        switch ($this->getLayout()) {
            case 'form';
            case 'form_3';
            case 'form_4';
                $this->_displayForm($tpl);
                break;
            case 'update';
            case 'update_3';
            case 'update_4';
                $this->_displayUpdate($tpl);
                break;
            case 'info';
            case 'info_3';
            case 'info_4';
                $this->_displayInfo($tpl);
                break;
            case 'selectpage';
            case 'selectpage_3';
            case 'selectpage_4';
                $this->_displaySelectpage($tpl);
                break;
        }
    }

    /**
     * sportsmanagementViewJLXMLImports::_displayUpdate()
     * 
     * @param mixed $tpl
     * @return void
     */
    private function _displayUpdate($tpl) {
        $app = Factory::getApplication();
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        //$project_id = (int) $app->getUserState($option.'project', 0);
        //$app->enqueueMessage(Text::_('_displayUpdate project_id -> '.'<pre>'.print_r($project_id ,true).'</pre>' ),'');
        $model = BaseDatabaseModel::getInstance('jlxmlimport', 'sportsmanagementmodel');
        $data = $model->getData();
        $update_matches = $model->getDataUpdate();
        $this->xml = $data;
        $this->importData = $update_matches;
        $this->projektfussballineuropa = $model->getDataUpdateImportID();
        $this->option = $option;

        // Get a refrence of the page instance in joomla
        $document = Factory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="' . JURI::root() . 'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css' . '" type="text/css" />' . "\n";
        $document->addCustomTag($stylelink);
        // Set toolbar items for the page
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TITLE_1_4');
        $this->icon = 'xmlimport';
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=cpanel');

        $this->setLayout('update');
        //parent::addToolbar();
        //parent::display($tpl);
    }

    /**
     * sportsmanagementViewJLXMLImports::_displayForm()
     * 
     * @param mixed $tpl
     * @return void
     */
    private function _displayForm($tpl) {
        $mtime = microtime();
        $mtime = explode(" ", $mtime);
        $mtime = $mtime[1] + $mtime[0];
        $starttime = $mtime;

        $app = Factory::getApplication();
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $document = Factory::getDocument();
        $db = sportsmanagementHelper::getDBConnection();
        $config['dbo'] = sportsmanagementHelper::getDBConnection();
        $model = BaseDatabaseModel::getInstance('jlxmlimport', 'sportsmanagementmodel');
        $data = $model->getData();
        $uploadArray = $app->getUserState($option . 'uploadArray', array());
        // TODO: import timezone
        $value = isset($data['project']->timezone) ? $data['project']->timezone : null;


        // Get the list of time zones from the server.
        $zones = DateTimeZone::listIdentifiers();

        $projectid = $jinput->getInt('project_id', 0);
        $lists['timezone'] = HTMLHelper::_('select.genericList', $zones, 'timezone', 'class="inputbox" ', 'value', 'text', $value);

        $whichfile = $app->getUserState($option . 'whichfile');
        $this->option = $option;
        $this->whichfile = $whichfile;
        $projectidimport = $app->getUserState($option . 'projectidimport');
        $this->projectidimport = $projectidimport;
        $this->uploadArray = $uploadArray;
        $this->starttime = $starttime;
        // diddi
        $this->countries = JSMCountries::getCountryOptions();

        //$this->request_url = $uri->toString();
        $this->xml = $data;
        // diddi
        $mdl = BaseDatabaseModel::getInstance('leagues', 'sportsmanagementModel');
        $this->leagues = $mdl->getLeagues();
        // diddi
        $mdl = BaseDatabaseModel::getInstance('seasons', 'sportsmanagementModel');
        $this->seasons = $mdl->getSeasons();
        // diddi
        $mdl = BaseDatabaseModel::getInstance('sportstypes', 'sportsmanagementModel');
        $this->sportstypes = $mdl->getSportsTypes();

        $this->admins = $model->getUserList(false);
        $this->editors = $model->getUserList(false);
        $this->templates = $model->getTemplateList();
        // diddi
        $mdl = BaseDatabaseModel::getInstance('teams', 'sportsmanagementModel');
        $this->teams = $mdl->getTeamListSelect();
        // diddi
        $mdl = BaseDatabaseModel::getInstance('clubs', 'sportsmanagementModel');
        $this->clubs = $mdl->getClubListSelect();
        // diddi
        $mdl = BaseDatabaseModel::getInstance('eventtypes', 'sportsmanagementModel');
        $this->events = $mdl->getEventList();
        // diddi
        //$mdl = BaseDatabaseModel::getInstance("positions", "sportsmanagementModel",$config);
        $mdl = BaseDatabaseModel::getInstance('positions', 'sportsmanagementModel');
        $this->positions = $mdl->getPositionListSelect();
        $this->parentpositions = $mdl->getParentsPositions();
        // diddi
        $mdl = BaseDatabaseModel::getInstance('playgrounds', 'sportsmanagementModel');
        $this->playgrounds = $mdl->getPlaygroundListSelect();

        $mdl = BaseDatabaseModel::getInstance('jlxmlimport', 'sportsmanagementmodel');
        //$this->jlxmlimport ?
        // diddi
        $mdl = BaseDatabaseModel::getInstance('persons', 'sportsmanagementModel');
        $this->persons = $mdl->getPersonListSelect();
        // diddi
        $mdl = BaseDatabaseModel::getInstance('statistics', 'sportsmanagementModel');
        $this->statistics = $mdl->getStatisticListSelect();

        $this->OldCountries = $model->getCountryByOldid();
        $this->import_version = $model->import_version;
        $this->lists = $lists;

        $this->show_debug_info = ComponentHelper::getParams($option)->get('show_debug_info', 0);

        $myoptions[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP'));
        $mdlagegroup = BaseDatabaseModel::getInstance('agegroups', 'sportsmanagementModel');
        if ($res = $mdlagegroup->getAgeGroups('', 0)) {
            $myoptions = array_merge($myoptions, $res);
            $this->search_agegroup = $res;
        }
        $lists['agegroup'] = $myoptions;
        $lists['agegroup2'] = JHtmlSelect::genericlist($myoptions, 'filter_search_agegroup', 'class="inputbox" style="width:140px; " onchange="this.form.submit();"', 'value', 'text', $this->state->get('filter.search_agegroup'));
        unset($myoptions);

        $this->lists = $lists;

        //	// Get a refrence of the page instance in joomla
//		$document	= Factory::getDocument();
//        // Set toolbar items for the page
//        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
//        $document->addCustomTag($stylelink);
        // Set toolbar items for the page
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TITLE_2_3');
        $this->icon = 'xmlimport';

        ToolbarHelper::custom('jlxmlimport.insert', 'upload', 'upload', Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_START_BUTTON'), false); // --> bij clicken op import wordt de insert view geactiveerd
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=cpanel');

        //parent::addToolbar();

        $document->addScript(JURI::root(true) . '/administrator/components/com_sportsmanagement/assets/js/sm_functions.js');
        $js = "registerproject('" . JURI::base() . "','" . $projectid . "','" . $app->getCfg('sitename') . "','1');" . "\n";
        $document->addScriptDeclaration($js);

        $this->setLayout('form');

        //parent::display($tpl);
    }

    /**
     * sportsmanagementViewJLXMLImports::_displayInfo()
     * 
     * @param mixed $tpl
     * @return void
     */
    private function _displayInfo($tpl) {
        $app = Factory::getApplication();
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $mtime = microtime();
        $mtime = explode(" ", $mtime);
        $mtime = $mtime[1] + $mtime[0];
        $starttime = $mtime;
        $config['dbo'] = sportsmanagementHelper::getDBConnection();
        $model = BaseDatabaseModel::getInstance('jlxmlimport', 'sportsmanagementmodel', $config);

        $data2 = $jinput->post->getArray(array());
        // Set toolbar items for the page
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TITLE_3_3');
        $this->icon = 'xmlimport';



        $this->starttime = $starttime;

        $this->importData = $model->importData($data2);
        $this->postData = $data2;

//		$this->importData	= $model->importData($post);
//		$this->postData	= $post;
        $this->option = $option;

        ToolbarHelper::divider();
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=projects');

        $this->setLayout('info');

        //parent::addToolbar();
        //parent::display($tpl);
    }

    /**
     * sportsmanagementViewJLXMLImports::_displaySelectpage()
     * 
     * @param mixed $tpl
     * @return void
     */
    private function _displaySelectpage($tpl) {
        $app = Factory::getApplication();
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $document = Factory::getDocument();
        $db = sportsmanagementHelper::getDBConnection();
        $uri = Factory::getURI();
        $model = BaseDatabaseModel::getInstance('JLXMLImport', 'sportsmanagementmodel');
        $lists = array();

        $this->request_url = $uri->toString();
        $this->selectType = $app->getUserState($option . 'selectType');
        $this->recordID = $app->getUserState($option . 'recordID');
        $this->option = $option;

        switch ($this->selectType) {
            case '10': { // Select new Club
                    $this->clubs = $model->getNewClubListSelect();
                    $clublist = array();
                    $clublist[] = HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_CLUB'));
                    $clublist = array_merge($clublist, $this->clubs);
                    $lists['clubs'] = HTMLHelper::_('select.genericlist', $clublist, 'clubID', 'class="inputbox select-club" onchange="javascript:insertNewClub(\'' . $this->recordID . '\')" ', 'value', 'text', 0);
                    unset($clubteamlist);
                }
                break;
            case '9': { // Select Club & Team
                    $this->clubsteams = $model->getClubAndTeamListSelect();
                    $clubteamlist = array();
                    $clubteamlist[] = HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_CLUB_AND_TEAM'));
                    $clubteamlist = array_merge($clubteamlist, $this->clubsteams);
                    $lists['clubsteams'] = HTMLHelper::_('select.genericlist', $clubteamlist, 'teamID', 'class="inputbox select-team" onchange="javascript:insertClubAndTeam(\'' . $this->recordID . '\')" ', 'value', 'text', 0);
                    unset($clubteamlist);
                }
                break;
            case '8': { // Select Statistics
                    $mdl = BaseDatabaseModel::getInstance('statistics', 'sportsmanagementModel');
                    $this->statistics = $mdl->getStatisticListSelect();
                    $statisticlist = array();
                    $statisticlist[] = HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_STATISTIC'));
                    $statisticlist = array_merge($statisticlist, $this->statistics);
                    $lists['statistics'] = HTMLHelper::_('select.genericlist', $statisticlist, 'statisticID', 'class="inputbox select-statistic" onchange="javascript:insertStatistic(\'' . $this->recordID . '\')" ');
                    unset($statisticlist);
                }
                break;

            case '7': { // Select ParentPosition
                    $mdl = BaseDatabaseModel::getInstance('positions', 'sportsmanagementModel');
                    $this->parentpositions = $mdl->getParentsPositions();
                    $parentpositionlist = array();
                    $parentpositionlist[] = HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_PARENT_POSITION'));
                    $parentpositionlist = array_merge($parentpositionlist, $this->parentpositions);
                    $lists['parentpositions'] = HTMLHelper::_('select.genericlist', $parentpositionlist, 'parentPositionID', 'class="inputbox select-parentposition" onchange="javascript:insertParentPosition(\'' . $this->recordID . '\')" ');
                    unset($parentpositionlist);
                }
                break;

            case '6': { // Select Position
                    $mdl = BaseDatabaseModel::getInstance('positions', 'sportsmanagementModel');
                    $this->positions = $mdl->getPositionListSelect();
                    $positionlist = array();
                    $positionlist[] = HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_POSITION'));
                    $positionlist = array_merge($positionlist, $this->positions);
                    $lists['positions'] = HTMLHelper::_('select.genericlist', $positionlist, 'positionID', 'class="inputbox select-position" onchange="javascript:insertPosition(\'' . $this->recordID . '\')" ');
                    unset($positionlist);
                }
                break;

            case '5': { // Select Event
                    $mdl = BaseDatabaseModel::getInstance('eventtypes', 'sportsmanagementModel');
                    $this->events = $mdl->getEventList();
                    $eventlist = array();
                    $eventlist[] = HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_EVENT'));
                    $eventlist = array_merge($eventlist, $this->events);
                    $lists['events'] = HTMLHelper::_('select.genericlist', $eventlist, 'eventID', 'class="inputbox select-event" onchange="javascript:insertEvent(\'' . $this->recordID . '\')" ');
                    unset($eventlist);
                }
                break;

            case '4': { // Select Playground
                    $mdl = BaseDatabaseModel::getInstance('playgrounds', 'sportsmanagementModel');
                    $this->playgrounds = $mdl->getPlaygroundListSelect();
                    $playgroundlist = array();
                    $playgroundlist[] = HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_PLAYGROUND'));
                    $playgroundlist = array_merge($playgroundlist, $this->playgrounds);
                    $lists['playgrounds'] = HTMLHelper::_('select.genericlist', $playgroundlist, 'playgroundID', 'class="inputbox select-playground" onchange="javascript:insertPlayground(\'' . $this->recordID . '\')" ');
                    unset($playgroundlist);
                }
                break;

            case '3': { // Select Person
                    $mdl = BaseDatabaseModel::getInstance('persons', 'sportsmanagementModel');
                    $this->persons = $mdl->getPersonListSelect();
                    $personlist = array();
                    $personlist[] = HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_PERSON'));
                    $personlist = array_merge($personlist, $this->persons);
                    $lists['persons'] = HTMLHelper::_('select.genericlist', $personlist, 'personID', 'class="inputbox select-person" onchange="javascript:insertPerson(\'' . $this->recordID . '\')" ');
                    unset($personlist);
                }
                break;

            case '2': { // Select Club
                    $mdl = BaseDatabaseModel::getInstance('clubs', 'sportsmanagementModel');
                    $this->clubs = $mdl->getClubListSelect();
                    $clublist = array();
                    $clublist[] = HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_CLUB'));
                    $clublist = array_merge($clublist, $this->clubs);
                    $lists['clubs'] = HTMLHelper::_('select.genericlist', $clublist, 'clubID', 'class="inputbox select-club" onchange="javascript:insertClub(\'' . $this->recordID . '\')" ');
                    unset($clublist);
                }
                break;

            case '1':
            default: { // Select Team
                    $mdl = BaseDatabaseModel::getInstance('teams', 'sportsmanagementModel');
                    $this->teams = $mdl->getTeamListSelect();
                    $mdl = BaseDatabaseModel::getInstance('clubs', 'sportsmanagementModel');
                    $this->clubs = $mdl->getClubListSelect();
                    $teamlist = array();
                    $teamlist[] = HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_TEAM'));
                    $teamlist = array_merge($teamlist, $this->teams);
                    $lists['teams'] = HTMLHelper::_('select.genericlist', $teamlist, 'teamID', 'class="inputbox select-team" onchange="javascript:insertTeam(\'' . $this->recordID . '\')" ', 'value', 'text', 0);
                    unset($teamlist);
                }
                break;
        }

        $this->lists = $lists;
        // Set page title
        $pageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ASSIGN_TITLE');
        $document->setTitle($pageTitle);

        $this->setLayout('selectpage');

        //parent::display($tpl);
    }

}

?>
