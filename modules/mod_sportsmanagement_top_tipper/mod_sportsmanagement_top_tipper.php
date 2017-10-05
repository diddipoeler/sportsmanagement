<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version                1.0.05
 * @file                   agegroup.php
 * @author                 diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright              Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license                This file is part of SportsManagement.
 *
 * SportsManagement is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SportsManagement is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Diese Datei ist Teil von SportsManagement.
 *
 * SportsManagement ist Freie Software: Sie können es unter den Bedingungen
 * der GNU General Public License, wie von der Free Software Foundation,
 * Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
 * veröffentlichten Version, weiterverbreiten und/oder modifizieren.
 *
 * SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
 * OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
 * Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
 * Siehe die GNU General Public License für weitere Details.
 *
 * Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
 * Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
 *
 * Note : All ini files need to be saved as UTF-8 without BOM
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
if ( !defined('DS') )
{
    define('DS', DIRECTORY_SEPARATOR);
}

if ( !defined('JSM_PATH') )
{
    DEFINE('JSM_PATH', 'components/com_sportsmanagement');
}

if ( !class_exists('JSMModelList') )
{
    $classpath = JPATH_ADMINISTRATOR . DS . 'components/com_sportsmanagement' . DS . 'libraries' . DS . 'sportsmanagement' . DS . 'model.php';
    JLoader::register('JSMModelList', $classpath);
}

/**
 * prüft vor Benutzung ob die gewünschte Klasse definiert ist
 */
if ( !class_exists('sportsmanagementHelper') )
{
//add the classes for handling
    $classpath = JPATH_ADMINISTRATOR . DS . JSM_PATH . DS . 'helpers' . DS . 'sportsmanagement.php';
    JLoader::register('sportsmanagementHelper', $classpath);
    JModelLegacy::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

require_once( JPATH_SITE . DS . JSM_PATH . DS . 'helpers' . DS . 'route.php' );
require_once( JPATH_SITE . DS . JSM_PATH . DS . 'helpers' . DS . 'predictionroute.php' );
require_once( JPATH_SITE . DS . JSM_PATH . DS . 'models' . DS . 'predictionranking.php' );

/**
 * get helper
 */
require_once( dirname(__FILE__) . DS . 'helper.php' );

$document = JFactory::getDocument();
$mainframe = JFactory::getApplication();
$config    = array();

/**
 * add css file
 */
$document->addStyleSheet(JURI::base() . 'modules' . DS . $module->module . DS . 'css' . DS . $module->module . '.css');

$pg_id = $params->get('pg');

$config['limit']                      = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_LIMIT');
$config['show_project_name']          = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_PROJECT_NAME');
$config['show_project_name_selector'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_PROJECT_NAME_SELECTOR');
$config['show_rankingnav'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_RANKING_NAV');
$config['show_all_user']   = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_ALL_USER');
$config['show_user_icon']  = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_USER_ICON');
$config['show_user_link'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_USER_LINK');
$config['show_tip_details'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_TIP_DETAILS');
$config['show_tip_ranking'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_TIP_RANKING');
$config['show_tip_ranking_text'] = $params->get('show_tip_ranking_text');
$config['show_tip_ranking_round']      = $params->get('show_tip_ranking_round');
$config['show_tip_link_ranking_round'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_LINK_RANKING_ROUNDID');
$config['show_average_points']  = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_AVERAGE_POINTS');
$config['show_count_tips']      = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_COUNT_TIPS');
$config['show_count_joker']     = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_COUNT_JOKER');
$config['show_count_topptips']  = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_COUNT_TOPP_TIPS');
$config['show_count_difftips']  = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_COUNT_DIFF_TIPS');
$config['show_count_tendtipps'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_COUNT_TEND_TIPS');
$config['show_debug_modus'] = $params->get('show_debug_modus');


/**
 * das model laden
 */
$modelpg = JModelLegacy::getInstance('PredictionRanking', 'sportsmanagementModel');

sportsmanagementModelPrediction::$predictionGameID = $pg_id;
/**
 * jetzt noch das overall template nachladen
 * dadurch erhalten wir die sortierung aus dem backend
 */
$overallConfig        = sportsmanagementModelPrediction::getPredictionOverallConfig();
$config               = array_merge($overallConfig, $config);
$configavatar         = sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionusers');
$predictionGame[]     = sportsmanagementModelPrediction::getPredictionGame();
$predictionMember[]   = sportsmanagementModelPrediction::getPredictionMember($configavatar);
$predictionProjectS[] = sportsmanagementModelPrediction::getPredictionProjectS();
$actJoomlaUser[]      = JFactory::getUser();
$roundID              = sportsmanagementModelPrediction::$roundID;

$type_array    = array();
$type_array[]  = JHTML::_('select.option', '0', JText::_('JL_PRED_RANK_FULL_RANKING'));
$type_array[]  = JHTML::_('select.option', '1', JText::_('JL_PRED_RANK_FIRST_HALF'));
$type_array[]  = JHTML::_('select.option', '2', JText::_('JL_PRED_RANK_SECOND_HALF'));
$lists['type'] = $type_array;
unset($type_array);


require( JModuleHelper::getLayoutPath($module->module) );
?>