<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_rssfeed.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filter\OutputFilter;

/**
 * sportsmanagementModeldatabasetool
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementModeldatabasetool extends JSMModelLegacy {

    var $_sport_types_events = array();
    var $_sport_types_position = array();
    var $_sport_types_position_parent = array();
    var $_success_text = '';
    var $my_text = '';
    var $storeFailedColor = 'red';
    var $storeSuccessColor = 'green';
    var $existingInDbColor = 'orange';
    static $db_num_rows = 0;
    static $jsmtables = null;
    static $bar_value = 0;

    /**
     * sportsmanagementModeldatabasetool::runJoomlaQuery()
     * 
     * @param string $setModelVar
     * @return
     */
    public static function runJoomlaQuery($setModelVar = '', $db = NULL) {
        $result = false;

        if (!$db) {
            $db = Factory::getDbo();
        }
        //$this->app->enqueueMessage(Text::_('Ihre Joomla Version = '.JVERSION.''),'Notice');
        //$this->app->enqueueMessage(Text::_('Ihre PHP Version = '.PHP_VERSION .''),'Notice');

        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            try {
            $result = $db->execute();
            } catch (Exception $e) {
                            //$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
                        }
            if (!empty($setModelVar)) {
                $setModelVar::$db_num_rows = $db->getAffectedRows();
            }
        } else {
            try {
            $result = $db->query();
            } catch (Exception $e) {
                            //$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
                        }
            if (!empty($setModelVar)) {
                $setModelVar::$db_num_rows = $db->getAffectedRows();
            }
        }
        return $result;
    }

    /**
     * Method to get the record form.
     *
     * @param	array	$data		Data for the form.
     * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
     * @return	mixed	A JForm object on success, false on failure
     * @since	1.6
     */
    public function getForm($data = array(), $loadData = true) {
        $cfg_which_media_tool = ComponentHelper::getParams($this->jsmoption)->get('cfg_which_media_tool', 0);
        // Get the form.
        $form = $this->loadForm('com_sportsmanagement.databasetool', 'databasetool', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * sportsmanagementModeldatabasetool::getMemory()
     * 
     * @param mixed $startmemory
     * @param mixed $endmemory
     * @return
     */
    function getMemory($startmemory, $endmemory) {
        $memory = array();
        $temp = new stdClass();
        $temp->name = 'start';
        $temp->memory = $startmemory;
        $memory[] = $temp;
        $temp = new stdClass();
        $temp->name = 'ende';
        $temp->memory = $endmemory;
        $memory[] = $temp;
        $temp = new stdClass();
        $temp->name = 'verbrauch';
        $temp->memory = $endmemory - $startmemory;
        $memory[] = $temp;
        return $memory;
    }

    /**
     * sportsmanagementModeldatabasetool::getRunTime()
     * 
     * @return
     */
    public static function getRunTime() {
        $mtime = microtime();
        $mtime = explode(" ", $mtime);
        $mtime = $mtime[1] + $mtime[0];
        return $mtime;
    }

    /**
     * sportsmanagementModeldatabasetool::getQueryTime()
     * 
     * @param mixed $starttime
     * @param mixed $endtime
     * @return
     */
    function getQueryTime($starttime, $endtime) {
        $starttime = explode(" ", $starttime);
        $endtime = explode(" ", $endtime);
        return round($endtime[0] - $starttime[0] + $endtime[1] - $starttime[1], 3);
    }

    /**
     * sportsmanagementModeldatabasetool::getSportsManagementTables()
     * 
     * @return
     */
    function getSportsManagementTables() {
        $prefix = $this->jsmapp->getCfg('dbprefix');
        $result = $this->jsmdb->getTableList();

        foreach ($result as $key => $value) {
            if (preg_match("/sportsmanagement/i", $value) && preg_match("/" . $prefix . "/i", $value)) {
                self::$jsmtables[] = $value;
            }
        }

        return self::$jsmtables;
    }

    /**
     * sportsmanagementModeldatabasetool::getJoomleagueTables()
     * 
     * @return
     */
    function getJoomleagueTablesTruncate() {
        $prefix = $this->jsmapp->getCfg('dbprefix');
        $result = $this->jsmdb->getTableList();
        foreach ($result as $key => $value) {
            if (preg_match("/joomleague/i", $value) && preg_match("/" . $prefix . "/i", $value)) {
                self::$jsmtables[] = $value;
            }
        }

        return self::$jsmtables;
    }

    /**
     * sportsmanagementModeldatabasetool::checkImportTablesJlJsm()
     * 
     * @param mixed $tables
     * @return void
     */
    function checkImportTablesJlJsm($tables) {
        $prefix = $this->jsmapp->getCfg('dbprefix');
        $storeFailedColor = 'red';
        $storeSuccessColor = 'green';
        $existingInDbColor = 'orange';
        $exporttable = array();
        $convert = array(
            'joomleague' => 'sportsmanagement'
        );
        $convert2 = array(
            $prefix . 'joomleague_' => ''
        );

        $count = 1;
        foreach ($tables as $key => $value) {
            $jsmtable = str_replace(array_keys($convert), array_values($convert), $value->name);
            $this->jsmquery = "SHOW TABLES LIKE '%" . $jsmtable . "%'";
            $this->jsmdb->setQuery($this->jsmquery);

            if (version_compare(JVERSION, '3.0.0', 'ge')) {
                /**
                 * Joomla! 3.0 code here
                 */
                $result = $this->jsmdb->loadColumn();
            } elseif (version_compare(JVERSION, '2.5.0', 'ge')) {
                /**
                 * Joomla! 2.5 code here
                 */
                $result = $this->jsmdb->loadResultArray();
            }


            if ($result) {
                $temptable = new stdClass();
                $temptable->id = $value->id;
                $temptable->jl = $value->name;

                $check_table = str_replace(array_keys($convert2), array_values($convert2), $value->name);

                switch ($check_table) {

                    case 'project_team':
                    case 'team_player':
                    case 'team_staff':
                        $temptable->info = Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_JL_NEW_STRUCTUR');
                        $temptable->color = $existingInDbColor;
                        break;
                    case 'match':
                    case 'club':
                    case 'league':
                    case 'person':
                    case 'playground':
                    case 'project':
                    case 'round':
                    case 'season':
                    case 'team':
                    case 'match_commentary':
                    case 'match_player':
                    case 'match_statistic':
                    case 'prediction_groups':
                    case 'prediction_member':
                    case 'template_config':
                        $temptable->info = Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_JL_OK');
                        $temptable->color = $storeSuccessColor;
                        break;
                    default:

                        $temptable->info = Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_JL_NOT_IMPORT');
                        $temptable->color = $storeFailedColor;
                        break;
                }
                $temptable->import = $value->import;
                $temptable->import_data = $value->import_data;
                $temptable->checked_out = $value->checked_out;
                $temptable->jsm = $jsmtable;
                $exporttable[] = $temptable;
                $count++;
            }
        }

        return $exporttable;
    }

    /**
     * sportsmanagementModeldatabasetool::getJoomleagueImportTables()
     * 
     * @return void
     */
    function getJoomleagueImportTables() {
        $this->jsmquery->select('*');
        $this->jsmquery->from('#__sportsmanagement_jl_tables');
        $this->jsmdb->setQuery($this->jsmquery);
        $result = $this->jsmdb->loadObjectList();
        return $result;
    }

    /**
     * sportsmanagementModeldatabasetool::getJoomleagueTables()
     * 
     * @return
     */
    function getJoomleagueTables() {
        $this->jsmquery = $this->jsmdb->getQuery(true);
        $this->jsmquery = "SHOW TABLES LIKE '%_joomleague%'";
        $this->jsmdb->setQuery($this->jsmquery);

        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            /**
             * Joomla! 3.0 code here
             */
            $result = $this->jsmdb->loadColumn();
        } elseif (version_compare(JVERSION, '2.5.0', 'ge')) {
            /**
             * Joomla! 2.5 code here
             */
            $result = $this->jsmdb->loadResultArray();
        }

        if ($result) {
            foreach ($result as $key => $value) {
                $this->jsmquery = $this->jsmdb->getQuery(true);
                $this->jsmquery->select('id');
                // From table
                $this->jsmquery->from('#__sportsmanagement_jl_tables');
                $this->jsmquery->where('name LIKE ' . $this->jsmdb->Quote('' . $value . ''));
                $this->jsmdb->setQuery($this->jsmquery);
                $record_jl = $this->jsmdb->loadResult();

                if ($record_jl) {
                    
                } else {
                    $temp = new stdClass();
                    $temp->name = $value;
                    $temp->import = 0;
                    $temp->import_data = 0;
                    // Insert the object into the table.
                    $resultinsert = $this->jsmdb->insertObject('#__sportsmanagement_jl_tables', $temp);
                    if ($resultinsert) {
                        
                    } else {
                        
                    }
                }
            }
        }
        return $result;
    }

    /**
     * sportsmanagementModeldatabasetool::setParamstoJSON()
     * 
     * @return void
     */
    function setParamstoJSON() {
        $this->jsmquery = $this->jsmdb->getQuery(true);
        $this->jsmquery->select('template,params');
// From table
        $this->jsmquery->from('#__sportsmanagement_template_config');
        $this->jsmquery->where('params not LIKE ' . $this->jsmdb->Quote('' . ''));
        $this->jsmquery->where('import_id != 0');
        $this->jsmquery->group('template');
        $this->jsmdb->setQuery($this->jsmquery);
        $record_jl = $this->jsmdb->loadObjectList();
        $defaultpath = JPATH_COMPONENT_SITE .DIRECTORY_SEPARATOR. 'settings' .DIRECTORY_SEPARATOR. 'default';

        foreach ($record_jl as $row) {
            $defaultvalues = array();
            $defaultvalues = explode('\n', $row->params);
            $parameter = new Registry;

            if (version_compare(JVERSION, '3.0.0', 'ge')) {
                $ini = $parameter->loadString($defaultvalues[0]);
            } else {
                $ini = $parameter->loadINI($defaultvalues[0]);
            }

            /**
             * beim import kann es vorkommen, das wir in der neuen komponente
             * zusätzliche felder haben, die mit abgespeichert werden müssen
             */
            $xmlfile = $defaultpath .DIRECTORY_SEPARATOR. $row->template . '.xml';

            if (file_exists($xmlfile)) {
                $newparams = array();
                $xml = Factory::getXML($xmlfile, true);
                foreach ($xml->fieldset as $paramGroup) {
                    foreach ($paramGroup->field as $param) {
                        $newparams[(string) $param->attributes()->name] = (string) $param->attributes()->default;
                    }
                }

                foreach ($newparams as $key => $value) {
                    if (version_compare(JVERSION, '3.0.0', 'ge')) {
                        $value = $ini->get($key);
                    } else {
//$value = $ini->getValue($key);
                    }
                    if (isset($value)) {
                        $newparams[$key] = $value;
                    }
                }
                $t_params = json_encode($newparams);
            } else {
                $ini = $parameter->toArray($ini);
                $t_params = json_encode($ini);
            }

            $this->jsmquery = $this->jsmdb->getQuery(true);
            // Fields to update.
            $fields = array(
                $this->jsmdb->quoteName('params') . ' = ' . $this->jsmdb->Quote('' . $t_params . '')
            );
// Conditions for which records should be updated.
            $conditions = array(
                $this->jsmdb->quoteName('template') . ' LIKE ' . $this->jsmdb->Quote('' . $row->template . '')
            );
            $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_template_config'))->set($fields)->where($conditions);
            $this->jsmdb->setQuery($this->jsmquery);
            self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        }
    }

    /**
     * sportsmanagementModeldatabasetool::setNewComponentName()
     * 
     * @return void
     */
    function setNewComponentName() {
        $this->jsmquery = $this->jsmdb->getQuery(true);
        // Fields to update.
        $fields = array(
            $this->jsmdb->quoteName('title') . " = replace(title, 'COM_JOOMLEAGUE', 'COM_SPORTSMANAGEMENT') "
        );
// Conditions for which records should be updated.
        $conditions = array(
            $this->jsmdb->quoteName('title') . ' LIKE ' . $this->jsmdb->Quote('%' . 'COM_JOOMLEAGUE' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_template_config'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert (template_config).'), 'Notice');


        $this->jsmquery = $this->jsmdb->getQuery(true);
        // Fields to update.
        $fields = array(
            $this->jsmdb->quoteName('extended') . " = replace(extended, 'COM_JOOMLEAGUE', 'COM_SPORTSMANAGEMENT') "
        );
// Conditions for which records should be updated.
        $conditions = array(
            $this->jsmdb->quoteName('extended') . ' LIKE ' . $this->jsmdb->Quote('%' . 'COM_JOOMLEAGUE' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_rosterposition'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert (rosterposition).'), 'Notice');

        $this->jsmquery = $this->jsmdb->getQuery(true);
// Fields to update.
        $fields = array(
            $this->jsmdb->quoteName('extended') . " = replace(extended, 'JL_EXT', 'COM_SPORTSMANAGEMENT_EXT') "
        );
// Conditions for which records should be updated.
        $conditions = array(
            $this->jsmdb->quoteName('extended') . ' LIKE ' . $this->jsmdb->Quote('%' . 'JL_EXT' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_rosterposition'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert (rosterposition).'), 'Notice');


        $this->jsmquery = $this->jsmdb->getQuery(true);
// Fields to update.
        $fields = array(
            $this->jsmdb->quoteName('name') . " = replace(name, 'COM_JOOMLEAGUE', 'COM_SPORTSMANAGEMENT') "
        );
// Conditions for which records should be updated.
        $conditions = array(
            $this->jsmdb->quoteName('name') . ' LIKE ' . $this->jsmdb->Quote('%' . 'COM_JOOMLEAGUE' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_eventtype'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert (eventtype).'), 'Notice');
    }

    /**
     * sportsmanagementModeldatabasetool::setNewPicturePath()
     * 
     * @return void
     */
    function setNewPicturePath() {
        $this->jsmquery = $this->jsmdb->getQuery(true);
        // Fields to update.
        $fields = array(
            $this->jsmdb->quoteName('picture') . " = replace(picture, 'com_joomleague', 'com_sportsmanagement') "
        );
// Conditions for which records should be updated.
        $conditions = array(
            $this->jsmdb->quoteName('picture') . ' LIKE ' . $this->jsmdb->Quote('%' . 'com_joomleague' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_person'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');
        $this->jsmquery = $this->jsmdb->getQuery(true);
// Fields to update.
        $fields = array(
            $this->jsmdb->quoteName('picture') . " = replace(picture, 'com_joomleague', 'com_sportsmanagement') "
        );
// Conditions for which records should be updated.
        $conditions = array(
            $this->jsmdb->quoteName('picture') . ' LIKE ' . $this->jsmdb->Quote('%' . 'com_joomleague' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_playground'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');

        $this->jsmquery = $this->jsmdb->getQuery(true);
// Fields to update.
        $fields = array(
            $this->jsmdb->quoteName('picture') . " = replace(picture, 'com_joomleague', 'com_sportsmanagement') "
        );
// Conditions for which records should be updated.
        $conditions = array(
            $this->jsmdb->quoteName('picture') . ' LIKE ' . $this->jsmdb->Quote('%' . 'com_joomleague' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_team'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');
        $this->jsmquery = $this->jsmdb->getQuery(true);
// Fields to update.
        $fields = array(
            $this->jsmdb->quoteName('logo_big') . " = replace(logo_big, 'com_joomleague', 'com_sportsmanagement') "
        );
// Conditions for which records should be updated.
        $conditions = array(
            $this->jsmdb->quoteName('logo_big') . ' LIKE ' . $this->jsmdb->Quote('%' . 'com_joomleague' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_club'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');
        $this->jsmquery = $this->jsmdb->getQuery(true);
// Fields to update.
        $fields = array(
            $this->jsmdb->quoteName('logo_middle') . " = replace(logo_middle, 'com_joomleague', 'com_sportsmanagement') "
        );
// Conditions for which records should be updated.
        $conditions = array(
            $this->jsmdb->quoteName('logo_middle') . ' LIKE ' . $this->jsmdb->Quote('%' . 'com_joomleague' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_club'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');
        $this->jsmquery = $this->jsmdb->getQuery(true);
// Fields to update.
        $fields = array(
            $this->jsmdb->quoteName('logo_small') . " = replace(logo_small, 'com_joomleague', 'com_sportsmanagement') "
        );
// Conditions for which records should be updated.
        $conditions = array(
            $this->jsmdb->quoteName('logo_small') . ' LIKE ' . $this->jsmdb->Quote('%' . 'com_joomleague' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_club'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');
        $this->jsmquery = $this->jsmdb->getQuery(true);
// Fields to update.
        $fields = array(
            $this->jsmdb->quoteName('picture') . " = replace(picture, 'com_joomleague', 'com_sportsmanagement') "
        );
// Conditions for which records should be updated.
        $conditions = array(
            $this->jsmdb->quoteName('picture') . ' LIKE ' . $this->jsmdb->Quote('%' . 'com_joomleague' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_associations'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');

        $this->jsmquery = $this->jsmdb->getQuery(true);
// Fields to update.
        $fields = array(
            $this->jsmdb->quoteName('assocflag') . " = replace(assocflag, 'com_joomleague', 'com_sportsmanagement') "
        );
// Conditions for which records should be updated.
        $conditions = array(
            $this->jsmdb->quoteName('assocflag') . ' LIKE ' . $this->jsmdb->Quote('%' . 'com_joomleague' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_associations'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');

        $this->jsmquery = $this->jsmdb->getQuery(true);
// Fields to update.
        $fields = array(
            $this->jsmdb->quoteName('icon') . " = replace(icon, 'com_joomleague', 'com_sportsmanagement') "
        );
// Conditions for which records should be updated.
        $conditions = array(
            $this->jsmdb->quoteName('icon') . ' LIKE ' . $this->jsmdb->Quote('%' . 'com_joomleague' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_eventtype'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');

        $this->jsmquery = $this->jsmdb->getQuery(true);
// Fields to update.
        $fields = array(
            $this->jsmdb->quoteName('picture') . " = replace(picture, 'com_joomleague', 'com_sportsmanagement') "
        );
// Conditions for which records should be updated.
        $conditions = array(
            $this->jsmdb->quoteName('picture') . ' LIKE ' . $this->jsmdb->Quote('%' . 'com_joomleague' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_project_team'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');

        $this->jsmquery = $this->jsmdb->getQuery(true);
// Fields to update.
        $fields = array(
            $this->jsmdb->quoteName('trikot_home') . " = replace(trikot_home, 'com_joomleague', 'com_sportsmanagement') "
        );
// Conditions for which records should be updated.
        $conditions = array(
            $this->jsmdb->quoteName('trikot_home') . ' LIKE ' . $this->jsmdb->Quote('%' . 'com_joomleague' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_project_team'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');

        $this->jsmquery = $this->jsmdb->getQuery(true);
// Fields to update.
        $fields = array(
            $this->jsmdb->quoteName('trikot_away') . " = replace(trikot_away, 'com_joomleague', 'com_sportsmanagement') "
        );
// Conditions for which records should be updated.
        $conditions = array(
            $this->jsmdb->quoteName('trikot_away') . ' LIKE ' . $this->jsmdb->Quote('%' . 'com_joomleague' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_project_team'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');

        $this->jsmquery = $this->jsmdb->getQuery(true);
// Fields to update.
        $fields = array(
            $this->jsmdb->quoteName('picture') . " = replace(picture, 'com_joomleague', 'com_sportsmanagement') "
        );
// Conditions for which records should be updated.
        $conditions = array(
            $this->jsmdb->quoteName('picture') . ' LIKE ' . $this->jsmdb->Quote('%' . 'com_joomleague' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_season_team_person_id'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');

        $this->jsmquery = $this->jsmdb->getQuery(true);
// Fields to update.
        $fields = array(
            $this->jsmdb->quoteName('picture') . " = replace(picture, 'com_joomleague', 'com_sportsmanagement') "
        );
// Conditions for which records should be updated.
        $conditions = array(
            $this->jsmdb->quoteName('picture') . ' LIKE ' . $this->jsmdb->Quote('%' . 'com_joomleague' . '%')
        );
        $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_season_person_id'))->set($fields)->where($conditions);
        $this->jsmdb->setQuery($this->jsmquery);
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(Text::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');
    }

    /**
     * sportsmanagementModeldatabasetool::setSportsManagementTableQuery()
     * 
     * @param mixed $table
     * @param mixed $command
     * @return
     */
    function setSportsManagementTableQuery($table, $command) {

        $this->jsmquery = strtoupper($command) . ' TABLE `' . $table . '`';
        $this->jsmdb->setQuery($this->jsmquery);
        if (!self::runJoomlaQuery(__CLASS__, $this->jsmdb)) {
            return false;
        }


        return true;
    }

    /**
     * sportsmanagementModeldatabasetool::checkQuotes()
     * 
     * @param mixed $sm_quotes
     * @return
     */
    function checkQuotes($sm_quotes) {

        foreach ($sm_quotes as $key => $type) {
            $temp = explode(",", $type);
            $this->jsmquery = $this->jsmdb->getQuery(true);
            // Select some fields
            $this->jsmquery->select('count(*) AS count');
            // From the table
            $this->jsmquery->from('#__sportsmanagement_rquote');
            $this->jsmquery->where('daily_number = ' . $temp[1]);

            $this->jsmdb->setQuery($this->jsmquery);
            // sind zitate vorhanden ?
            if (!$this->jsmdb->loadResult()) {
                /* Ein JDatabaseQuery Objekt beziehen */
                $this->jsmquery = $this->jsmdb->getQuery(true);
                $this->jsmquery->delete()->from('#__sportsmanagement_rquote')->where('daily_number = ' . $temp[1] . '');
                $this->jsmdb->setQuery($this->jsmquery);
                $result = self::runJoomlaQuery(__CLASS__, $this->jsmdb);

                // joomla versionen
                if (version_compare(JVERSION, '3.0.0', 'ge')) {
                    $xml = simplexml_load_file(JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/helpers/xml_files/quote_' . $temp[0] . '.xml');
                    $document = 'version';
                    $quotes = 'children()';
                } else {
                    $xml = Factory::getXML(JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/helpers/xml_files/quote_' . $temp[0] . '.xml');
                    $document = 'version';
                    $quotes = 'children()';
                }

                $quote_version = (string) $xml->$document;

                $this->my_text .= '<span style="color:' . $this->storeSuccessColor . '"><strong>';
                $this->my_text .= Text::_('Installiere Zitate') . '</strong></span><br />';
                $this->my_text .= Text::_('Zitate ' . $temp[0] . ' Version : ' . $quote_version . ' wird installiert !') . '<br />';

                if ($xml) {
                    foreach ($xml->children() as $quote) {
                        $author = '';
                        $notes = '';
                        $daily_number = '';
                        $zitat = '';
                        $attr = $quote->quote->attributes();
                        if (isset($quote->quote)) {
                            if (isset($attr['author'])) {
                                $author = str_replace("\\", "\\\\", (string) $quote->quote->attributes()->author);
                            }
                            if (isset($attr['notes'])) {
                                $notes = (string) $quote->quote->attributes()->notes;
                            }
                            if (isset($attr['daily_number'])) {
                                $daily_number = (string) $quote->quote->attributes()->daily_number;
                            }
                        }
                        if (isset($quote->quote)) {
                            $zitat = (string) $quote->quote;
                        }

                        if ($zitat) {
                            $insertquery = $this->jsmdb->getQuery(true);
                            // Insert columns.
                            $columns = array('daily_number', 'author', 'quote', 'notes');
                            // Insert values.
                            $values = array('\'' . $temp[1] . '\'', '\'' . $author . '\'', '\'' . $zitat . '\'', '\'' . $notes . '\'');
                            // Prepare the insert query.
                            $insertquery
                                    ->insert($this->jsmdb->quoteName('#__sportsmanagement_rquote'))
                                    ->columns($this->jsmdb->quoteName($columns))
                                    ->values(implode(',', $values));
                            // Set the query using our newly populated query object and execute it.
                            $this->jsmdb->setQuery($insertquery);

                            if (!self::runJoomlaQuery(__CLASS__, $this->jsmdb)) {
                                self::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->jsmdb->getErrorMsg(), __LINE__);
                            } else {
                                
                            }
                        }
                    }
                }
            } else {

                // joomla versionen
                if (version_compare(JVERSION, '3.0.0', 'ge')) {
                    $xml = simplexml_load_file(JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/helpers/xml_files/quote_' . $temp[0] . '.xml');
                    //$xml = Factory::getXML(JPATH_ADMINISTRATOR.'/components/'.$this->jsmoption.'/helpers/xml_files/quote_'.$temp[0].'.xml'); 
                    $document = 'version';
                } else {
                    $xml = Factory::getXML(JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/helpers/xml_files/quote_' . $temp[0] . '.xml');
                    $document = 'version';
                }

                $quote_version = (string) $xml->$document;

                $this->my_text .= '<span style="color:' . $this->existingInDbColor . '"><strong>';
                $this->my_text .= Text::_('Installierte Zitate') . '</strong></span><br />';
                $this->my_text .= Text::_('Zitate ' . $temp[0] . ' Version : ' . $quote_version . ' ist installiert !') . '<br />';
            }
        }
        return $this->my_text;
    }

    /**
     * sportsmanagementModeldatabasetool::insertAgegroup()
     * 
     * @param mixed $search_nation
     * @param mixed $filter_sports_type
     * @return
     */
    function insertAgegroup($search_nation, $filter_sports_type) {
        $app = Factory::getApplication();

        $mdl = BaseDatabaseModel::getInstance("sportstype", "sportsmanagementModel");
        $p_sportstype = $mdl->getTable();
        $p_sportstype->load((int) $filter_sports_type);
        $temp = explode("_", $p_sportstype->name);
        $sport_type_name = strtolower(array_pop($temp));

        $filename = JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/helpers/xml_files/' . 'agegroup_' . strtolower($search_nation) . '_' . $sport_type_name . '.xml';

        if (!File::exists($filename)) {
            $this->my_text = '<span style="color:' . $this->storeFailedColor . '"><strong>';
            $this->my_text .= Text::_('Fehlende Altersgruppen') . '</strong></span><br />';
            $this->my_text .= Text::sprintf('Die Datei %1$s ist nicht vorhanden!', 'agegroup_' . strtolower($search_nation) . '_' . $sport_type_name . '.xml') . '<br />';
            return $this->my_text;
        } else {
            $this->my_text = '<span style="color:' . $this->existingInDbColor . '"><strong>';
            $this->my_text .= Text::_('Installierte Altersgruppen') . '</strong></span><br />';
            $this->my_text .= Text::sprintf('Die Datei %1$s ist vorhanden!', 'agegroup_' . strtolower($search_nation) . '_' . $sport_type_name . '.xml') . '<br />';

            if (version_compare(JVERSION, '3.0.0', 'ge')) {
                $xml = simplexml_load_file($filename);
            } else {
                $xml = Factory::getXML($filename);
            }
            if ($xml) {

                // schleife altersgruppen anfang
                foreach ($xml->agegroups as $agegroups) {
                    $agegroup = (string) $agegroups->agegroup;
                    $info = (string) $agegroups->agegroup->attributes()->info;
                    $picture = 'images/com_sportsmanagement/database/agegroups/' . (string) $agegroups->agegroup->attributes()->picture;

                    $this->jsmquery = $this->jsmdb->getQuery(true);
                    // Select some fields
                    $this->jsmquery->select('id');
                    // From the table
                    $this->jsmquery->from('#__sportsmanagement_agegroup');
                    $this->jsmquery->where('name LIKE ' . $this->jsmdb->Quote('' . addslashes(stripslashes($agegroup)) . ''));
                    $this->jsmquery->where('country LIKE ' . $this->jsmdb->Quote('' . addslashes(stripslashes($search_nation)) . ''));
                    $this->jsmquery->where('sportstype_id = ' . $filter_sports_type);
                    $this->jsmdb->setQuery($this->jsmquery);
                    // altersgruppe nicht vorhanden ?
                    if (!$this->jsmdb->loadResult()) {
                        // Create a new query object.
                        $this->jsmquery = $this->jsmdb->getQuery(true);
                        // Insert columns.
                        $columns = array('name', 'picture', 'info', 'sportstype_id', 'country');
                        // Insert values.
                        $values = array('\'' . $agegroup . '\'', '\'' . $picture . '\'', '\'' . $info . '\'', '\'' . $filter_sports_type . '\'', '\'' . $search_nation . '\'');
                        // Prepare the insert query.
                        $this->jsmquery
                                ->insert($this->jsmdb->quoteName('#__sportsmanagement_agegroup'))
                                ->columns($this->jsmdb->quoteName($columns))
                                ->values(implode(',', $values));
                        // Set the query using our newly populated query object and execute it.
                        $this->jsmdb->setQuery($this->jsmquery);

                        try {
                            self::runJoomlaQuery();
                            $this->my_text .= '<span style="color:' . $this->storeSuccessColor . '"><strong>';
                            $this->my_text .= Text::_('Installierte Altersgruppen') . '</strong></span><br />';
                            $this->my_text .= Text::sprintf('Die Altersgruppe %1$s wurde angelegt!!', $agegroup) . '<br />';
                        } catch (Exception $e) {
                            $app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
                        }
                        
                    }
                }
                // schleife altersgruppen ende    
            }


            return $this->my_text;
        }
    }

    /**
     * sportsmanagementModeldatabasetool::checkAssociations()
     * 
     * @return
     */
    function checkAssociations() {
        $country_assoc_del = '';

        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            $xml = simplexml_load_file(JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/helpers/xml_files/associations.xml');
            $document = 'associations';
        } else {
            $xml = Factory::getXML(JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/helpers/xml_files/associations.xml');
            $document = 'associations';
        }


        if (!File::exists(JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/helpers/xml_files/associations.xml')) {
            return false;
        }

        $params = ComponentHelper::getParams($this->jsmoption);
        $country_assoc = $params->get('cfg_country_associations');
        if ($country_assoc) {
            $country_assoc_del = "'" . implode("','", $country_assoc) . "'";
        }

        /** Ein JDatabaseQuery Objekt beziehen **/
        if ($country_assoc_del) {
            $this->jsmquery = $this->jsmdb->getQuery(true);
            $this->jsmquery->delete()->from('#__sportsmanagement_associations')->where('country NOT IN (' . $country_assoc_del . ')');
            $this->jsmdb->setQuery($this->jsmquery);
            $result = self::runJoomlaQuery();
        }

        $image_path = 'images/' . $this->jsmoption . '/database/associations/';

        /** schleife */
        foreach ($xml->$document as $association) {
            $country = (string) $association->assocname->attributes()->country;

            if ($country_assoc) {
                /** welche länder möchte denn der user haben ? **/
                foreach ($country_assoc as $key => $value) {
                    if ($value == $country) {
                        $main = (string) $association->assocname->attributes()->main;
                        $parentmain = (string) $association->assocname->attributes()->parentmain;
                        $icon = $image_path . (string) $association->assocname->attributes()->icon;
                        $flag = (string) $association->assocname->attributes()->flag;
                        $website = (string) $association->assocname->attributes()->website;
                        $shortname = (string) $association->assocname->attributes()->shortname;
                        $assocname = (string) $association->assocname;
                        $middlename = $assocname;
                        $aliasname = OutputFilter::stringURLSafe( $assocname );
                        if ( !$shortname )
                        {
                        $shortname = $assocname;    
                        }
                        

                        $this->jsmquery = $this->jsmdb->getQuery(true);
                        /** Select some fields  */
                        $this->jsmquery->select('id');
                        /** From the table */
                        $this->jsmquery->from('#__sportsmanagement_associations');
                        $this->jsmquery->where('country LIKE ' . $this->jsmdb->Quote('' . addslashes(stripslashes($country)) . ''));
                        $this->jsmquery->where('name LIKE ' . $this->jsmdb->Quote('' . addslashes(stripslashes($assocname)) . ''));
                        $this->jsmdb->setQuery($this->jsmquery);
                        $result = $this->jsmdb->loadResult();

                        $export = array();
                        if (!$result) {
                            /** landesverband nicht gefunden */
                            if (empty($parentmain)) {
                                /** Create a new query object. */
                                $insertquery = $this->jsmdb->getQuery(true);
                                /** Insert columns. */
                                $columns = array('country', 'name', 'picture', 'assocflag', 'website', 'short_name', 'middle_name', 'alias');
                                /** Insert values. */
                                $values = array('\'' . $country . '\'', '\'' . $assocname . '\'', '\'' . $icon . '\'', '\'' . $flag . '\'', '\'' . $website . '\'', '\'' . $shortname . '\'', '\'' . $middlename . '\'', '\'' . $aliasname . '\'');
                                /** Prepare the insert query. */
                                $insertquery
                                        ->insert($this->jsmdb->quoteName('#__sportsmanagement_associations'))
                                        ->columns($this->jsmdb->quoteName($columns))
                                        ->values(implode(',', $values));
                                /** Set the query using our newly populated query object and execute it. */
                                $this->jsmdb->setQuery($insertquery);

                                if (!self::runJoomlaQuery()) {
                                    self::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->jsmdb->getErrorMsg(), __LINE__);
                                } else {
                                    $temp = new stdClass();
                                    $temp->id = $this->jsmdb->insertid();
                                    $export[] = $temp;
                                    $this->_assoclist[$country][$main] = array_merge($export);
                                }
                            } else {
                                $parent_id = $this->_assoclist[$country][$parentmain];
                                /** Create a new query object. */
                                $insertquery = $this->jsmdb->getQuery(true);
                                /** Insert columns. */
                                $columns = array('country', 'name', 'parent_id', 'picture', 'assocflag', 'website', 'short_name', 'middle_name', 'alias');
                                /** Insert values. */
                                $values = array('\'' . $country . '\'', '\'' . $assocname . '\'', $parent_id[0]->id, '\'' . $icon . '\'', '\'' . $flag . '\'', '\'' . $website . '\'', '\'' . $shortname . '\'', '\'' . $middlename . '\'', '\'' . $aliasname . '\'');
                                /** Prepare the insert query. */
                                $insertquery
                                        ->insert($this->jsmdb->quoteName('#__sportsmanagement_associations'))
                                        ->columns($this->jsmdb->quoteName($columns))
                                        ->values(implode(',', $values));
                                /** Set the query using our newly populated query object and execute it. */
                                $this->jsmdb->setQuery($insertquery);

                                if (!self::runJoomlaQuery()) {
                                    self::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->jsmdb->getErrorMsg(), __LINE__);
                                } else {
                                    $temp = new stdClass();
                                    $temp->id = $this->jsmdb->insertid();
                                    $export[] = $temp;
                                    $this->_assoclist[$country][$main] = array_merge($export);
                                }
                            }
                        } else {
                            /** landesverband gefunden */
                            $temp = new stdClass();
                            $temp->id = $result;
                            $export[] = $temp;
                            $this->_assoclist[$country][$main] = array_merge($export);
                            if ( $parentmain )
                            {
                                if ( array_key_exists($parentmain,$this->_assoclist[$country]) )
                                {
                            $parent_id = $this->_assoclist[$country][$parentmain];
                                }
                                else
                                {
                                    $parent_id = 0;
                                }
                            }
                            else
                            {
                                $parent_id = 0;
                            }

                            /** Fields to update. */
                            $this->jsmquery = $this->jsmdb->getQuery(true);
                            $fields = array(
                                $this->jsmdb->quoteName('picture') . '=' . '\'' . $icon . '\'',
                                $this->jsmdb->quoteName('short_name') . '=' . '\'' . $shortname . '\'',
                                $this->jsmdb->quoteName('middle_name') . '=' . '\'' . $middlename . '\'',
                                $this->jsmdb->quoteName('alias') . '=' . '\'' . $aliasname . '\''

                            );
                            /** Conditions for which records should be updated. */
                            $conditions = array(
                                $this->jsmdb->quoteName('id') . '=' . $result
                            );
                            $this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_associations'))->set($fields)->where($conditions);
                            $this->jsmdb->setQuery($this->jsmquery);
                            $result = self::runJoomlaQuery();
                        }
                    }
                }
            }
        }
 
    }

    /**
     * sportsmanagementModeldatabasetool::checkSportTypeStructur()
     * 
     * @param mixed $type
     * @return
     */
    function checkSportTypeStructur($type) {
        $app = Factory::getApplication();
        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            $xml = simplexml_load_file(JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/helpers/sp_structur/' . $type . '.xml');
        } else {
            $xml = Factory::getXML(JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/helpers/sp_structur/' . $type . '.xml');
        }

        if (!File::exists(JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/helpers/sp_structur/' . $type . '.xml')) {
            return false;
        }
        // We can now step through each element of the file
        if (isset($xml->events)) {
            foreach ($xml->events as $event) {
                $temp = new stdClass();
                $temp->name = strtoupper($this->jsmoption) . '_' . strtoupper($type) . '_E_' . strtoupper((string) $event->name);
                $temp->icon = 'images/' . $this->jsmoption . '/database/events/' . $type . '/' . strtolower((string) $event->name->attributes()->icon);
                $export[] = $temp;
                $this->_sport_types_events[$type] = array_merge($export);
            }
        }
        unset($export);

        if (isset($xml->mainpositions)) {
            foreach ($xml->mainpositions as $position) {
                $temp = new stdClass();
                $temp->name = strtoupper($this->jsmoption) . '_' . strtoupper($type) . '_F_' . strtoupper((string) $position->mainname);
                $temp->switch = strtolower((string) $position->mainname->attributes()->switch);
                $temp->parent = (string) $position->mainname->attributes()->parent;
                $temp->content = (string) $position->mainname->attributes()->content;
                $export[] = $temp;
                $this->_sport_types_position[$type] = array_merge($export);
            }
        }
        unset($export);
        if (isset($xml->parentpositions)) {
            foreach ($xml->parentpositions as $parent) {
                $temp = new stdClass();
                $temp->name = strtoupper($this->jsmoption) . '_' . strtoupper($type) . '_' . strtoupper((string) $parent->parentname->attributes()->art) . '_' . strtoupper((string) $parent->parentname);
                $temp->switch = strtolower((string) $parent->parentname->attributes()->switch);
                $temp->parent = (string) $parent->parentname->attributes()->parent;
                $temp->content = (string) $parent->parentname->attributes()->content;
                $temp->events = (string) $parent->parentname->attributes()->events;
                $export[] = $temp;
                $this->_sport_types_position_parent[strtoupper($this->jsmoption) . '_' . strtoupper($type) . '_F_' . strtoupper((string) $parent->parentname->attributes()->main)] = array_merge($export);
            }
        }

        return true;
    }

    /**
     * sportsmanagementModeldatabasetool::insertCountries()
     * 
     * @return
     */
    function insertCountries() {
        $app = Factory::getApplication();
        require_once( JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/' . 'helpers' .DIRECTORY_SEPARATOR. 'jinstallationhelper.php' );
        $db = sportsmanagementHelper::getDBConnection();
        $db_table = JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/sql/countries.sql';
        $cols = $this->jsmdb->getTableColumns('#__sportsmanagement_countries');
        if ($cols) {
            $result = JInstallationHelper::populateDatabase($db, $db_table, $errors);
            if ($result) {
                $this->my_text = '<span style="color:' . $this->storeFailedColor . '"><strong>';
                $this->my_text .= Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNTRIES_INSERT_ERROR') . '</strong></span><br />';
                return $this->my_text;
            } else {
                $this->my_text = '<span style="color:' . $this->storeSuccessColor . '"><strong>';
                $this->my_text .= Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNTRIES_INSERT_SUCCESS') . '</strong></span><br />';
                return $this->my_text;
            }
        } else {
            $this->my_text = '<span style="color:' . $this->storeFailedColor . '"><strong>';
            $this->my_text .= Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_JOOMLEAGUE_COUNTRIES_INSERT_ERROR') . '</strong></span><br />';
            return $this->my_text;
        }
    }

    /**
     * sportsmanagementModeldatabasetool::insertSportType()
     * 
     * @param mixed $type
     * @return
     */
    function insertSportType($type) {
        $app = Factory::getApplication();
        $sports_type_id = 0;
        $available = self::checkSportTypeStructur($type);

        $install_standard_position = ComponentHelper::getParams($this->jsmoption)->get('install_standard_position', 0);

        if (!$available) {
            $this->my_text = '<span style="color:' . $this->storeFailedColor . '"><strong>';
            $this->my_text .= Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPE_INSERT_XML_ERROR', strtoupper($type)) . '</strong></span><br />';
            return false;
        }

        $this->jsmquery = "SELECT id FROM #__sportsmanagement_sports_type" . " WHERE name='" . "COM_SPORTSMANAGEMENT_ST_" . strtoupper($type) . "' ";
        $this->jsmdb->setQuery($this->jsmquery);
        $result = $this->jsmdb->loadResult();
        $sports_type_id = $result;
        if ($result) {
            /**
             * nur wenn in den optionen ja eingestellt ist, werden die positionen installiert
             */
            if ($install_standard_position) {
                $sports_type_id = $result;
                $sports_type_name = 'COM_SPORTSMANAGEMENT_ST_' . strtoupper($type);
                self::addStandardForSportType($sports_type_name, $sports_type_id, $type, $update = 1);
            }
        } else {
            // Create a new query object.
            $this->jsmquery = $this->jsmdb->getQuery(true);
            // Insert columns.
            $columns = array('name', 'icon');
            // Insert values.
            $values = array('\'' . 'COM_SPORTSMANAGEMENT_ST_' . strtoupper($type) . '\'', '\'' . 'images/com_sportsmanagement/database/placeholders/placeholder_21.png' . '\'');
            // Prepare the insert query.
            $this->jsmquery
                    ->insert($this->jsmdb->quoteName('#__sportsmanagement_sports_type'))
                    ->columns($this->jsmdb->quoteName($columns))
                    ->values(implode(',', $values));
            // Set the query using our newly populated query object and execute it.
            $this->jsmdb->setQuery($this->jsmquery);

            if (!$this->jsmdb->execute()) {
                $result = false;
            } else {
                $this->my_text .= '<span style="color:' . $this->storeSuccessColor . '"><strong>';
                $this->my_text .= Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPE_INSERT_SUCCESS', strtoupper($type)) . '</strong></span><br />';
                $sports_type_id = $this->jsmdb->insertid();
                $sports_type_name = 'COM_SPORTSMANAGEMENT_ST_' . strtoupper($type);
                /**
                 * nur wenn in den optionen ja eingestellt ist, werden die positionen installiert
                 */
                if ($install_standard_position) {
                    self::addStandardForSportType($sports_type_name, $sports_type_id, $type, $update = 0);
                }
            }
        }

        return $sports_type_id;
    }

    /**
     * sportsmanagementModeldatabasetool::addStandardForSportType()
     * 
     * @param mixed $name
     * @param mixed $id
     * @param mixed $type
     * @param integer $update
     * @return void
     */
    function addStandardForSportType($name, $id, $type, $update = 0) {
        $app = Factory::getApplication();
        $this->option = Factory::getApplication()->input->getCmd('option');
        $events_player = array();
        $events_staff = array();
        $events_referees = array();
        $events_clubstaff = array();
        $PlayersPositions = array();
        $StaffPositions = array();
        $RefereePositions = array();
        $ClubStaffPositions = array();

        $result = false;

        // insert events
        $i = 0;
        if (isset($this->_sport_types_events[$type])) {
            foreach ($this->_sport_types_events[$type] as $event) {
                $this->jsmquery = self::build_SelectQuery('eventtype', $event->name, $id);
                $this->jsmdb->setQuery($this->jsmquery);

                if (!$object = $this->jsmdb->loadObject()) {
                    $this->jsmquery = self::build_InsertQuery_Event('eventtype', $event->name, $event->icon, $id, 2);
                    $this->jsmdb->setQuery($this->jsmquery);
                    $result = $this->jsmdb->execute();
                    $events_player[$i] = $this->jsmdb->insertid();
                    $events_staff[$i] = $this->jsmdb->insertid();
                    $events_clubstaff[$i] = $this->jsmdb->insertid();
                    $events_referees[$i] = $this->jsmdb->insertid();
                    $event->tableid = $this->jsmdb->insertid();
                } else {
                    $events_player[$i] = $object->id;
                    $events_staff[$i] = $object->id;
                    $events_clubstaff[$i] = $object->id;
                    $events_referees[$i] = $object->id;
                    $event->tableid = $object->id;
                }

                if (!$update) {
                    $this->my_text .= '<span style="color:' . $this->storeSuccessColor . '"><strong>';
                    $this->my_text .= Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_EVENTS_INSERT_SUCCESS', $event->name) . '</strong></span><br />';
                }
                $i++;
            }
        }
        // standardpositionen einf�gen
        $i = 0;
        $j = 0;
        if (isset($this->_sport_types_position[$type])) {
            foreach ($this->_sport_types_position[$type] as $position) {
                $this->jsmquery = self::build_SelectQuery('position', $position->name, $id);
                $this->jsmdb->setQuery($this->jsmquery);
                if (!$dbresult = $this->jsmdb->loadObject()) {
                    $this->jsmquery = self::build_InsertQuery_Position('position', $position->name, $position->switch, $position->parent, $position->content, $id, 1);
                    $this->jsmdb->setQuery($this->jsmquery);
                    $result = $this->jsmdb->execute();
                    $ParentID = $this->jsmdb->insertid();
                    $PlayersPositions[$i] = $this->jsmdb->insertid();
                } else {
                    $ParentID = $dbresult->id;
                    $PlayersPositions[$i] = $dbresult->id;
                }

                if (!$update) {
                    $this->my_text .= '<span style="color:' . $this->storeSuccessColor . '"><strong>';
                    $this->my_text .= Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_POSITION_INSERT_SUCCESS', $position->name) . '</strong></span><br />';
                }

                // parent position
                if (isset($this->_sport_types_position_parent[$position->name])) {
                    foreach ($this->_sport_types_position_parent[$position->name] as $parent) {
                        $this->jsmquery = self::build_SelectQuery('position', $parent->name, $id);
                        $this->jsmdb->setQuery($this->jsmquery);
                        if (!$object = $this->jsmdb->loadObject()) {
                            $this->jsmquery = self::build_InsertQuery_Position('position', $parent->name, $parent->switch, $ParentID, $parent->content, $id, 2);
                            $this->jsmdb->setQuery($this->jsmquery);
                            $result = self::runJoomlaQuery();
                            $PlayersPositions[$j] = $this->jsmdb->insertid();
                            $parent->tableid = $this->jsmdb->insertid();
                        } else {
                            $PlayersPositions[$j] = $object->id;
                            $parent->tableid = $object->id;
                        }

                        if ($parent->events) {
                        }

                        $j++;

                        if (!$update) {
                            $this->my_text .= '<span style="color:' . $this->storeSuccessColor . '"><strong>';
                            $this->my_text .= Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_PARENT_POSITION_INSERT_SUCCESS', $parent->name) . '</strong></span><br />';
                        }
                    }
                }




                $i++;
            }
        }
    }

    /**
     * sportsmanagementModeldatabasetool::build_SelectQuery()
     * 
     * @param mixed $tablename
     * @param mixed $param1
     * @param integer $st_id
     * @return
     */
    function build_SelectQuery($tablename, $param1, $st_id = 0) {
        $this->jsmquery = "SELECT * FROM #__sportsmanagement_" . $tablename . " WHERE name='" . $param1 . "' and sports_type_id = " . $st_id . " ";
        return $this->jsmquery;
    }

    /**
     * sportsmanagementModeldatabasetool::build_InsertQuery_Position()
     * 
     * @param mixed $tablename
     * @param mixed $param1
     * @param mixed $param2
     * @param mixed $param3
     * @param mixed $param4
     * @param mixed $sports_type_id
     * @param mixed $order_count
     * @return
     */
    function build_InsertQuery_Position($tablename, $param1, $param2, $param3, $param4, $sports_type_id, $order_count) {
        // Get a db connection.
        $db = Factory::getDbo();
        /**
         * feld import_id einfügen
         */
        try {
            $this->jsmquery = $this->jsmdb->getQuery(true);
            $this->jsmquery = "ALTER TABLE `#__sportsmanagement_" . $tablename . "` ADD `import_id` INT(11) NOT NULL DEFAULT '0' ";
            $this->jsmdb->setQuery($this->jsmquery);
            sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
        } catch (Exception $e) {

        }

        $count_id = 0;

        $alias = OutputFilter::stringURLSafe($param1);
        $this->jsmquery = "INSERT INTO #__sportsmanagement_" . $tablename . " (`name`,`alias`,`" . $param2 . "`,`parent_id`,`sports_type_id`,`published`,`ordering`,`import_id`) VALUES ('" . $param1 . "','" . $alias . "','" . $param4 . "','" . $param3 . "','" . $sports_type_id . "','1','" . $order_count . "','" . $count_id . "')";
        return $this->jsmquery;
    }

    /**
     * sportsmanagementModeldatabasetool::build_InsertQuery_Event()
     * 
     * @param mixed $tablename
     * @param mixed $param1
     * @param mixed $param2
     * @param mixed $sports_type_id
     * @param mixed $order_count
     * @return
     */
    function build_InsertQuery_Event($tablename, $param1, $param2, $sports_type_id, $order_count) {
        // Get a db connection.
        $db = Factory::getDbo();
        /**
         * feld import_id einfügen
         */
        try {
            $this->jsmquery = $this->jsmdb->getQuery(true);
            $this->jsmquery = "ALTER TABLE `#__sportsmanagement_" . $tablename . "` ADD `import_id` INT(11) NOT NULL DEFAULT '0' ";
            $this->jsmdb->setQuery($this->jsmquery);
            sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
        } catch (Exception $e) {

        }

        $count_id = 0;
        $alias = OutputFilter::stringURLSafe($param1);
        $this->jsmquery = "INSERT INTO #__sportsmanagement_" . $tablename . " (`name`,`alias`,`icon`,`sports_type_id`,`published`,`ordering`,`import_id`) VALUES ('" . $param1 . "','" . $alias . "','" . $param2 . "','" . $sports_type_id . "','1','" . $order_count . "','" . $count_id . "')";
        return $this->jsmquery;
    }

    /**
     * sportsmanagementModeldatabasetool::build_InsertQuery_PositionEventType()
     * 
     * @param mixed $param1
     * @param mixed $param2
     * @return
     */
    function build_InsertQuery_PositionEventType($param1, $param2) {
        // Get a db connection.
        $db = Factory::getDbo();
        /**
         * feld import_id einfügen
         */
        try {
            $this->jsmquery = $this->jsmdb->getQuery(true);
            $this->jsmquery = "ALTER TABLE `#__sportsmanagement_position_eventtype` ADD `import_id` INT(11) NOT NULL DEFAULT '0' ";
            $this->jsmdb->setQuery($this->jsmquery);
            sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
        } catch (Exception $e) {

        }

        $count_id = 0;
        $this->jsmquery = "INSERT INTO	#__sportsmanagement_position_eventtype (`position_id`,`eventtype_id`,`import_id`) VALUES ('" . $param1 . "','" . $param2 . "','" . $count_id . "')";
        return $this->jsmquery;
    }

    /**
     * sportsmanagementModeldatabasetool::writeErrorLog()
     * 
     * @param mixed $class
     * @param mixed $function
     * @param mixed $file
     * @param mixed $text
     * @param mixed $line
     * @return void
     */
    public static function writeErrorLog($class, $function, $file, $text, $line) {

    }

}
