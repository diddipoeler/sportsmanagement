<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      router.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * https://docs.joomla.org/J3.x:Supporting_SEF_URLs_in_your_component
 */
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('JSM_PATH')) {
    DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

$paramscomponent = JComponentHelper::getParams('com_sportsmanagement');
if (!defined('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO')) {
    DEFINE('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO', $paramscomponent->get('show_debug_info'));
}

if (!class_exists('sportsmanagementHelper')) {
    require_once (JPATH_ADMINISTRATOR . DS . JSM_PATH . DS . 'helpers' . DS .
            'sportsmanagement.php');
}

// Get the base version
$baseVersion = substr(JVERSION, 0, 3);

if (version_compare($baseVersion, '4.0', 'ge')) {
// Joomla! 4.0 code here
    defined('JSM_JVERSION') or define('JSM_JVERSION', 4);
}
if (version_compare($baseVersion, '3.0', 'ge')) {
// Joomla! 3.0 code here
    defined('JSM_JVERSION') or define('JSM_JVERSION', 3);
}
if (version_compare($baseVersion, '2.5', 'ge')) {
// Joomla! 2.5 code here
    defined('JSM_JVERSION') or define('JSM_JVERSION', 2);
}


if (class_exists('JComponentRouterBase')) {

    //abstract class SportsmanagementRouterBase extends JComponentRouterBase {}
    class SportsmanagementRouterBase {
        
    }

} else {

    class SportsmanagementRouterBase {

        /** @var JApplicationCms  */
        public $app;

        /** @var JMenu|null  */
        public $menu;

        /**
         * SportsmanagementRouterBase constructor.
         *
         * @param JApplicationCms $app
         * @param JMenu           $menu
         */
        public function __construct($app = null, $menu = null) {
            if ($app) {
                $this->app = $app;
            } else {
                $this->app = JFactory::getApplication('site');
            }

            if ($menu) {
                $this->menu = $menu;
            } else {
                $this->menu = $this->app->getMenu();
            }
        }

    }

}

/**
 * SportsmanagementRouter3
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class SportsmanagementRouter3 extends SportsmanagementRouterBase {

    
    /**
     * SportsmanagementRouter3::build3()
     * 
     * @param mixed $query
     * @return
     */
    public function build3(&$query) {
        $app = JFactory::getApplication();
        //$paramscomponent = JComponentHelper::getParams( 'com_sportsmanagement' );
        //    $show_debug_info = $paramscomponent->get( 'show_debug_info' );
        //    DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO',$show_debug_info );
        //if (! defined('COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO'))
        //{
        //DEFINE( 'COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO',$paramscomponent->get( 'show_debug_info' ) );
        //}
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query,true).'</pre>'   ),'');


        if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO) {
            $my_text = 'query -><pre>' . print_r($query, true) . '</pre>';
            //$my_text .= 'dump -><pre>'.print_r($query->dump(),true).'</pre>';
            sportsmanagementHelper::setDebugInfoText(__method__, __function__, 'sportsmanagementRoute', __line__, $my_text);
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' segments<br><pre>'.print_r($segments,true).'</pre>'   ),'');
        }

        $segments = array();

        $view = (isset($query['view']) ? $query['view'] : null);
        if ($view) {
            $segments[] = $view;
            unset($query['view']);
        } else {
            return $segments;
        }

        // now, the specifics
        switch ($view) {
            case 'predictionrules':
            case 'predictionranking':
            case 'predictionentry':
            case 'predictionresults':
            case 'predictionusers':
            case 'predictionuser':
            case 'rankingalltime';
                if (isset($query['cfg_which_database'])) {
                    $segments[] = $query['cfg_which_database'];
                    unset($query['cfg_which_database']);
                }
                break;
            default:
                /**
                 * die ersten parameter im query
                 */
                if (isset($query['cfg_which_database'])) {
                    $segments[] = $query['cfg_which_database'];
                    unset($query['cfg_which_database']);
                }
                if (isset($query['s'])) {
                    $segments[] = $query['s'];
                    unset($query['s']);
                }

                if (isset($query['p'])) {
                    $segments[] = $query['p'];
                    unset($query['p']);
                }
                break;
        }

        // now, the specifics
        switch ($view) {
            case 'rankingalltime':
                if (isset($query['l'])) {
                    $segments[] = $query['l'];
                    unset($query['l']);
                }
                if (isset($query['points'])) {
                    $segments[] = $query['points'];
                    unset($query['points']);
                }
                if (isset($query['type'])) {
                    $segments[] = $query['type'];
                    unset($query['type']);
                }
                if (isset($query['order'])) {
                    $segments[] = $query['order'];
                    unset($query['order']);
                }
                if (isset($query['dir'])) {
                    $segments[] = $query['dir'];
                    unset($query['dir']);
                }
                if (isset($query['s'])) {
                    $segments[] = $query['s'];
                    unset($query['s']);
                }

                if (isset($query['p'])) {
                    $segments[] = $query['p'];
                    unset($query['p']);
                }

                break;
            case 'event':
                if (isset($query['gcid'])) {
                    $segments[] = $query['gcid'];
                    unset($query['gcid']);
                }
                if (isset($query['eventID'])) {
                    $segments[] = $query['eventID'];
                    unset($query['eventID']);
                }
                break;
            case 'predictionrules':
                if (isset($query['prediction_id'])) {
                    $segments[] = $query['prediction_id'];
                    unset($query['prediction_id']);
                }

                break;
            case 'predictionranking':
                if (isset($query['prediction_id'])) {
                    $segments[] = $query['prediction_id'];
                    unset($query['prediction_id']);
                }
                if (isset($query['pggroup'])) {
                    $segments[] = $query['pggroup'];
                    unset($query['pggroup']);
                }
                if (isset($query['pj'])) {
                    $segments[] = $query['pj'];
                    unset($query['pj']);
                }
                if (isset($query['r'])) {
                    $segments[] = $query['r'];
                    unset($query['r']);
                }
                if (isset($query['pggrouprank'])) {
                    $segments[] = $query['pggrouprank'];
                    unset($query['pggrouprank']);
                }

                if (isset($query['type'])) {
                    $segments[] = $query['type'];
                    unset($query['type']);
                }
                if (isset($query['from'])) {
                    $segments[] = $query['from'];
                    unset($query['from']);
                }
                if (isset($query['to'])) {
                    $segments[] = $query['to'];
                    unset($query['to']);
                }

                break;
            case 'predictionentry':
                if (isset($query['prediction_id'])) {
                    $segments[] = $query['prediction_id'];
                    unset($query['prediction_id']);
                }
                if (isset($query['pggroup'])) {
                    $segments[] = $query['pggroup'];
                    unset($query['pggroup']);
                }
                if (isset($query['pj'])) {
                    $segments[] = $query['pj'];
                    unset($query['pj']);
                }
                if (isset($query['r'])) {
                    $segments[] = $query['r'];
                    unset($query['r']);
                }
                if (isset($query['uid'])) {
                    $segments[] = $query['uid'];
                    unset($query['uid']);
                }
                break;
            case 'predictionresults':
                if (isset($query['prediction_id'])) {
                    $segments[] = $query['prediction_id'];
                    unset($query['prediction_id']);
                }
                if (isset($query['pggroup'])) {
                    $segments[] = $query['pggroup'];
                    unset($query['pggroup']);
                }
                if (isset($query['pj'])) {
                    $segments[] = $query['pj'];
                    unset($query['pj']);
                }
                if (isset($query['r'])) {
                    $segments[] = $query['r'];
                    unset($query['r']);
                }
                if (isset($query['uid'])) {
                    $segments[] = $query['uid'];
                    unset($query['uid']);
                }
                break;
            case 'predictionusers':
                if (isset($query['prediction_id'])) {
                    $segments[] = $query['prediction_id'];
                    unset($query['prediction_id']);
                }
                if (isset($query['pggroup'])) {
                    $segments[] = $query['pggroup'];
                    unset($query['pggroup']);
                }
                if (isset($query['pj'])) {
                    $segments[] = $query['pj'];
                    unset($query['pj']);
                }
                if (isset($query['r'])) {
                    $segments[] = $query['r'];
                    unset($query['r']);
                }
                if (isset($query['uid'])) {
                    $segments[] = $query['uid'];
                    unset($query['uid']);
                }
            case 'predictionuser':
                if (isset($query['prediction_id'])) {
                    $segments[] = $query['prediction_id'];
                    unset($query['prediction_id']);
                }
                if (isset($query['pggroup'])) {
                    $segments[] = $query['pggroup'];
                    unset($query['pggroup']);
                }
                if (isset($query['pj'])) {
                    $segments[] = $query['pj'];
                    unset($query['pj']);
                }
                if (isset($query['r'])) {
                    $segments[] = $query['r'];
                    unset($query['r']);
                }
                if (isset($query['uid'])) {
                    $segments[] = $query['uid'];
                    unset($query['uid']);
                }
                if (isset($query['layout'])) {
                    $segments[] = $query['layout'];
                    unset($query['layout']);
                }

                break;
            case 'allprojects':
                if (isset($query['filter_search_nation'])) {
                    $segments[] = $query['filter_search_nation'];
                    unset($query['filter_search_nation']);
                }

                if (isset($query['filter_search_leagues'])) {
                    $segments[] = $query['filter_search_leagues'];
                    unset($query['filter_search_leagues']);
                }
                break;

            case 'clubinfo':
                if (isset($query['cid'])) {
                    $segments[] = $query['cid'];
                    unset($query['cid']);
                }
                break;
            case 'curve':
                if (isset($query['tid1'])) {
                    $segments[] = $query['tid1'];
                    unset($query['tid1']);
                }
                if (isset($query['tid2'])) {
                    $segments[] = $query['tid2'];
                    unset($query['tid2']);
                }
                if (isset($query['division'])) {
                    $segments[] = $query['division'];
                    unset($query['division']);
                }
                break;
            case 'eventsranking':
                if (isset($query['division'])) {
                    $segments[] = $query['division'];
                    unset($query['division']);
                }
                if (isset($query['tid'])) {
                    $segments[] = $query['tid'];
                    unset($query['tid']);
                }
                if (isset($query['evid'])) {
                    $segments[] = $query['evid'];
                    unset($query['evid']);
                }
                if (isset($query['mid'])) {
                    $segments[] = $query['mid'];
                    unset($query['mid']);
                }
                break;

            case 'editevents':
                if (isset($query['mid'])) {
                    $segments[] = $query['mid'];
                    unset($query['mid']);
                }
                break;
            case "matrix":
                if (isset($query['division'])) {
                    $segments[] = $query['division'];
                    unset($query['division']);
                }
                if (isset($query['r'])) {
                    $segments[] = $query['r'];
                    unset($query['r']);
                }
                break;
            case 'matchreport':
            case 'nextmatch':
                if (isset($query['mid'])) {
                    $segments[] = $query['mid'];
                    unset($query['mid']);
                }
                if (isset($query['pics'])) {
                    $segments[] = $query['pics'];
                    unset($query['pics']);
                }
                if (isset($query['ptid'])) {
                    $segments[] = $query['ptid'];
                    unset($query['ptid']);
                }
                break;
            case "playground":
                if (isset($query['pgid'])) {
                    $segments[] = $query['pgid'];
                    unset($query['pgid']);
                }
                break;
            case 'ranking':
            case 'rankingmatrix':
                if (isset($query['type'])) {
                    $segments[] = $query['type'];
                    unset($query['type']);
                }
                if (isset($query['r'])) {
                    $segments[] = $query['r'];
                    unset($query['r']);
                }
                if (isset($query['from'])) {
                    $segments[] = $query['from'];
                    unset($query['from']);
                }
                if (isset($query['to'])) {
                    $segments[] = $query['to'];
                    unset($query['to']);
                }
                if (isset($query['division'])) {
                    $segments[] = $query['division'];
                    unset($query['division']);
                }

                if (isset($query['order'])) {
                    $segments[] = $query['order'];
                    unset($query['order']);
                }

                if (isset($query['dir'])) {
                    $segments[] = $query['dir'];
                    unset($query['dir']);
                }
                /**
                 * beim printbutton wird ein popup geöffnet
                 */
                if (isset($query['tmpl'])) {
                    $segments[] = $query['tmpl'];
                    unset($query['tmpl']);
                }
                if (isset($query['print'])) {
                    $segments[] = $query['print'];
                    unset($query['print']);
                }

                break;
            case 'roster':
            case 'teaminfo':
            case 'teamstats':
                if (isset($query['tid'])) {
                    $segments[] = $query['tid'];
                    unset($query['tid']);
                }
                // diddipoeler
                if (isset($query['ptid'])) {
                    $segments[] = $query['ptid'];
                    unset($query['ptid']);
                }
                if (isset($query['division'])) {
                    $segments[] = $query['division'];
                    unset($query['division']);
                }
                break;

            case 'clubplan':
                if (isset($query['cid'])) {
                    $segments[] = $query['cid'];
                    unset($query['cid']);
                }
                if (isset($query['task'])) {
                    $segments[] = $query['task'];
                    unset($query['task']);
                }

                break;

            case 'teamplan':
                if (isset($query['tid'])) {
                    $segments[] = $query['tid'];
                    unset($query['tid']);
                }
                if (isset($query['division'])) {
                    $segments[] = $query['division'];
                    unset($query['division']);
                }
                if (isset($query['mode'])) {
                    $segments[] = $query['mode'];
                    unset($query['mode']);
                }
                // diddipoeler
                if (isset($query['ptid'])) {
                    $segments[] = $query['ptid'];
                    unset($query['ptid']);
                }

                break;
            case 'jltournamenttree':
                if (isset($query['r'])) {
                    $segments[] = $query['r'];
                    unset($query['r']);
                }
                break;
            case 'results':
            case 'editmatch':
                if (isset($query['r'])) {
                    $segments[] = $query['r'];
                    unset($query['r']);
                }
                if (isset($query['division'])) {
                    $segments[] = $query['division'];
                    unset($query['division']);
                }
                if (isset($query['mode'])) {
                    $segments[] = $query['mode'];
                    unset($query['mode']);
                }
                if (isset($query['order'])) {
                    $segments[] = $query['order'];
                    unset($query['order']);
                }
                if (isset($query['layout'])) {
                    $segments[] = $query['layout'];
                    unset($query['layout']);
                }
                if (isset($query['matchid'])) {
                    $segments[] = $query['matchid'];
                    unset($query['matchid']);
                }
                if (isset($query['tmpl'])) {
                    $segments[] = $query['tmpl'];
                    unset($query['tmpl']);
                }
                if (isset($query['oldlayout'])) {
                    $segments[] = $query['oldlayout'];
                    unset($query['oldlayout']);
                }
                if (isset($query['team'])) {
                    $segments[] = $query['team'];
                    unset($query['team']);
                }
                if (isset($query['pteam'])) {
                    $segments[] = $query['pteam'];
                    unset($query['pteam']);
                }
                if (isset($query['match_date'])) {
                    $segments[] = $query['match_date'];
                    unset($query['match_date']);
                }

                break;
            case 'resultsmatrix':
            case 'resultsranking':
                if (isset($query['r'])) {
                    $segments[] = $query['r'];
                    unset($query['r']);
                }
                if (isset($query['division'])) {
                    $segments[] = $query['division'];
                    unset($query['division']);
                }
                if (isset($query['mode'])) {
                    $segments[] = $query['mode'];
                    unset($query['mode']);
                }
                if (isset($query['order'])) {
                    $segments[] = $query['order'];
                    unset($query['order']);
                }
                if (isset($query['layout'])) {
                    $segments[] = $query['layout'];
                    unset($query['layout']);
                }

                break;
            case 'player':
            case 'staff':
                if (isset($query['tid'])) {
                    $segments[] = $query['tid'];
                    unset($query['tid']);
                }
                if (isset($query['pid'])) {
                    $segments[] = $query['pid'];
                    unset($query['pid']);
                }
                break;
            case 'clubs':
            case 'stats':
            case 'teams':
            case 'teamstree':
                if (isset($query['division'])) {
                    $segments[] = $query['division'];
                    unset($query['division']);
                }
                break;
            case 'statsranking':
                if (isset($query['division'])) {
                    $segments[] = $query['division'];
                    unset($query['division']);
                }
                if (isset($query['tid'])) {
                    $segments[] = $query['tid'];
                    unset($query['tid']);
                }
                break;
            case 'referee':
                if (isset($query['pid'])) {
                    $segments[] = $query['pid'];
                    unset($query['pid']);
                }
                break;
            case 'tree':
                if (isset($query['did'])) {
                    $segments[] = $query['did'];
                    unset($query['did']);
                }
                break;

            default:
                break;
        }

        return $segments;
    }

    /**
     * SportsmanagementRouter3::parse3()
     * 
     * @param mixed $segments
     * @return
     */
    /**
     * Parse the segments of a URL.
     *
     * @param   array  &$segments  The segments of the URL to parse.
     *
     * @return  array  The URL attributes to be used by the application.
     *
     * @since   3.3
     */
    public function parse3(&$segments) {
        $app = JFactory::getApplication();

        $vars = array();

        $vars['view'] = $segments[0];

        // now, the specifics
        switch ($vars['view']) {
            case 'predictionrules':
            case 'predictionranking':
            case 'predictionentry':
            case 'predictionresults':
            case 'predictionusers':
            case 'predictionuser':
            case 'rankingalltime';
                if (isset($segments[1])) {
                    $vars['cfg_which_database'] = $segments[1];
                }

                break;

            default:
                /**
                 * die ersten parameter im query
                 */
                if (isset($segments[1])) {
                    $vars['cfg_which_database'] = $segments[1];
                }
                if (isset($segments[2])) {
                    $vars['s'] = $segments[2];
                }

                if (isset($segments[3])) {
                    $vars['p'] = $segments[3];
                }
                break;
        }

        switch ($vars['view']) { // the view...
            case 'rankingalltime';
                if (isset($segments[2])) {
                    $vars['l'] = $segments[2];
                }
                if (isset($segments[3])) {
                    $vars['points'] = $segments[3];
                }
                if (isset($segments[4])) {
                    $vars['type'] = $segments[4];
                }
                if (isset($segments[5])) {
                    $vars['order'] = $segments[5];
                }
                if (isset($segments[6])) {
                    $vars['dir'] = $segments[6];
                }
                if (isset($segments[7])) {
                    $vars['s'] = $segments[7];
                }

                if (isset($segments[8])) {
                    $vars['p'] = $segments[8];
                }

                break;

            case 'event':
                $vars['gcid'] = $segments[1];
                if (count($segments) < 3) {
                    $vars['eventID'] = JFactory::getApplication()->input->getVar('eventId');
                } else {
                    $vars['eventID'] = $segments[2];
                }
                $vars['Itemid'] = jsmGCalendarUtil::getItemId($vars['gcid']);
                break;

            case 'google':

            case 'gcalendar':
                // do nothing

                break;

            case 'predictionranking':
                if (isset($segments[2])) {
                    $vars['prediction_id'] = $segments[2];
                }
                if (isset($segments[3])) {
                    $vars['pggroup'] = $segments[3];
                }
                if (isset($segments[4])) {
                    $vars['pj'] = $segments[4];
                }
                if (isset($segments[5])) {
                    $vars['r'] = $segments[5];
                }
                if (isset($segments[6])) {
                    $vars['pggrouprank'] = $segments[6];
                }
                if (isset($segments[7])) {
                    $vars['type'] = $segments[7];
                }
                if (isset($segments[8])) {
                    $vars['from'] = $segments[8];
                }
                if (isset($segments[9])) {
                    $vars['to'] = $segments[9];
                }

                break;

            case 'predictionentry':
                if (isset($segments[2])) {
                    $vars['prediction_id'] = $segments[2];
                }
                if (isset($segments[3])) {
                    $vars['pggroup'] = $segments[3];
                }
                if (isset($segments[4])) {
                    $vars['pj'] = $segments[4];
                }
                if (isset($segments[5])) {
                    $vars['r'] = $segments[5];
                }
                if (isset($segments[6])) {
                    $vars['uid'] = $segments[6];
                }

                break;

            case 'predictionresults':
                if (isset($segments[2])) {
                    $vars['prediction_id'] = $segments[2];
                }
                if (isset($segments[3])) {
                    $vars['pggroup'] = $segments[3];
                }
                if (isset($segments[4])) {
                    $vars['pj'] = $segments[4];
                }
                if (isset($segments[5])) {
                    $vars['r'] = $segments[5];
                }
                if (isset($segments[6])) {
                    $vars['uid'] = $segments[6];
                }

                break;

            case 'predictionusers':
                if (isset($segments[2])) {
                    $vars['prediction_id'] = $segments[2];
                }
                if (isset($segments[3])) {
                    $vars['pggroup'] = $segments[3];
                }
                if (isset($segments[4])) {
                    $vars['pj'] = $segments[4];
                }
                if (isset($segments[5])) {
                    $vars['r'] = $segments[5];
                }
                if (isset($segments[6])) {
                    $vars['uid'] = $segments[6];
                }

                break;

            case 'predictionuser':
                if (isset($segments[2])) {
                    $vars['prediction_id'] = $segments[2];
                }
                if (isset($segments[3])) {
                    $vars['pggroup'] = $segments[3];
                }
                if (isset($segments[4])) {
                    $vars['pj'] = $segments[4];
                }
                if (isset($segments[5])) {
                    $vars['r'] = $segments[5];
                }
                if (isset($segments[6])) {
                    $vars['uid'] = $segments[6];
                }
                if (isset($segments[7])) {
                    $vars['layout'] = $segments[7];
                }

                break;

            case 'predictionrules':
                if (isset($segments[2])) {
                    $vars['prediction_id'] = $segments[2];
                }

                break;

            case 'allprojects':
                if (isset($segments[1])) {
                    $vars['filter_search_nation'] = $segments[1];
                }

                if (isset($segments[2])) {
                    $vars['filter_search_leagues'] = $segments[2];
                }

                break;

            case 'clubinfo':
                if (isset($segments[4])) {
                    $vars['cid'] = $segments[4];
                }

                break;

            case 'curve':
                if (isset($segments[4])) {
                    $vars['tid1'] = $segments[4];
                }
                if (isset($segments[5])) {
                    $vars['tid2'] = $segments[5];
                }
                if (isset($segments[6])) {
                    $vars['division'] = $segments[6];
                }

                break;

            case 'eventsranking':
                if (isset($segments[4])) {
                    $vars['division'] = $segments[4];
                }
                if (isset($segments[5])) {
                    $vars['tid'] = $segments[5];
                }
                if (isset($segments[6])) {
                    $vars['evid'] = $segments[6];
                }
                if (isset($segments[7])) {
                    $vars['mid'] = $segments[7];
                }

                break;

            case 'editevents':
                if (isset($segments[4])) {
                    $vars['mid'] = $segments[4];
                }

                break;

            case 'matrix':
                if (isset($segments[4])) {
                    $vars['division'] = $segments[4];
                }
                if (isset($segments[5])) {
                    $vars['r'] = $segments[5];
                }

                break;

            case 'playground':
                if (isset($segments[4])) {
                    $vars['pgid'] = $segments[4];
                }

                break;

            case "ranking":
            case 'rankingmatrix':
                if (isset($segments[4])) {
                    $vars['type'] = $segments[4];
                }
                if (isset($segments[5])) {
                    $vars['r'] = $segments[5];
                }
                if (isset($segments[6])) {
                    $vars['from'] = $segments[6];
                }
                if (isset($segments[7])) {
                    $vars['to'] = $segments[7];
                }
                if (isset($segments[8])) {
                    $vars['division'] = $segments[8];
                }

                if (isset($segments[9])) {
                    $vars['order'] = $segments[9];
                }
                if (isset($segments[10])) {
                    $vars['dir'] = $segments[10];
                }
                /**
                 * beim printbutton wird ein popup geöffnet
                 */
                if (isset($segments[11])) {
                    $vars['tmpl'] = $segments[11];
                }
                if (isset($segments[12])) {
                    $vars['print'] = $segments[12];
                }

                break;

            case 'teamplan':
                if (isset($segments[4])) {
                    $vars['tid'] = $segments[4];
                }
                if (isset($segments[5])) {
                    $vars['division'] = $segments[5];
                }
                if (isset($segments[6])) {
                    $vars['mode'] = $segments[6];
                }
                // diddipoeler
                if (isset($segments[7])) {
                    $vars['ptid'] = $segments[7];
                }

                break;

            case 'clubplan':
                if (isset($segments[4])) {
                    $vars['cid'] = $segments[4];
                }
                if (isset($segments[5])) {
                    $vars['task'] = $segments[5];
                }

                break;

            case 'roster':
            case 'teaminfo':
            case 'teamstats':
                if (isset($segments[4])) {
                    $vars['tid'] = $segments[4];
                }
                //diddipoeler
                if (isset($segments[5])) {
                    $vars['ptid'] = $segments[5];
                }
                if (isset($segments[6])) {
                    $vars['division'] = $segments[6];
                }

                break;

            case 'jltournamenttree':
                if (isset($segments[4])) {
                    $vars['r'] = $segments[4];
                }

                break;

            case 'results':
            case 'editmatch':
                if (isset($segments[4])) {
                    $vars['r'] = $segments[4];
                }
                if (isset($segments[5])) {
                    $vars['division'] = $segments[5];
                }
                if (isset($segments[6])) {
                    $vars['mode'] = $segments[6];
                }
                if (isset($segments[7])) {
                    $vars['order'] = $segments[7];
                }
                if (isset($segments[8])) {
                    $vars['layout'] = $segments[8];
                }
                if (isset($segments[9])) {
                    $vars['matchid'] = $segments[9];
                }
                if (isset($segments[10])) {
                    $vars['tmpl'] = $segments[10];
                }
                if (isset($segments[11])) {
                    $vars['oldlayout'] = $segments[11];
                }
                if (isset($segments[12])) {
                    $vars['team'] = $segments[12];
                }
                if (isset($segments[13])) {
                    $vars['pteam'] = $segments[13];
                }
                if (isset($segments[14])) {
                    $vars['match_date'] = $segments[14];
                }

                break;

            case 'resultsmatrix':
            case 'resultsranking':
                if (isset($segments[4])) {
                    $vars['r'] = $segments[4];
                }
                if (isset($segments[5])) {
                    $vars['division'] = $segments[5];
                }
                if (isset($segments[6])) {
                    $vars['mode'] = $segments[6];
                }
                if (isset($segments[7])) {
                    $vars['order'] = $segments[7];
                }
                if (isset($segments[8])) {
                    $vars['layout'] = $segments[8];
                }

                break;

            case 'matchreport':
            case 'nextmatch':
                if (isset($segments[4])) {
                    $vars['mid'] = $segments[4];
                }
                if (isset($segments[5])) {
                    $vars['pics'] = $segments[5];
                }
                if (isset($segments[6])) {
                    $vars['ptid'] = $segments[6];
                }
                break;

            case 'player':
            case 'staff':
                if (isset($segments[4])) {
                    $vars['tid'] = $segments[4];
                }
                if (isset($segments[5])) {
                    $vars['pid'] = $segments[5];
                }

                break;

            case 'clubs':
            case 'stats':
            case 'teams':
            case 'teamstree':

                if (isset($segments[4])) {
                    $vars['division'] = $segments[4];
                }

                break;

            case 'statsranking':
                if (isset($segments[4])) {
                    $vars['division'] = $segments[4];
                }
                if (isset($segments[5])) {
                    $vars['tid'] = $segments[5];
                }

                break;

            // /standard-referee/referee/46/2
            case 'referee':
                if (isset($segments[4])) {
                    $vars['pid'] = $segments[4];
                }

                break;

            case 'tree':
                if (isset($segments[5])) {
                    $vars['did'] = $segments[5];
                }
                break;

            default:

                break;
        }

        

        return $vars;
    }

}

/**
 * sportsmanagementBuildRoute()
 * 
 * @param mixed $query
 * @return
 */
function sportsmanagementBuildRoute(&$query) {

    $router = new SportsmanagementRouter3;
    return $router->build3($query);
}

/**
 * sportsmanagementParseRoute()
 * 
 * @param mixed $segments
 * @return
 */
function sportsmanagementParseRoute($segments) {
    $router = new SportsmanagementRouter3;
    return $router->parse3($segments);
}

?>
