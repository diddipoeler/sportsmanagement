<?php
/**
 * @version	 $Id$
 * @package	 Joomla
 * @subpackage  Joomleague Statistik module
 * @copyright   Copyright (C) 2008 Open Source Matters. All rights reserved.
 * @license	 GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');



/**
 * Statistik Module helper
 *
 * @package Joomla
 * @subpackage Joomleague Statistik module
 * @since		1.0
 */
class modJSMStatistikRekordHelper
{



	/**
	 * Method to get the list
	 *
	 * @access public
	 * @return array
	 */
	function getData($params)
	{
	//global $mainframe;
    $db = JFactory::getDBO();

    //$language=& JFactory::getLanguage(); //get the current language
    //echo 'Current language is: ' . $language->getName() . '<br>';
    //$language->load( 'mod_sportsmanagement_statistik' );
    

if ( $params->get('jsm_stat_spielpaarungen'))
{		
$query  =	'	SELECT count(*) as total
			FROM #__sportsmanagement_match
			';

$db->setQuery( $query );
$anzahl  = $db->loadResult();

$temp  = new stdClass();
$temp->image  = 'modules/mod_sportsmagement_count_rekord/images/matches.png';
//$temp->text  = JText::_( 'SHOW MATCHES DIFF' );

$temp->anzahl  = $anzahl;
$temp->anzahlbis  = $params->get('jsm_stat_paarungen');
$temp->anzahldiff  = $params->get('jsm_stat_paarungen') - $anzahl;
$temp->text  = JText::sprintf( 'SHOW_MATCHES_DIFF',"<strong>".number_format($temp->anzahldiff,0, ",", ".")."</strong>","<strong>".number_format($temp->anzahlbis,0, ",", ".")."</strong>" );
$result[]  = $temp;
$result  = array_merge($result);
unset($temp);
}
		
		
		
		return $result;
		
	}
	
	

	
}