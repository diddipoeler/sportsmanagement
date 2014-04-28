<?php



//no direct access
defined('_JEXEC') or die('Restricted access');

// welche tabelle soll genutzt werden
$paramscomponent = JComponentHelper::getParams( 'com_sportsmanagement' );
$database_table	= $paramscomponent->get( 'cfg_which_database_table' );
DEFINE( 'COM_SPORTSMANAGEMENT_TABLE',$database_table );

//get helper
require_once (dirname(__FILE__).DS.'helper.php');



$list = modJSMRankingHelper::getData($params);

$document = JFactory::getDocument();
//add css file
$document->addStyleSheet(JUri::base().'modules/mod_sportsmanagement_ranking/css/mod_sportsmanagement_ranking.css');

require(JModuleHelper::getLayoutPath('mod_sportsmanagement_ranking'));