<?php defined('_JEXEC') or die('Restricted access');
?>
<table class="contentpaneopen">
	<tr>
		<td class="contentheading">
			<?php
			$output='';
			if ((isset($this->division)) && (is_a($this->division,'LeagueDivision')))
			{
				$output .= ' '.$this->division->name.' ';
			}
			if (!empty($this->ptid))
			{
				$output .= ' '.$this->teams[$this->ptid]->name;
			}
			else
			{
				$output .= ' '.$this->project->name;
			}
			echo JText::sprintf('COM_SPORTSMANAGEMENT_TEAMPLAN_PAGE_TITLE',$output);
			?>
		</td>
		<?php
		if($this->config['show_ical_link'])
		{
			?>
			<td class="contentheading" style="text-align: right;">
				<?php
				if (!is_null($this->ptid))
				{
				$link=sportsmanagementHelperRoute::getIcalRoute($this->project->id,$this->teams[$this->ptid]->team_id,null,null);
				$text=JHtml::_('image','administrator/components/com_sportsmanagement/assets/images/calendar.png', JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_ICAL_EXPORT'));
				$attribs	= array('title' => JText::_('COM_SPORTSMANAGEMENT_TEAMPLAN_ICAL_EXPORT'));
				echo JHtml::_('link',$link,$text,$attribs);
				}
				?>
			</td>
			<?php
		}
		?>
	</tr>
</table><br />
