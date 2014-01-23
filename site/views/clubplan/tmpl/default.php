<?php defined('_JEXEC') or die('Restricted access');

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('projectheading', 'backbutton', 'footer');
JoomleagueHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="joomleague">
	<?php
	echo $this->loadTemplate('projectheading');

	if (($this->config['show_sectionheader'])==1 && $this->club)
	{
		echo $this->loadTemplate('sectionheader');
	}

	echo $this->loadTemplate('datenav');
	switch ($this->config['type_matches'])
	{
	case 0 : // All matches
		if (!empty($this->allmatches))
		{
			$tm=count ($this->allmatches);
			echo "<h3>".$tm." ".JText::_('COM_JOOMLEAGUE_CLUBPLAN_MATCHES')."</h3>";
			$this->matches=$this->allmatches;
			echo $this->loadTemplate('matches');//or use matches_sbd (sort by date)
		}
		else
		{
			echo "<h3>".JText::_('COM_JOOMLEAGUE_CLUBPLAN_NO_MATCHES')."</h3><br/>";
		}
		break;
	case 1 : // Home matches
		if (!empty($this->homematches))
		{
			$tm=count ($this->homematches);
			echo "<h3>".$tm." ".JText::_('COM_JOOMLEAGUE_CLUBPLAN_MATCHES')."</h3>";
			$this->matches=$this->homematches;
			echo $this->loadTemplate('matches');//or use matches_sbd (sort by date)
		}
		else
		{
			echo "<h3>".JText::_('COM_JOOMLEAGUE_CLUBPLAN_NO_HOME_MATCHES')."</h3><br/>";
		}
		break;
	case 2 : // Away matches
		if (!empty($this->awaymatches))
		{
			$tm=count ($this->awaymatches);
			echo "<h3>".$tm." ".JText::_('COM_JOOMLEAGUE_CLUBPLAN_MATCHES')."</h3>";
			$this->matches=$this->awaymatches;
			echo $this->loadTemplate('matches');//or use matches_sbd (sort by date)
		}
		else
		{
			echo "<h3>".JText::_('COM_JOOMLEAGUE_CLUBPLAN_NO_AWAY_MATCHES')."</h3><br/>";
		}
		break;
	case 4 : // matches sorted by date
		if (!empty($this->allmatches))
		{
			$tm=count ($this->allmatches);
			echo "<h3>".$tm." ".JText::_('COM_JOOMLEAGUE_CLUBPLAN_MATCHES')."</h3>";
			$this->matches=$this->allmatches;
			echo $this->loadTemplate('matches_sorted_by_date');//or use matches_sbd (sort by date)
		}
		else
		{
			echo "<h3>".JText::_('COM_JOOMLEAGUE_CLUBPLAN_NO_MATCHES')."</h3><br/>";
		}
		break;
	default : // Home+Away matches
		if (!empty($this->homematches))
		{
			$tm=count ($this->homematches);
			if(count($this->awaymatches)==0) {
				echo "<h3>".$tm." ".JText::_('COM_JOOMLEAGUE_CLUBPLAN_MATCHES')."</h3>";
			} else {
				echo "<h3>".$tm." ".JText::_('COM_JOOMLEAGUE_CLUBPLAN_HOME_MATCHES')."</h3>";
			}
			$this->matches=$this->homematches;
			echo $this->loadTemplate('matches');//or use matches_sbd (sort by date)
		}
		else
		{
			echo "<h3>".JText::_('COM_JOOMLEAGUE_CLUBPLAN_NO_HOME_MATCHES')."</h3><br/>";
		}
		if (!empty($this->awaymatches))
		{
			$tm=count ($this->awaymatches);
			if(count($this->homematches)==0) {
				echo "<h3>".$tm." ".JText::_('COM_JOOMLEAGUE_CLUBPLAN_MATCHES')."</h3>";
			} else {
				echo "<h3>".$tm." ".JText::_('COM_JOOMLEAGUE_CLUBPLAN_AWAY_MATCHES')."</h3>";
			}
			$this->matches=$this->awaymatches;
			echo $this->loadTemplate('matches');//or use matches_sbd (sort by date)
		}
		else
		{
			echo "<h3>".JText::_('COM_JOOMLEAGUE_CLUBPLAN_NO_AWAY_MATCHES')."</h3><br/>";
		}
		break;
	}

	echo '<div>';
		echo $this->loadTemplate('backbutton');
		echo $this->loadTemplate('footer');
	echo '</div>';
	?>
</div>
