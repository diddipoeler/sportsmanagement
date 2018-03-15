<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage matches
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');

/**
 * sportsmanagementViewMatches
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewMatches extends sportsmanagementView {

    /**
     * sportsmanagementViewMatches::init()
     * 
     * @return void
     */
    public function init() {
        $app = JFactory::getApplication();
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        if (version_compare(JSM_JVERSION, '4', 'eq')) {
            $uri = JUri::getInstance();
        } else {
            $uri = JFactory::getURI();
        }
        $model = $this->getModel();
        $params = JComponentHelper::getParams($option);
        $document = JFactory::getDocument();
        $view = $jinput->get('view');
        $_db = sportsmanagementHelper::getDBConnection(); // the method is contextual so we must have a DBO
        $table_info = $_db->getTableFields('#__sportsmanagement_match');

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($table_info,true).'</pre>'),'Notice');

        $starttime = microtime();
        
        // Must fix this code up one day.
        //Notice: Undefined index: code 
        $app->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' code <br><pre>' . print_r($_GET['code'], true) . '</pre><br>', 'Notice');

        //$this->state = $this->get('State'); 
        //$this->sortDirection = $this->state->get('list.direction');
        //$this->sortColumn = $this->state->get('list.ordering');
        //$items = $this->get('Items');

        if (COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO) {
            $app->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' Ausfuehrungszeit query<br><pre>' . print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()), true) . '</pre>'), 'Notice');
        }

        //$total = $this->get('Total');
        //$pagination = $this->get('Pagination');

        $table = JTable::getInstance('match', 'sportsmanagementTable');
        $this->table = $table;

        $this->project_id = $app->getUserState("$option.pid", '0');
        $this->project_art_id = $app->getUserState("$option.project_art_id", '0');
        //$this->project_id	= $app->getUserState( "$option.pid", '0' );

        $this->project_id = $jinput->get('pid', 0);
        if (!$this->project_id) {
            $this->project_id = $app->getUserState("$option.pid", '0');
        }

        $this->rid = $jinput->get('rid', 0);
        if (!$this->rid) {
            $this->rid = $app->getUserState("$option.rid", '0');
        }
        $mdlProject = JModelLegacy::getInstance('Project', 'sportsmanagementModel');
        $projectws = $mdlProject->getProject($this->project_id);

        if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            $my_text = 'projectws <pre>' . print_r($projectws, true) . '</pre>';
            //$my_text .= 'inoutstats <pre>'.print_r($inoutstats,true).'</pre>';   
            //$my_text .= 'form_value <pre>'.print_r($form_value,true).'</pre>';       
            sportsmanagementHelper::setDebugInfoText(__METHOD__, __FUNCTION__, __CLASS__, __LINE__, $my_text);
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' projectws<br><pre>'.print_r($projectws,true).'</pre>'),'');
        }

        $mdlRound = JModelLegacy::getInstance('Round', 'sportsmanagementModel');
        $roundws = $mdlRound->getRound($this->rid);

        //build the html selectlist for rounds
        $ress = sportsmanagementHelper::getRoundsOptions($this->project_id, 'ASC', true);

        /**
         * dadurch werden die spaltenbreiten optimiert
         */
        $this->document->addStyleSheet(JUri::root() . 'administrator/components/com_sportsmanagement/assets/css/form_control.css', 'text/css');

        foreach ($ress as $res) {
            $datum = sportsmanagementHelper::convertDate($res->round_date_first, 1) . ' - ' . sportsmanagementHelper::convertDate($res->round_date_last, 1);
            $project_roundslist[] = JHtml::_('select.option', $res->id, sprintf("%s (%s)", $res->name, $datum));
        }
        $lists['project_rounds'] = JHtml::_('select.genericList', $project_roundslist, 'rid', 'class="inputbox" ' .
                        'onChange="document.getElementById(\'short_act\').value=\'rounds\';' .
                        'document.roundForm.submit();" ', 'value', 'text', $roundws->id);

        $lists['project_rounds2'] = JHtml::_('select.genericList', $project_roundslist, 'rid', 'class="inputbox" ', 'value', 'text', $roundws->id);
        // diddipoeler rounds for change in match
        $project_change_roundslist = array();
        if ($ress = sportsmanagementHelper::getRoundsOptions($this->project_id, 'ASC', true)) {
            $project_change_roundslist = array_merge($project_change_roundslist, $ress);
        }
        $lists['project_change_rounds'] = $project_change_roundslist;
        unset($project_change_roundslist);

        //build the html options for teams
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' items<br><pre>'.print_r($items,true).'</pre>'),'');
        foreach ($this->items as $row) {
            if ($row->divhomeid == '') {
                $row->divhomeid = 0;
            }
            if ($row->divawayid == '') {
                $row->divawayid = 0;
            }

            if ($this->project_art_id == 3) {
                $teams[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PERSON'));
            } else {
                $teams[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM'));
            }

            $divhomeid = 0;
            //apply the filter only if both teams are from the same division
            //teams are not from the same division in tournament mode with divisions
            if ($row->divhomeid == $row->divawayid) {
                $divhomeid = $row->divhomeid;
            } else {
                $row->divhomeid = 0;
                $row->divawayid = 0;
            }
            if ($projectteams = $mdlProject->getProjectTeamsOptions($this->project_id, $divhomeid)) {
                $teams = array_merge($teams, $projectteams);
            }

            $lists['teams_' . $divhomeid] = $teams;
            unset($teams);

            // sind die verzeichnisse vorhanden ?
            //$dest = JPATH_ROOT.'/media/com_sportsmanagement/database/matchreport/'.$row->id;
            $dest = JPATH_ROOT . '/images/com_sportsmanagement/database/matchreport/' . $row->id;
            if (JFolder::exists($dest)) {
                //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' pfad vorhanden<br><pre>'.print_r($dest,true).'</pre>'),'');    
            } else {
                //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' pfad nicht vorhanden<br><pre>'.print_r($dest,true).'</pre>'),'');    
                $result = JFolder::create($dest);
                //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' ' .  ' result<br><pre>'.print_r($result,true).'</pre>'),'');
            }
        }

        //build the html options for extratime
        $match_result_type[] = JHtmlSelect::option('0', JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_RT'));
        $match_result_type[] = JHtmlSelect::option('1', JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_OT'));
        $match_result_type[] = JHtmlSelect::option('2', JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_SO'));
        $lists['match_result_type'] = $match_result_type;
        unset($match_result_type);

        //build the html options for article
        $articles[] = JHtmlSelect::option('0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_ARTICLE'));
        if ($res = sportsmanagementHelper::getArticleList($projectws->category_id)) {
            $articles = array_merge($articles, $res);
        }
        $lists['articles'] = $articles;
        unset($articles);

        //build the html options for divisions
        $divisions[] = JHtmlSelect::option('0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DIVISION'));
        $mdlDivisions = JModelLegacy::getInstance('divisions', 'sportsmanagementModel');
        if ($res = $mdlDivisions->getDivisions($this->project_id)) {
            $divisions = array_merge($divisions, $res);
        }
        $lists['divisions'] = $divisions;
        unset($divisions);

        $document->addScript(JURI::base() . 'components/' . $option . '/assets/js/matches.js');

        $selectlist = array();
        if (isset($table_info['#__sportsmanagement_match'])) {
            foreach ($table_info['#__sportsmanagement_match'] as $field => $value) {
                $select_Options = sportsmanagementHelper::getExtraSelectOptions($view, $field);

                if ($select_Options) {
                    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($select_Options,true).'</pre>'),'Notice');  

                    $select[] = JHtmlSelect::option('0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'));
                    $select = array_merge($select, $select_Options);
                    $selectlist[$field] = $select;
                    unset($select);
                }
            }
        }

        //$this->assignRef('division',$division);

        $this->user = JFactory::getUser();
        $this->lists = $lists;
        $this->selectlist = $selectlist;
        $this->option = $option;
        $this->matches = $this->items;
        $this->ress = $ress;
        $this->projectws = $projectws;
        $this->roundws = $roundws;
        //$this->pagination	= $pagination;
        $this->request_url = $uri->toString();
        $this->prefill = $params->get('use_prefilled_match_roster',0);
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getLayout<br><pre>'.print_r($this->getLayout(),true).'</pre>'),'Notice');

        if ($this->getLayout() == 'massadd' || $this->getLayout() == 'massadd_3') {
            //build the html options for massadd create type
            $createTypes = array(0 => JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD'),
                1 => JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_1'),
                2 => JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_2')
            );
            $ctOptions = array();
            foreach ($createTypes AS $key => $value) {
                $ctOptions[] = JHtmlSelect::option($key, $value);
            }
            $lists['createTypes'] = JHtmlSelect::genericlist($ctOptions, 'ct[]', 'class="inputbox" onchange="javascript:displayTypeView();"', 'value', 'text', 1, 'ct');
            unset($createTypes);

            // build the html radio for adding into one round / all rounds
            $createYesNo = array(0 => JText::_('JNO'), 1 => JText::_('JYES'));
            $ynOptions = array();
            foreach ($createYesNo AS $key => $value) {
                $ynOptions[] = JHtmlSelect::option($key, $value);
            }
            $lists['addToRound'] = JHtmlSelect::radiolist($ynOptions, 'addToRound', 'class="inputbox"', 'value', 'text', 0);

            // build the html radio for auto publish new matches
            $lists['autoPublish'] = JHtmlSelect::radiolist($ynOptions, 'autoPublish', 'class="inputbox"', 'value', 'text', 0);
            $this->lists = $lists;
            $this->setLayout('massadd');
        }
    }

    /**
     * Add the page title and toolbar.
     *
     * @since	1.7
     */
    protected function addToolbar() {
        //// Get a refrence of the page instance in joomla
//		$document	= JFactory::getDocument();
//        // Set toolbar items for the page
//        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
//        $document->addCustomTag($stylelink);

        $app = JFactory::getApplication();
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // store the variable that we would like to keep for next time
        // function syntax is setUserState( $key, $value );
        $app->setUserState("$option.rid", $this->rid);
        $app->setUserState("$option.pid", $this->project_id);

        $massadd = $jinput->getInt('massadd', 0);

        // Set toolbar items for the page
        $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_TITLE');

        if (!$massadd) {
            //JToolbarHelper::publishList('matches.publish');
            //JToolbarHelper::unpublishList('matches.unpublish');

            JToolbarHelper::publish('match.insertgooglecalendar', 'JLIB_HTML_CALENDAR', true);
            JToolbarHelper::divider();
            JToolbarHelper::publish('matches.count_result_yes', 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_INCL', true);
            JToolbarHelper::unpublish('matches.count_result_no', 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_INCL', true);
            JToolbarHelper::divider();
            JToolbarHelper::publish('matches.publish', 'JTOOLBAR_PUBLISH', true);
            JToolbarHelper::unpublish('matches.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolbarHelper::divider();

            JToolbarHelper::apply('matches.saveshort');
            JToolbarHelper::divider();

            JToolbarHelper::custom('match.massadd', 'new.png', 'new_f2.png', JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_MATCHES'), false);
            JToolbarHelper::addNew('match.addmatch', JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_ADD_MATCH'));
//			JToolbarHelper::deleteList(JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_WARNING'), 'match.remove');
            JToolbarHelper::divider();

            JToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=rounds');
        } else {
            JToolbarHelper::custom('match.cancelmassadd', 'cancel.png', 'cancel_f2.png', JText::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_CANCEL_MATCHADD'), false);
        }

        parent::addToolbar();
    }

}

?>
