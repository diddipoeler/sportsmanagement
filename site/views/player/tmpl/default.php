<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');

//echo 'player view games<pre>',print_r($this->config,true),'</pre><br>'; 

if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{

$my_text = 'player view games <pre>'.print_r($this->games,true).'</pre>';
$my_text .= 'player view teams <pre>'.print_r($this->teams,true).'</pre>';   
$my_text .= 'player view person_position <pre>'.print_r($this->person_position,true).'</pre>';       
$my_text .= 'player view person_parent_positions <pre>'.print_r($this->person_parent_positions,true).'</pre>';
$my_text .= 'stats <br><pre>'.print_r($this->stats,true).'</pre>';
$my_text .= 'gamesstats <br><pre>'.print_r($this->gamesstats,true).'</pre>'; 
$my_text .= 'config <br><pre>'.print_r($this->config,true).'</pre>';
$my_text .= 'historyPlayer <br><pre>'.print_r($this->historyPlayer,true).'</pre>'; 

$my_text .= 'person_position <pre>'.print_r($this->person_position,true).'</pre>';
$my_text .= 'person_parent_positions <pre>'.print_r($this->person_parent_positions,true).'</pre>';
$my_text .= 'position_name <pre>'.print_r($this->teamPlayer->position_name,true).'</pre>';
   
sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,'sportsmanagementViewPlayerdefault',__LINE__,$my_text);
        
}


// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
if (isset($this->person))
{
	?>

<div class="<?php echo COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS; ?>">
	<?php
    if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
{
echo $this->loadTemplate('debug');
}
	echo $this->loadTemplate('projectheading');

	if ( $this->config['show_sectionheader'] )
	{
		echo $this->loadTemplate('sectionheader');
	}

	// Person view START
    
	$this->output = array();

    echo $this->loadTemplate('info');
    
    if ( $this->config['show_playfield'] )
	{
        $this->output[intval($this->config['show_order_playfield'])] = array('text'=>'COM_SPORTSMANAGEMENT_PERSON_PLAYFIELD','template'=>'playfield');
	}
    
    if ( $this->config['show_extra_fields'] )
	{
        $this->output[intval($this->config['show_order_extra_fields'])] = array('text'=>'COM_SPORTSMANAGEMENT_TABS_EXTRA_FIELDS','template'=>'extrafields');
	}
    
    if ( $this->config['show_extended'] && $this->hasExtendedData )
	{
        $this->output[intval($this->config['show_order_extended'])] = array('text'=>'COM_SPORTSMANAGEMENT_TABS_EXTENDED','template'=>'extended');
	}
    
	if ( $this->config['show_plstatus'] && $this->hasStatus )
	{
        $this->output[intval($this->config['show_order_plstatus'])] = array('text'=>'COM_SPORTSMANAGEMENT_PERSON_STATUS','template'=>'status');
	}
    
	if ( $this->config['show_description'] && !empty($this->hasDescription) )
	{
        $this->output[intval($this->config['show_order_description'])] = array('text'=>'COM_SPORTSMANAGEMENT_PERSON_INFO','template'=>'description');
	}
    
	if ( $this->config['show_gameshistory'] && count($this->games) )
	{
        $this->output[intval($this->config['show_order_gameshistory'])] = array('text'=>'COM_SPORTSMANAGEMENT_PERSON_GAMES_HISTORY','template'=>'gameshistory');
	}
    
	if ( $this->config['show_plstats'] )
	{
        $this->output[intval($this->config['show_order_plstats'])] = array('text'=>'COM_SPORTSMANAGEMENT_PERSON_PERSONAL_STATISTICS','template'=>'playerstats');
	}
    
	if ( $this->config['show_plcareer'] && count($this->historyPlayer) > 0 )
	{
        $this->output[intval($this->config['show_order_plcareer'])] = array('text'=>'COM_SPORTSMANAGEMENT_PERSON_PLAYING_CAREER','template'=>'playercareer');
	}
    
	if ( $this->config['show_stcareer'] && count($this->historyPlayerStaff) > 0 )
	{
        $this->output[intval($this->config['show_order_stcareer'])] = array('text'=>'COM_SPORTSMANAGEMENT_PERSON_STAFF_CAREER','template'=>'playerstaffcareer');
	}

 
 /**
 * das array muss noch sortiert werden, sonst macht
 *  die user vorgabe keinen sinn
 */
 ksort($this->output);   
 
    echo $this->loadTemplate($this->config['show_players_layout']);
   
?>
<div>
<?PHP    
	echo $this->loadTemplate('backbutton');
	echo $this->loadTemplate('footer');
?>
</div>
<?PHP 

	//fixxme: had a domready Calendar.setup error on my local site
	echo "<script>";
	echo "Calendar={};";
	echo "</script>";
	?>
</div>
<!-- player -->
</div>
<?php
}
else
{
?>
<div class="alert alert-error">
<h4>
<?php
echo JText::_('COM_SPORTSMANAGEMENT_ERROR');
?>
</h4>
<?php
echo JText::_('COM_SPORTSMANAGEMENT_PERSON_NO_SELECTED');
?>
</div>
<?php
}
?>