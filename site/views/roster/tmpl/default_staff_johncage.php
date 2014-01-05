<?php defined('_JEXEC') or die('Restricted access');

// Show team-staff as defined
if (count($this->stafflist) > 0)
{
?>
	<div id="jl_roster_staff_holder">
		<div class="jl_roster_staffheading sectiontableheader">
<?php
				echo '&nbsp;';
				if ($this->config['show_team_shortform'] == 1)
				{
					echo JText::sprintf('COM_JOOMLEAGUE_ROSTER_STAFF_OF2',$this->team->name, $this->team->short_name);
				}
				else
				{
					echo JText::sprintf('COM_JOOMLEAGUE_ROSTER_STAFF_OF',$this->team->name);
				}
?>
		</div><!-- /.jl_roster_staffheading -->
<?php
		for ($i=0, $n=count( $this->stafflist ); $i < $n; $i++) 
		{
			$k = $i % 2;
			if ($k == 0)
			{
?>
			<div class="jl_rosterpersonrow">
<?php
			}
			$row = &$this->stafflist[$i];
			$this->assignRef('row',$row);
			$this->assign('k',$k);
			echo $this->loadTemplate('person_staff');
			if ($k == 1 OR !isset($this->stafflist[$i+1]))
			{
?>
			</div><!-- /.jl_rosterpersonrow -->
<?php
			}
		}
?>
	</div><!-- /.jl_roster_staff_holder -->
<?php
}
?>