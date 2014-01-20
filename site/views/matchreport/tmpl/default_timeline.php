<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>
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