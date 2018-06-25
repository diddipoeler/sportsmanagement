<?php

/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_rssfeed.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

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
            $db = JFactory::getDbo();
        }
        //$this->app->enqueueMessage(JText::_('Ihre Joomla Version = '.JVERSION.''),'Notice');
        //$this->app->enqueueMessage(JText::_('Ihre PHP Version = '.PHP_VERSION .''),'Notice');

        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            $result = $db->execute();
            if (!empty($setModelVar)) {
                $setModelVar::$db_num_rows = $db->getAffectedRows();
            }
        } else {
            $result = $db->query();
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
        $cfg_which_media_tool = JComponentHelper::getParams($this->jsmoption)->get('cfg_which_media_tool', 0);
        //$this->jsmapp->enqueueMessage(JText::_('sportsmanagementModelagegroup getForm cfg_which_media_tool<br><pre>'.print_r($cfg_which_media_tool,true).'</pre>'),'Notice');
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
        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' prefix <br><pre>'.print_r($prefix,true).'</pre>'),'');
        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getTableList <br><pre>'.print_r($result,true).'</pre>'),'');

        foreach ($result as $key => $value) {
            if (preg_match("/sportsmanagement/i", $value) && preg_match("/" . $prefix . "/i", $value)) {
                self::$jsmtables[] = $value;
            }
        }

        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsmtables <br><pre>'.print_r($jsmtables,true).'</pre>'),'');

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
        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' prefix <br><pre>'.print_r($prefix,true).'</pre>'),'');
        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getTableList <br><pre>'.print_r($result,true).'</pre>'),'');

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

        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' <br><pre>'.print_r($tables,true).'</pre>'),'');
        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' prefix' .  ' <br><pre>'.print_r($prefix,true).'</pre>'),'');
        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' convert2' .  ' <br><pre>'.print_r($convert2,true).'</pre>'),'');

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

                //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' check_table' .  ' <br><pre>'.print_r($check_table,true).'</pre>'),'');

                switch ($check_table) {

                    case 'project_team':
                    case 'team_player':
                    case 'team_staff':
                        $temptable->info = JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_JL_NEW_STRUCTUR');
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
                        $temptable->info = JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_JL_OK');
                        $temptable->color = $storeSuccessColor;
                        break;
                    default:

                        $temptable->info = JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_JL_NOT_IMPORT');
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

        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($result,true).'</pre>'),'');

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
                    // Create and populate an object.
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
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' params <br><pre>'.print_r($record_jl,true).'</pre>'),'');        

        $defaultpath = JPATH_COMPONENT_SITE . DS . 'settings' . DS . 'default';
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' defaultpath <br><pre>'.print_r($defaultpath,true).'</pre>'),'');

        foreach ($record_jl as $row) {
            $defaultvalues = array();
            $defaultvalues = explode('\n', $row->params);
            $parameter = new JRegistry;

            if (version_compare(JVERSION, '3.0.0', 'ge')) {
                $ini = $parameter->loadString($defaultvalues[0]);
            } else {
                $ini = $parameter->loadINI($defaultvalues[0]);
            }

//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' ini <br><pre>'.print_r($ini,true).'</pre>'),'');

            /**
             * beim import kann es vorkommen, das wir in der neuen komponente
             * zusätzliche felder haben, die mit abgespeichert werden müssen
             */
            $xmlfile = $defaultpath . DS . $row->template . '.xml';

            if (file_exists($xmlfile)) {
                $newparams = array();
                $xml = JFactory::getXML($xmlfile, true);
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
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' template <br><pre>'.print_r($row->template,true).'</pre>'),'Error');    
                $ini = $parameter->toArray($ini);
                $t_params = json_encode($ini);
            }

//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' template <br><pre>'.print_r($row->template,true).'</pre>'),'');
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' t_params <br><pre>'.print_r($t_params,true).'</pre>'),'');

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
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');    
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
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');    
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(JText::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert (template_config).'), 'Notice');


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
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');    
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(JText::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert (rosterposition).'), 'Notice');

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
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');    
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(JText::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert (rosterposition).'), 'Notice');


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
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');    
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(JText::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert (eventtype).'), 'Notice');
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
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');    
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(JText::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');

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
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');    
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(JText::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');

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
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');    
//$this->jsmapp->enqueueMessage(JText::_(__CLASS__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(JText::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');


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
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');    
//$this->jsmapp->enqueueMessage(JText::_(__CLASS__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(JText::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');

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
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');    
//$this->jsmapp->enqueueMessage(JText::_(__CLASS__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(JText::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');

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
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');    
//$this->jsmapp->enqueueMessage(JText::_(__CLASS__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(JText::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');


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
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');    
//$this->jsmapp->enqueueMessage(JText::_(__CLASS__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(JText::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');

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
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');    
//$this->jsmapp->enqueueMessage(JText::_(__CLASS__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(JText::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');

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
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');    
//$this->jsmapp->enqueueMessage(JText::_(__CLASS__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(JText::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');

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
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');    
//$this->jsmapp->enqueueMessage(JText::_(__CLASS__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(JText::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');

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
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');    
//$this->jsmapp->enqueueMessage(JText::_(__CLASS__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(JText::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');

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
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');    
//$this->jsmapp->enqueueMessage(JText::_(__CLASS__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(JText::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');

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
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');    
//$this->jsmapp->enqueueMessage(JText::_(__CLASS__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(JText::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');

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
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');    
//$this->jsmapp->enqueueMessage(JText::_(__CLASS__.' '.__LINE__.' query->dump<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'');
        self::runJoomlaQuery(__CLASS__, $this->jsmdb);
        $this->jsmapp->enqueueMessage(JText::_('Wir haben ' . self::$db_num_rows . ' Datensätze aktualisiert.'), 'Notice');
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
            $this->jsmapp->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . '<br><pre>' . print_r($this->jsmdb->getErrorMsg(), true) . '</pre>'), 'Error');
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
                    $xml = JFactory::getXML(JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/helpers/xml_files/quote_' . $temp[0] . '.xml');
                    $document = 'version';
                    $quotes = 'children()';
                }

                $quote_version = (string) $xml->$document;

                $this->my_text .= '<span style="color:' . $this->storeSuccessColor . '"><strong>';
                $this->my_text .= JText::_('Installiere Zitate') . '</strong></span><br />';
                $this->my_text .= JText::_('Zitate ' . $temp[0] . ' Version : ' . $quote_version . ' wird installiert !') . '<br />';

                //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' quotes<br><pre>'.print_r($xml->children(),true).'</pre>'),'Notice');
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

                        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' quote<br><pre>'.print_r($quote,true).'</pre>'),'Notice');
                        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' author<br><pre>'.print_r($author,true).'</pre>'),'Notice');
                        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' notes<br><pre>'.print_r($notes,true).'</pre>'),'Notice');
                        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' daily_number<br><pre>'.print_r($daily_number,true).'</pre>'),'Notice');
                        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' zitat<br><pre>'.print_r($zitat,true).'</pre>'),'Notice');

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
                    //$xml = JFactory::getXML(JPATH_ADMINISTRATOR.'/components/'.$this->jsmoption.'/helpers/xml_files/quote_'.$temp[0].'.xml'); 
                    $document = 'version';
                } else {
                    $xml = JFactory::getXML(JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/helpers/xml_files/quote_' . $temp[0] . '.xml');
                    $document = 'version';
                }

                $quote_version = (string) $xml->$document;

                $this->my_text .= '<span style="color:' . $this->existingInDbColor . '"><strong>';
                $this->my_text .= JText::_('Installierte Zitate') . '</strong></span><br />';
                $this->my_text .= JText::_('Zitate ' . $temp[0] . ' Version : ' . $quote_version . ' ist installiert !') . '<br />';
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
        $app = JFactory::getApplication();

        //$this->jsmapp->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($search_nation,true).'</pre>'),'Notice');
        //$this->jsmapp->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($filter_sports_type,true).'</pre>'),'Notice');

        $mdl = JModelLegacy::getInstance("sportstype", "sportsmanagementModel");
        $p_sportstype = $mdl->getTable();
        $p_sportstype->load((int) $filter_sports_type);
        $temp = explode("_", $p_sportstype->name);
        $sport_type_name = strtolower(array_pop($temp));

        //$this->jsmapp->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($sport_type_name,true).'</pre>'),'Notice');
        $filename = JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/helpers/xml_files/' . 'agegroup_' . strtolower($search_nation) . '_' . $sport_type_name . '.xml';

        if (!JFile::exists($filename)) {
            //$this->jsmapp->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($filename,true).'</pre>'),'Error');
            $this->my_text = '<span style="color:' . $this->storeFailedColor . '"><strong>';
            $this->my_text .= JText::_('Fehlende Altersgruppen') . '</strong></span><br />';
            $this->my_text .= JText::sprintf('Die Datei %1$s ist nicht vorhanden!', 'agegroup_' . strtolower($search_nation) . '_' . $sport_type_name . '.xml') . '<br />';

            //$this->_success_text['Altersgruppen:'] = $my_text;
            //$this->jsmapp->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($this->my_text,true).'</pre>'),'');
            return $this->my_text;
        } else {
            $this->my_text = '<span style="color:' . $this->existingInDbColor . '"><strong>';
            $this->my_text .= JText::_('Installierte Altersgruppen') . '</strong></span><br />';
            $this->my_text .= JText::sprintf('Die Datei %1$s ist vorhanden!', 'agegroup_' . strtolower($search_nation) . '_' . $sport_type_name . '.xml') . '<br />';

            if (version_compare(JVERSION, '3.0.0', 'ge')) {
                $xml = simplexml_load_file($filename);
            } else {
//                $xml = JFactory::getXMLParser( 'Simple' );
//    $xml->loadFile($filename);
                $xml = JFactory::getXML($filename);
            }
//        $xml = JFactory::getXMLParser( 'Simple' );
//       $xml->loadFile($filename); 

            if ($xml) {

                //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' xml -><br><pre>'.print_r($xml,true).'</pre>'),'');
                // schleife altersgruppen anfang
                foreach ($xml->agegroups as $agegroups) {

//   $name = $agegroup->getElementByPath('agegroup');
//   $attributes = $name->attributes();
                    //$this->jsmapp->enqueueMessage(JText::_(get_class($this).'<br><pre>'.print_r($name->data(),true).'</pre>'),'Notice');

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

//   $this->query="SELECT id
//            FROM #__".COM_SPORTSMANAGEMENT_TABLE."_agegroup where name like '".$agegroup."' and country like '".$search_nation."' and sportstype_id = ".$filter_sports_type;


                    $this->jsmdb->setQuery($this->jsmquery);
                    // altersgruppe nicht vorhanden ?
                    if (!$this->jsmdb->loadResult()) {
//   // Get a db connection.
//        $db = JFactory::getDbo();
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
                            $this->my_text .= JText::_('Installierte Altersgruppen') . '</strong></span><br />';
                            $this->my_text .= JText::sprintf('Die Altersgruppe %1$s wurde angelegt!!', $agegroup) . '<br />';
                        } catch (Exception $e) {
                            $app->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
                        }
                        /*
                          if (!self::runJoomlaQuery())
                          {

                          //$this->jsmapp->enqueueMessage(JText::_(get_class($this).' insertSportType<br><pre>'.print_r($this->jsmdb->getErrorMsg(),true).'</pre>'),'Error');
                          $result = false;
                          }
                          else
                          {
                          //$this->jsmapp->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_AGEGROUP_SUCCESS',$agegroup),'Notice');
                          $this->my_text .= '<span style="color:'.$this->storeSuccessColor.'"><strong>';
                          $this->my_text .= JText::_('Installierte Altersgruppen').'</strong></span><br />';
                          $this->my_text .= JText::sprintf('Die Altersgruppe %1$s wurde angelegt!!',$agegroup).'<br />';
                          }
                         */
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
        //$app = JFactory::getApplication();
//    $this->option = JFactory::getApplication()->input->getCmd('option');    
        /* Ein Datenbankobjekt beziehen */
        //$db = JFactory::getDbo();  

        $country_assoc_del = '';

        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            $xml = simplexml_load_file(JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/helpers/xml_files/associations.xml');
            //$xml = JFactory::getXML(JPATH_ADMINISTRATOR.'/components/'.$this->jsmoption.'/helpers/xml_files/associations.xml'); 
            $document = 'associations';
        } else {
//                $xml = JFactory::getXMLParser( 'Simple' );
//    $xml->loadFile(JPATH_ADMINISTRATOR.'/components/'.$this->jsmoption.'/helpers/xml_files/associations.xml');
            $xml = JFactory::getXML(JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/helpers/xml_files/associations.xml');
            $document = 'associations';
        }


        if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/helpers/xml_files/associations.xml')) {
            return false;
        }

        $params = JComponentHelper::getParams($this->jsmoption);
        $country_assoc = $params->get('cfg_country_associations');
        if ($country_assoc) {
            $country_assoc_del = "'" . implode("','", $country_assoc) . "'";
        }

        //$this->jsmapp->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($country_assoc,true).'</pre>'),'Notice');
        //$this->jsmapp->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($country_assoc_del,true).'</pre>'),'Notice');


        /* Ein JDatabaseQuery Objekt beziehen */
        if ($country_assoc_del) {
            $this->jsmquery = $this->jsmdb->getQuery(true);
            $this->jsmquery->delete()->from('#__sportsmanagement_associations')->where('country NOT IN (' . $country_assoc_del . ')');
            $this->jsmdb->setQuery($this->jsmquery);
            $result = self::runJoomlaQuery();
        }

        //$this->jsmapp->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($this->jsmdb->getErrorMsg(),true).'</pre>'),'');

        $image_path = 'images/' . $this->jsmoption . '/database/associations/';

        // schleife
        foreach ($xml->$document as $association) {
            //$name = $association->getElementByPath('assocname');
            //$attributes = $name->attributes();
            //$country = $attributes['country'];
            $country = (string) $association->assocname->attributes()->country;

            //$this->jsmapp->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($attributes['country'],true).'</pre>'),'Notice');
            //$this->jsmapp->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($name->data(),true).'</pre>'),'Notice');

            if ($country_assoc) {
                // welche l�nder m�chte denn der user haben ?
                foreach ($country_assoc as $key => $value) {
                    if ($value == $country) {
                        //$country = $attributes['country'];
                        $main = (string) $association->assocname->attributes()->main;
                        $parentmain = (string) $association->assocname->attributes()->parentmain;

                        $icon = $image_path . (string) $association->assocname->attributes()->icon;
                        $flag = (string) $association->assocname->attributes()->flag;
                        $website = (string) $association->assocname->attributes()->website;
                        $shortname = (string) $association->assocname->attributes()->shortname;

                        $assocname = (string) $association->assocname;

                        $this->jsmquery = $this->jsmdb->getQuery(true);
                        // Select some fields
                        $this->jsmquery->select('id');
                        // From the table
                        $this->jsmquery->from('#__sportsmanagement_associations');
                        $this->jsmquery->where('country LIKE ' . $this->jsmdb->Quote('' . addslashes(stripslashes($country)) . ''));
                        $this->jsmquery->where('name LIKE ' . $this->jsmdb->Quote('' . addslashes(stripslashes($assocname)) . ''));

//   $this->jsmquery = "SELECT id FROM #__".COM_SPORTSMANAGEMENT_TABLE."_associations WHERE country LIKE '" . $country ."' and name LIKE '".$assocname."'";
                        $this->jsmdb->setQuery($this->jsmquery);
                        $result = $this->jsmdb->loadResult();

                        //$this->jsmapp->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($this->jsmquery,true).'</pre>'),'Notice');

                        $export = array();
                        if (!$result) {
                            if (empty($parentmain)) {
                                // Create a new query object.
                                $insertquery = $this->jsmdb->getQuery(true);
                                // Insert columns.
                                $columns = array('country', 'name', 'picture', 'assocflag', 'website', 'short_name');
                                // Insert values.
                                $values = array('\'' . $country . '\'', '\'' . $assocname . '\'', '\'' . $icon . '\'', '\'' . $flag . '\'', '\'' . $website . '\'', '\'' . $shortname . '\'');
                                // Prepare the insert query.
                                $insertquery
                                        ->insert($this->jsmdb->quoteName('#__sportsmanagement_associations'))
                                        ->columns($this->jsmdb->quoteName($columns))
                                        ->values(implode(',', $values));
                                // Set the query using our newly populated query object and execute it.
                                $this->jsmdb->setQuery($insertquery);

                                if (!self::runJoomlaQuery()) {
                                    //$this->jsmapp->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($this->jsmdb->getErrorMsg(),true).'</pre>'),'Error'); 
                                    self::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->jsmdb->getErrorMsg(), __LINE__);
                                } else {
                                    $temp = new stdClass();
                                    $temp->id = $this->jsmdb->insertid();
                                    $export[] = $temp;
                                    $this->_assoclist[$country][$main] = array_merge($export);
                                }
                            } else {
                                $parent_id = $this->_assoclist[$country][$parentmain];
                                //$this->jsmapp->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($parent_id,true).'</pre>'),'');
                                // Create a new query object.
                                $insertquery = $this->jsmdb->getQuery(true);
                                // Insert columns.
                                $columns = array('country', 'name', 'parent_id', 'picture', 'assocflag', 'website', 'short_name');
                                // Insert values.
                                $values = array('\'' . $country . '\'', '\'' . $assocname . '\'', $parent_id[0]->id, '\'' . $icon . '\'', '\'' . $flag . '\'', '\'' . $website . '\'', '\'' . $shortname . '\'');
                                // Prepare the insert query.
                                $insertquery
                                        ->insert($this->jsmdb->quoteName('#__sportsmanagement_associations'))
                                        ->columns($this->jsmdb->quoteName($columns))
                                        ->values(implode(',', $values));
                                // Set the query using our newly populated query object and execute it.
                                $this->jsmdb->setQuery($insertquery);

                                if (!self::runJoomlaQuery()) {
                                    //$this->jsmapp->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($this->jsmdb->getErrorMsg(),true).'</pre>'),'Error');
                                    self::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->jsmdb->getErrorMsg(), __LINE__);
                                } else {
                                    $temp = new stdClass();
                                    $temp->id = $this->jsmdb->insertid();
                                    $export[] = $temp;
                                    $this->_assoclist[$country][$main] = array_merge($export);
                                }
                            }
                        } else {
                            $temp = new stdClass();
                            $temp->id = $result;
                            $export[] = $temp;
                            $this->_assoclist[$country][$main] = array_merge($export);

                            // Fields to update.
                            $this->jsmquery = $this->jsmdb->getQuery(true);
                            $fields = array(
                                $this->jsmdb->quoteName('picture') . '=' . '\'' . $icon . '\'',
                                $this->jsmdb->quoteName('short_name') . '=' . '\'' . $shortname . '\''
                            );
                            // Conditions for which records should be updated.
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

        //$this->jsmapp->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($this->_assoclist,true).'</pre>'),'');   
    }

    /**
     * sportsmanagementModeldatabasetool::checkSportTypeStructur()
     * 
     * @param mixed $type
     * @return
     */
    function checkSportTypeStructur($type) {
        $app = JFactory::getApplication();
        //$this->option = JFactory::getApplication()->input->getCmd('option');    
        //$db_table = JPATH_ADMINISTRATOR.'/components/'.$this->jsmoption.'/helpers/sp_structur/'.$type.'.txt';    
        //$fileContent = JFile::read($db_table);    
        //$this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur fileContent<br><pre>'.print_r($fileContent,true).'</pre>'),'Notice');
//    $xml = JFactory::getXMLParser( 'Simple' );
//    $xml->loadFile(JPATH_ADMINISTRATOR.'/components/'.$this->jsmoption.'/helpers/sp_structur/'.$type.'.xml');

        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            $xml = simplexml_load_file(JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/helpers/sp_structur/' . $type . '.xml');
        } else {
//                $xml = JFactory::getXMLParser( 'Simple' );
//    $xml->loadFile(JPATH_ADMINISTRATOR.'/components/'.$this->option.'/helpers/sp_structur/'.$type.'.xml');
            $xml = JFactory::getXML(JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/helpers/sp_structur/' . $type . '.xml');
        }

        if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/helpers/sp_structur/' . $type . '.xml')) {
            return false;
        }
        //$xml = JFactory::getXML(JPATH_ADMINISTRATOR.'/components/'.$this->jsmoption.'/helpers/sp_structur/'.$type.'.xml');
        //$this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml<br><pre>'.print_r($xml,true).'</pre>'),'Notice');
        // We can now step through each element of the file
        if (isset($xml->events)) {
            foreach ($xml->events as $event) {
                //$name = $event->getElementByPath('name');
//   $attributes = $name->attributes();
                //$main = (string)$association->assocname->attributes()->main;
                //$icon = $event->getElementByPath('icon');
                //$this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml name<br><pre>'.print_r($name->data(),true).'</pre>'),'Notice');
                //$this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml icon<br><pre>'.print_r($icon->data(),true).'</pre>'),'Notice');

                $temp = new stdClass();
                $temp->name = strtoupper($this->jsmoption) . '_' . strtoupper($type) . '_E_' . strtoupper((string) $event->name);
                $temp->icon = 'images/' . $this->jsmoption . '/database/events/' . $type . '/' . strtolower((string) $event->name->attributes()->icon);
                $export[] = $temp;
                $this->_sport_types_events[$type] = array_merge($export);
            }
        }
        //$this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool createSportTypeArray _sport_types_events<br><pre>'.print_r($this->_sport_types_events,true).'</pre>'),'Notice'); 
        //$this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent mainpositions<br><pre>'.print_r($xml->document->mainpositions,true).'</pre>'),'Notice');

        unset($export);


        if (isset($xml->mainpositions)) {
            foreach ($xml->mainpositions as $position) {
                //$name = $position->getElementByPath('mainname');
//   $attributes = $name->attributes();
                //$this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml mainpositions<br><pre>'.print_r($name,true).'</pre>'),'Notice');
//   $switch = $position->getElementByPath('mainswitch');
//   $parent = $position->getElementByPath('mainparent');
//   $content = $position->getElementByPath('maincontent');
                //$this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml name<br><pre>'.print_r($name->data(),true).'</pre>'),'Notice');
                //$this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml icon<br><pre>'.print_r($icon->data(),true).'</pre>'),'Notice');


                $temp = new stdClass();
                $temp->name = strtoupper($this->jsmoption) . '_' . strtoupper($type) . '_F_' . strtoupper((string) $position->mainname);
                $temp->switch = strtolower((string) $position->mainname->attributes()->switch);
                $temp->parent = (string) $position->mainname->attributes()->parent;
                $temp->content = (string) $position->mainname->attributes()->content;
                $export[] = $temp;
                $this->_sport_types_position[$type] = array_merge($export);
            }
        }
        //$this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool createSportTypeArray _sport_types_position<br><pre>'.print_r($this->_sport_types_position,true).'</pre>'),'Notice');
        //$this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent parentpositions<br><pre>'.print_r($xml->document->parentpositions,true).'</pre>'),'Notice');

        unset($export);
        if (isset($xml->parentpositions)) {
            foreach ($xml->parentpositions as $parent) {
                //$name = $parent->getElementByPath('parentname');
//   $attributes = $name->attributes();
                //$this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent parentname<br><pre>'.print_r($name,true).'</pre>'),'Notice');
                //$this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent attributes<br><pre>'.print_r($name->attributes(),true).'</pre>'),'Notice');
//   $switch = $parent->getElementByPath('parentswitch');
//   $parent = $parent->getElementByPath('parentparent');
//   $content = $parent->getElementByPath('parentcontent');
//   $mainparentposition = $parent->getElementByPath('mainparentposition');
//   $this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent name<br><pre>'.print_r($name->data(),true).'</pre>'),'Notice');
//   $this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent switch<br><pre>'.print_r($switch->data(),true).'</pre>'),'Notice');
//   $this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent parent<br><pre>'.print_r($parent->data(),true).'</pre>'),'Notice');
//   $this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent content<br><pre>'.print_r($content->data(),true).'</pre>'),'Notice');
//   $this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent mainparentposition<br><pre>'.print_r($mainparentposition->data(),true).'</pre>'),'Notice');
//   $this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent name<br><pre>'.print_r($name,true).'</pre>'),'Notice');
//   $this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent switch<br><pre>'.print_r($switch,true).'</pre>'),'Notice');
//   $this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent parent<br><pre>'.print_r($parent,true).'</pre>'),'Notice');
//   $this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent content<br><pre>'.print_r($content,true).'</pre>'),'Notice');
//   $this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool checkSportTypeStructur xml parent mainparentposition<br><pre>'.print_r($mainparentposition,true).'</pre>'),'Notice');


                $temp = new stdClass();
                $temp->name = strtoupper($this->jsmoption) . '_' . strtoupper($type) . '_' . strtoupper((string) $parent->parentname->attributes()->art) . '_' . strtoupper((string) $parent->parentname);
                $temp->switch = strtolower((string) $parent->parentname->attributes()->switch);
                $temp->parent = (string) $parent->parentname->attributes()->parent;
                $temp->content = (string) $parent->parentname->attributes()->content;
                $temp->events = (string) $parent->parentname->attributes()->events;
                //$export = array();
                $export[] = $temp;
                $this->_sport_types_position_parent[strtoupper($this->jsmoption) . '_' . strtoupper($type) . '_F_' . strtoupper((string) $parent->parentname->attributes()->main)] = array_merge($export);
            }
        }





        //$this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool createSportTypeArray _sport_types_position_parent<br><pre>'.print_r($this->_sport_types_position_parent,true).'</pre>'),'Notice');

        return true;
    }

    /**
     * sportsmanagementModeldatabasetool::insertCountries()
     * 
     * @return
     */
    function insertCountries() {
        $app = JFactory::getApplication();
        //$this->option = JFactory::getApplication()->input->getCmd('option');
        require_once( JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/' . 'helpers' . DS . 'jinstallationhelper.php' );
        $db = sportsmanagementHelper::getDBConnection();
        $db_table = JPATH_ADMINISTRATOR . '/components/' . $this->jsmoption . '/sql/countries.sql';
// echo '<br>'.$db_table.'<br>';
// $fileContent = JFile::read($db_table);
// $sql_teil = explode(";",$fileContent);

        $cols = $this->jsmdb->getTableColumns('#__' . COM_SPORTSMANAGEMENT_TABLE . '_countries');
        if ($cols) {
            $result = JInstallationHelper::populateDatabase($db, $db_table, $errors);
            if ($result) {
                //$this->jsmapp->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNTRIES_INSERT_ERROR'),'Error'); 
                $this->my_text = '<span style="color:' . $this->storeFailedColor . '"><strong>';
                $this->my_text .= JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNTRIES_INSERT_ERROR') . '</strong></span><br />';


                //$this->_success_text['Altersgruppen:'] = $my_text;
                //$this->jsmapp->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($this->my_text,true).'</pre>'),'');
                return $this->my_text;
            } else {
                //$this->jsmapp->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNTRIES_INSERT_SUCCESS'),'');
                $this->my_text = '<span style="color:' . $this->storeSuccessColor . '"><strong>';
                $this->my_text .= JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_COUNTRIES_INSERT_SUCCESS') . '</strong></span><br />';


                //$this->_success_text['Altersgruppen:'] = $my_text;
                //$this->jsmapp->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($this->my_text,true).'</pre>'),'');
                return $this->my_text;
            }
        } else {
            $this->my_text = '<span style="color:' . $this->storeFailedColor . '"><strong>';
            $this->my_text .= JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_JOOMLEAGUE_COUNTRIES_INSERT_ERROR') . '</strong></span><br />';
            return $this->my_text;
        }
        //$this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool insertCountries result<br><pre>'.print_r($result,true).'</pre>'),'Notice');    
    }

    /**
     * sportsmanagementModeldatabasetool::insertSportType()
     * 
     * @param mixed $type
     * @return
     */
    function insertSportType($type) {
        $app = JFactory::getApplication();
        //$this->option = JFactory::getApplication()->input->getCmd('option');
        //$db = sportsmanagementHelper::getDBConnection(FALSE,FALSE);
        $sports_type_id = 0;
        //$this->jsmapp->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPE_INSERT',strtoupper($type)),'Notice');
        //self::createSportTypeArray();
        $available = self::checkSportTypeStructur($type);

        $install_standard_position = JComponentHelper::getParams($this->jsmoption)->get('install_standard_position', 0);

        if (!$available) {
            //$this->jsmapp->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPE_INSERT_XML_ERROR',strtoupper($type)),'Error');
            $this->my_text = '<span style="color:' . $this->storeFailedColor . '"><strong>';
            $this->my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPE_INSERT_XML_ERROR', strtoupper($type)) . '</strong></span><br />';
            return false;
        }

        $this->jsmquery = "SELECT id FROM #__sportsmanagement_sports_type" . " WHERE name='" . "COM_SPORTSMANAGEMENT_ST_" . strtoupper($type) . "' ";
        $this->jsmdb->setQuery($this->jsmquery);
        $result = $this->jsmdb->loadResult();
        $sports_type_id = $result;
        if ($result) {
            //$this->jsmapp->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPE_AVAILABLE',strtoupper($type)),'Notice');
            // $this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool insertSportType result<br><pre>'.print_r($result,true).'</pre>'),'Notice'); 
            /**
             * nur wenn in den optionen ja eingestellt ist, werden die positionen installiert
             */
            if ($install_standard_position) {
                $sports_type_id = $result;
                $sports_type_name = 'COM_SPORTSMANAGEMENT_ST_' . strtoupper($type);
                self::addStandardForSportType($sports_type_name, $sports_type_id, $type, $update = 1);
            }
        } else {
            //// Get a db connection.
//        $db = JFactory::getDbo();
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

                $this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool insertSportType<br><pre>' . print_r($this->jsmdb->getErrorMsg(), true) . '</pre>'), 'Error');
                $result = false;
            } else {
                //$this->jsmapp->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPE_INSERT_SUCCESS',strtoupper($type)),'Notice');
                $this->my_text .= '<span style="color:' . $this->storeSuccessColor . '"><strong>';
                $this->my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_SPORT_TYPE_INSERT_SUCCESS', strtoupper($type)) . '</strong></span><br />';

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
        //return $this->my_text;
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
        $app = JFactory::getApplication();
        $this->option = JFactory::getApplication()->input->getCmd('option');
        // Get a db connection.
        //$db = JFactory::getDbo();
        //$this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool addStandardForSportType name<br><pre>'.print_r($name,true).'</pre>'),'Notice');
        //$this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool addStandardForSportType id<br><pre>'.print_r($id,true).'</pre>'),'Notice');
        //$this->jsmapp->enqueueMessage(JText::_('sportsmanagementModeldatabasetool addStandardForSportType events<br><pre>'.print_r($this->_sport_types_events[$type],true).'</pre>'),'Notice');

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
                    //$this->jsmapp->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_EVENTS_INSERT_SUCCESS',$event->name),'Notice');
                    $this->my_text .= '<span style="color:' . $this->storeSuccessColor . '"><strong>';
                    $this->my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_EVENTS_INSERT_SUCCESS', $event->name) . '</strong></span><br />';
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
                    //$this->jsmapp->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_POSITION_INSERT_SUCCESS',$position->name),'Notice');
                    $this->my_text .= '<span style="color:' . $this->storeSuccessColor . '"><strong>';
                    $this->my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_POSITION_INSERT_SUCCESS', $position->name) . '</strong></span><br />';
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
//                foreach ( $this->_sport_types_events[$type] as $event )
//                {
//                $this->query	= self::build_InsertQuery_PositionEventType($parent->tableid,$event->tableid);
//                JFactory::getDbo()->setQuery($this->query);
//				$result = self::runJoomlaQuery(); 
//                if ( !$result )
//                {
//                
//                }
//                else
//                {    
//                if ( !$update )
//                {
//                if ( $result )
//                {
//                    //$this->jsmapp->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_PARENT_POSITION_INSERT_EVENT_SUCCESS',$event->name),'Notice');
//                    $this->my_text .= '<span style="color:'.$this->storeSuccessColor.'"><strong>';
//		$this->my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_PARENT_POSITION_INSERT_EVENT_SUCCESS',$event->name).'</strong></span><br />';
//                }   
//                else
//                {
//                    //$this->jsmapp->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_PARENT_POSITION_INSERT_EVENT_ERROR',$event->name),'Error');
//                }
//                }
//                }
//                }
                        }

                        $j++;

                        if (!$update) {
                            //$this->jsmapp->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_PARENT_POSITION_INSERT_SUCCESS',$parent->name),'Notice');    
                            $this->my_text .= '<span style="color:' . $this->storeSuccessColor . '"><strong>';
                            $this->my_text .= JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_PARENT_POSITION_INSERT_SUCCESS', $parent->name) . '</strong></span><br />';
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
        $db = JFactory::getDbo();
//    $this->query = $this->jsmdb->getQuery(true);
//    // Select some fields
//    $this->query->select('max(import_id) AS count');
//    // From the table
//    $this->query->from('#__sportsmanagement_'.$tablename);
//	$this->jsmdb->setQuery($this->query);
//	$count_id = $this->jsmdb->loadResult();

        /**
         * feld import_id einfügen
         */
        try {
            $this->jsmquery = $this->jsmdb->getQuery(true);
            $this->jsmquery = "ALTER TABLE `#__sportsmanagement_" . $tablename . "` ADD `import_id` INT(11) NOT NULL DEFAULT '0' ";
            $this->jsmdb->setQuery($this->jsmquery);
            sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
//$result = $this->jsmdb->execute();
        } catch (Exception $e) {
//    // catch any database errors.
//    $this->jsmdb->transactionRollback();
//    JErrorPage::render($e);
        }

        $count_id = 0;

        $alias = JFilterOutput::stringURLSafe($param1);
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
        $db = JFactory::getDbo();
//    $this->query = $this->jsmdb->getQuery(true);
//    // Select some fields
//    $this->query->select('max(import_id) AS count');
//    // From the table
//    $this->query->from('#__sportsmanagement_'.$tablename);
//	$this->jsmdb->setQuery($this->query);
//	$count_id = $this->jsmdb->loadResult();

        /**
         * feld import_id einfügen
         */
        try {
            $this->jsmquery = $this->jsmdb->getQuery(true);
            $this->jsmquery = "ALTER TABLE `#__sportsmanagement_" . $tablename . "` ADD `import_id` INT(11) NOT NULL DEFAULT '0' ";
            $this->jsmdb->setQuery($this->jsmquery);
            sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
//$result = $this->jsmdb->execute();
        } catch (Exception $e) {
//    // catch any database errors.
//    $this->jsmdb->transactionRollback();
//    JErrorPage::render($e);
        }

        $count_id = 0;
        $alias = JFilterOutput::stringURLSafe($param1);
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
        $db = JFactory::getDbo();
//    $this->query = $this->jsmdb->getQuery(true);
//    // Select some fields
//    $this->query->select('max(import_id) AS count');
//    // From the table
//    $this->query->from('#__sportsmanagement_'.$tablename);
//	$this->jsmdb->setQuery($this->query);
//	$count_id = $this->jsmdb->loadResult();

        /**
         * feld import_id einfügen
         */
        try {
            $this->jsmquery = $this->jsmdb->getQuery(true);
            $this->jsmquery = "ALTER TABLE `#__sportsmanagement_position_eventtype` ADD `import_id` INT(11) NOT NULL DEFAULT '0' ";
            $this->jsmdb->setQuery($this->jsmquery);
            sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
//$result = $this->jsmdb->execute();
        } catch (Exception $e) {
//    // catch any database errors.
//    $this->jsmdb->transactionRollback();
//    JErrorPage::render($e);
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
        /* 	
          $date = date("Y-m-d");
          $time = date("H:i:s");

          $file = str_replace("\\", "\\\\", $file);

          $insertquery = $this->jsmdb->getQuery(true);
          // Insert columns.
          $columns = array('date','time','class','file','text','function','line');
          // Insert values.
          $values = array('\''.$date.'\'','\''.$time.'\'','\''.$class.'\'','"'.$file.'"','"'.$text.'"','\''.$function.'\'','\''.$line.'\'');
          // Prepare the insert query.
          $insertquery
          ->insert($this->jsmdb->quoteName('#__sportsmanagement_error_log'))
          ->columns($this->jsmdb->quoteName($columns))
          ->values(implode(',', $values));
          // Set the query using our newly populated query object and execute it.
          $this->jsmdb->setQuery($insertquery);

          //$this->jsmapp->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($file,true).'</pre>'),'');
          //$this->jsmapp->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($insertquery,true).'</pre>'),'');

          if (!$this->jsmdb->execute())
          {
          $this->jsmapp->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($this->jsmdb->getErrorMsg(),true).'</pre>'),'Error');
          }
          else
          {

          }
         */
    }

}
