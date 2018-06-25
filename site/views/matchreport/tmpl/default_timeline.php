<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_timeline.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage matchreport
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 
?>
<!-- START of match timeline -->

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
<table id="timeline" class="table table-responsive" >
	<tr>
  <?php
		if ($this->team1->logo_small == '')
		{
			?>
            <td width="140">
            <?php
		}
		else
		{
			?>
            <td width="40">
            <?php
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
			?>
            <td width="140">
            <?php
			echo $this->team1->name;
		}
		else
		{
			?>
            <td width="40">
            <?php
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
			?>
            <td width="140">
            <?php
			echo $this->team2->name;
		}
		else
		{
			?>
            <td width="40">
            <?php
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
