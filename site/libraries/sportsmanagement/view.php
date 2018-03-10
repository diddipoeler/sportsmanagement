<?php

/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage libraries
 */
defined('_JEXEC') or die();

$document = JFactory::getDocument();

$params_com = JComponentHelper::getParams( 'com_sportsmanagement' );
$jsmgrid	= $params_com->get( 'use_jsmgrid' );
$jsmflex	= $params_com->get( 'use_jsmflex' );
$cssflags	= $params_com->get( 'cfg_flags_css' );
$usefontawesome	= $params_com->get( 'use_fontawesome' );
$addfontawesome	= $params_com->get( 'add_fontawesome' );

// welche joomla version ?
if (version_compare(JVERSION, '3.0.0', 'ge')) {
//JHtml::_('jquery.framework');
    if($cssflags){
    $stylelink = '<link rel="stylesheet" href="' . JURI::root() . 'administrator/components/com_sportsmanagement/libraries/flag-icon/css/flag-icon.css' . '" type="text/css" />' . "\n";
    $document->addCustomTag($stylelink);
    }
    if($jsmflex){
    $stylelink = '<link rel="stylesheet" href="' . JURI::root() . 'administrator/components/com_sportsmanagement/assets/css/flex.css' . '" type="text/css" />' . "\n";
    $document->addCustomTag($stylelink);
    }
    if($jsmgrid){
    $stylelink = '<link rel="stylesheet" href="' . JURI::root() . 'administrator/components/com_sportsmanagement/assets/css/grid.css' . '" type="text/css" />' . "\n";
    $document->addCustomTag($stylelink);
    }
    if($usefontawesome){
    $stylelink = '<link rel="stylesheet" href="' . JURI::root() . 'components/com_sportsmanagement/assets/css/fontawesome_extend.css' . '" type="text/css" />' . "\n";
    $document->addCustomTag($stylelink);
    }
    if($addfontawesome){
    $stylelink = '<link rel="stylesheet" href="' . JURI::root() . 'administrator/components/com_sportsmanagement/libraries/fontawesome/css/font-awesome.min.css' . '" type="text/css" />' . "\n";
    $document->addCustomTag($stylelink);
    }
} elseif (version_compare(JVERSION, '2.5.0', 'ge')) {
// Joomla! 2.5 code here
//JHtml::_('behavior.modal');
//JHtml::_('behavior.framework');
} elseif (version_compare(JVERSION, '1.7.0', 'ge')) {
// Joomla! 1.7 code here
} elseif (version_compare(JVERSION, '1.6.0', 'ge')) {
// Joomla! 1.6 code here
} else {
// Joomla! 1.5 code here
}

/**
 * führt zu fehlern
 */
//JHtml::_('bootstrap.framework', false);

/**
 * sportsmanagementView
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementView extends JViewLegacy {

    protected $icon = '';
    protected $title = '';
    protected $layout = '';
    protected $tmpl = '';
    protected $table_data_class = '';
    protected $table_data_div = '';

    /**
     * sportsmanagementView::display()
     * 
     * @param mixed $tpl
     * @return
     */
    public function display($tpl = null) {
        // Reference global application object
        $this->app = JFactory::getApplication();
        // JInput object
        $this->jinput = $this->app->input;

        $this->modalheight = JComponentHelper::getParams($this->jinput->getCmd('option'))->get('modal_popup_height', 600);
        $this->modalwidth = JComponentHelper::getParams($this->jinput->getCmd('option'))->get('modal_popup_width', 900);

        if (version_compare(JSM_JVERSION, '4', 'eq')) {
            $this->uri = JUri::getInstance();
        } else {
            $this->uri = JFactory::getURI();
        }

        $this->action = $this->uri->toString();
        $this->params = $this->app->getParams();
        // Get a refrence of the page instance in joomla
        $this->document = JFactory::getDocument();
        $this->option = $this->jinput->getCmd('option');
        $this->user = JFactory::getUser();
        $this->view = $this->jinput->getVar("view");

        $this->model = $this->getModel();
        $headData = $this->document->getHeadData();
        $scripts = $headData['scripts'];
//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' picture server <br><pre>'.print_r($scripts,true).'</pre>'),'');

        /**
         * führt zu fehlern
         */
//unset($scripts[JUri::root(true) . '/media/jui/js/jquery.min.js']);
//unset($scripts[JUri::root(true) . '/media/jui/js/jquery-noconflict.js']);
//unset($scripts[JUri::root(true) . '/media/jui/js/jquery-migrate.min.js']);
//unset($scripts[JUri::root(true) . '/media/jui/js/bootstrap.min.js']);

        $headData['scripts'] = $scripts;
        $this->document->setHeadData($headData);

        switch ($this->view) {
            case 'resultsranking':
                $this->project = sportsmanagementModelProject::getProject(sportsmanagementModelProject::$cfg_which_database);
                $this->overallconfig = sportsmanagementModelProject::getOverallConfig(sportsmanagementModelProject::$cfg_which_database);

                break;
            default:
                $this->project = sportsmanagementModelProject::getProject(sportsmanagementModelProject::$cfg_which_database);
                $this->overallconfig = sportsmanagementModelProject::getOverallConfig(sportsmanagementModelProject::$cfg_which_database);
                $this->config = sportsmanagementModelProject::getTemplateConfig($this->getName(), sportsmanagementModelProject::$cfg_which_database);
                $this->config = array_merge($this->overallconfig, $this->config);
                break;
        }

        $this->init();

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * sportsmanagementView::addToolbar()
     * 
     * @return void
     */
    protected function addToolbar() {
        
    }

    /**
     * sportsmanagementView::init()
     * 
     * @return void
     */
    protected function init() {
        
    }

}
