<?php

/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage allleagues
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

if (!defined('JSM_PATH')) {
    DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

// pr�ft vor Benutzung ob die gew�nschte Klasse definiert ist
if (!class_exists('sportsmanagementHelperHtml')) {
//add the classes for handling
    $classpath = JPATH_SITE . DS . JSM_PATH . DS . 'helpers' . DS . 'html.php';
    JLoader::register('sportsmanagementHelperHtml', $classpath);
}

/**
 * sportsmanagementViewallleagues
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewallleagues extends sportsmanagementView {

    protected $state = null;
    protected $item = null;
    protected $items = null;
    protected $pagination = null;

    /**
     * sportsmanagementViewallleagues::init()
     * 
     * @return void
     */
    function init() {
        $app = JFactory::getApplication();
        // JInput object
        $this->jinput = $app->input;
        $inputappend = '';
        $this->tableclass = $this->jinput->getVar('table_class', 'table', 'request', 'string');
        $user = JFactory::getUser();
        $starttime = microtime();
        $this->state = $this->get('State');
        $this->items = $this->get('Items');

        if (COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO) {
            $app->enqueueMessage(JText::_(__METHOD__ . ' ' . __LINE__ . ' Ausfuehrungszeit query<br><pre>' . print_r(sportsmanagementModeldatabasetool::getQueryTime($starttime, microtime()), true) . '</pre>'), 'Notice');
        }

        $this->pagination = $this->get('Pagination');

        //build the html options for nation
        $nation[] = JHtml::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));
        if ($res = JSMCountries::getCountryOptions()) {
            $nation = array_merge($nation, $res);
        }

        $lists['nation'] = $nation;
        $lists['nation2'] = JHtmlSelect::genericlist($nation, 'filter_search_nation', $inputappend . 'class="inputbox" style="width:140px; " onchange="this.form.submit();"', 'value', 'text', $this->state->get('filter.search_nation'));

        // Set page title
        $this->document->setTitle(JText::_('COM_SPORTSMANAGEMENT_ALLLEAGUES_PAGE_TITLE'));
        $form = new stdClass();
        $form->limitField = $this->pagination->getLimitBox();
        $this->filter = $this->state->get('filter.search');
        $this->form = $form;
        $this->user = $user;
        $this->sortDirection = $this->state->get('filter_order_Dir');
        $this->sortColumn = $this->state->get('filter_order');
        $this->lists = $lists;
    }

}

?>