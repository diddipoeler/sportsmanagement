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

defined( '_JEXEC' ) or die( 'Restricted access' ); 
?>
<!-- START of match timeline -->
<style type="text/css">
#timeline-top {
    background-image: url("/images/com_sportsmanagement/database/matchreport/spielfeld_top.png");
    background-repeat: no-repeat;
    background-size: 100% 46px;
    height: 30px;
	
}

#timeline-bottom {
    background-image: url("/images/com_sportsmanagement/database/matchreport/spielfeld_bottom.png");
    background-repeat: no-repeat;
    background-size: 100% 46px;
    height: 30px; 
	vertical-align: baseline;

}
</style>
<script type="text/javascript">
//	window.addEvent('domready', function(){
//		var Tips1 = new Tips($$('.imgzev'));
//	});
    
	function gotoevent(row) {
        var t=document.getElementById('event-' + row)
        t.scrollIntoView()
    }
</script>
<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_TIMELINE'); ?></h2>
<table id="timeline">
	<tr>
  <?php
		if ($this->team1->logo_small == '')
		{
			echo '<td width="140">';
		}
		else
		{
			echo '<td width="40">';
		}
		?>
		</td>
    <td id="" style="">
    <div id="timelinetop" style="position:relative;width:100%;">
    <div id="firsthalftime" style="position:absolute; top:0px; left:0px; width:50%; height:15px;text-align: center;color:#FFFFFF;background-color:lightgrey;">
    <?php echo JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_TIMELINE_FIRST_HALF'); ?>
    </div>
   
    <div id="secondhalftime" style="position:absolute; top:0px; left:50%; width:50%; height:15px;text-align: center;color:#FFFFFF;background-color:grey;">
    <?php echo JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_TIMELINE_SECOND_HALF'); ?>
    </div>
    </div>
    <br>
  </td>
  </tr>
  
	<tr>
		<?php
		if ($this->team1->logo_small == '')
		{
			echo '<td width="140">';
			echo $this->team1->name;
		}
		else
		{
			echo '<td width="40">';
			echo sportsmanagementModelProject::getClubIconHtml($this->team1,1);
		}
		?>
		</td>
		<td id="timeline-top">
			<div id="timelinetop">
			<?php
			echo $this->showSubstitution_Timelines1();
			echo $this->showEvents_Timelines1();
			?>
			</div>
		</td>
	</tr>
	<tr>
		<?php
		if ($this->team2->logo_small == '')
		{
			echo '<td width="140">';
			echo $this->team2->name;
		}
		else
		{
			echo '<td width="40">';
			echo sportsmanagementModelProject::getClubIconHtml($this->team2,1);
		}
		?>
		</td>
		<td id="timeline-bottom">
			<div id="timelinebottom">
			<?php
			echo $this->showSubstitution_Timelines2();
			echo $this->showEvents_Timelines2();
			?>
			</div>
	</tr>

</table>

<!-- END of match timeline -->
