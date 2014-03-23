<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');
require_once(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'sportsmanagement.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'models'.DS.'predictionranking.php');
require_once(dirname(__FILE__).DS.'helper.php');

$document = JFactory::getDocument();
$mainframe = JFactory::getApplication();
$config = array();

//add css file
$document->addStyleSheet(JURI::base().'modules/mod_sportsmanagement_top_tipper/css/mod_sportsmanagement_top_tipper.css');

$pg_id = $params->get('pg');

$config['limit'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_LIMIT'); 
$config['show_project_name'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_PROJECT_NAME');
$config['show_project_name_selector'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_PROJECT_NAME_SELECTOR');

$config['show_rankingnav'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_RANKING_NAV');
$config['show_all_user'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_ALL_USER');
$config['show_user_icon'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_USER_ICON');

$config['show_user_link'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_USER_LINK');

$config['show_tip_details'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_TIP_DETAILS');
$config['show_tip_ranking'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_TIP_RANKING');

$config['show_tip_ranking_round'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_TIP_RANKING_ROUNDID');
$config['show_tip_link_ranking_round'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_LINK_RANKING_ROUNDID');

$config['show_average_points'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_AVERAGE_POINTS');
$config['show_count_tips'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_COUNT_TIPS');
$config['show_count_joker'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_COUNT_JOKER');
$config['show_count_topptips'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_COUNT_TOPP_TIPS');
$config['show_count_difftips'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_COUNT_DIFF_TIPS');
$config['show_count_tendtipps'] = $params->get('MOD_SPORTSMANAGEMENT_TOP_TIPPER_PREDICTION_GAME_SHOW_COUNT_TEND_TIPS');

$config['show_debug_modus'] = $params->get('show_debug_modus');

//$mainframe->enqueueMessage(JText::_(__FILE__.' '.__LINE__.' config<br><pre>'.print_r($config,true).'</pre>'),'');

//echo 'prediction game id -> '.$pg_id.'<br>';

/*
// sprachedatei der extension nachladen, damit wir nicht noch mal alles eintragen müssen
$lang =& JFactory::getLanguage();
$extension = 'com_sportsmanagement_predictiongame';
$base_dir = JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'extensions'.DS.'predictiongame';
// $language_tag = 'en-GB';
$language_tag = '';
$reload = true;
$lang->load($extension, $base_dir, $language_tag, $reload);
// sprachdatei der komponente nachladen
$extension = 'com_sportsmanagement';
$base_dir = JPATH_SITE;
$lang->load($extension, $base_dir, $language_tag, $reload);
*/

//JRequest::setVar('prediction_id', $pg_id);

// das model laden
$modelpg = JModel::getInstance('PredictionRanking', 'sportsmanagementModel');

sportsmanagementModelPrediction::$predictionGameID = $pg_id;
// jetzt nach das overall template nachladen
// dadurch erhalten wir die sortierung aus dem backend
$overallConfig	= sportsmanagementModelPrediction::getPredictionOverallConfig();
$config = array_merge($overallConfig,$config);
$configavatar = sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionusers');
$predictionGame[] = sportsmanagementModelPrediction::getPredictionGame();
$predictionMember[] = sportsmanagementModelPrediction::getPredictionMember($configavatar);
$predictionProjectS[] = sportsmanagementModelPrediction::getPredictionProjectS();
$actJoomlaUser[] = JFactory::getUser();
$roundID = $modelpg->roundID;

//$mainframe->enqueueMessage(JText::_(__FILE__.' '.__LINE__.' predictionGame<br><pre>'.print_r($predictionGame,true).'</pre>'),'');
//$mainframe->enqueueMessage(JText::_(__FILE__.' '.__LINE__.' predictionProjectS<br><pre>'.print_r($predictionProjectS,true).'</pre>'),'');

$type_array = array();
$type_array[]=JHTML ::_('select.option','0',JText::_('JL_PRED_RANK_FULL_RANKING'));
$type_array[]=JHTML ::_('select.option','1',JText::_('JL_PRED_RANK_FIRST_HALF'));
$type_array[]=JHTML ::_('select.option','2',JText::_('JL_PRED_RANK_SECOND_HALF'));
$lists['type']=$type_array;
unset($type_array);
			
//echo 'predictionGame -> <pre>'.print_r($predictionGame,true).'</pre><br>';
//echo 'predictionMember -> <pre>'.print_r($predictionMember,true).'</pre><br>';
//echo 'predictionProjectS -> <pre>'.print_r($predictionProjectS,true).'</pre><br>';
//echo 'actJoomlaUser -> <pre>'.print_r($actJoomlaUser,true).'</pre><br>';

require(JModuleHelper::getLayoutPath('mod_sportsmanagement_top_tipper'));
?>