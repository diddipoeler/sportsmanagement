<?php
/**
 * @version $Id$
 * @package Joomleague
 * @subpackage pl_birthday
 * @copyright Copyright (C) 2009  JoomLeague
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see _joomleague_license.txt
 */

// <url addpath="/administrator/components/com_sportsmanagement/elements">

// no direct access
defined('_JEXEC') or die('Restricted access');
$database =& JFactory::getDBO();
$option='com_sportsmanagement';

// funktionen einbinden
require_once ( JPATH_SITE.DS.'components'.DS.$option.DS.'models'.DS.'predictionranking.php' );
require_once ( JPATH_SITE.DS.'components'.DS.$option.DS.'helpers'.DS.'route.php' );

// $modelpg = &JLGModel::getInstance('PredictionRanking', 'JoomleagueModel');
// $predictionGame[] = $modelpg->getPredictionGame();
// echo 'prediction game -> <pre>'.print_r($predictionGame).'</pre><br>';
		
?>