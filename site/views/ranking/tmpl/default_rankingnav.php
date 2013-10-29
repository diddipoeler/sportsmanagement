<?php defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<form name="adminForm" id="adminForm" method="post"
	action="<?php echo $this->action;?>">
<table>
	<tr>
	<?php
	//echo " [" . $this->startdate. " / " . $this->enddate. "]";

	//echo $this->pagenav.'<br>';
	//echo $this->pagenav2.'<br>';

	//echo JHTML::calendar( $this->startdate, 'startdate', 'startdate', $dateformat );
	//echo " - " . JHTML::calendar( $this->enddate, 'enddate', 'enddate', $dateformat );
	echo "<td>".JHTML::_('select.genericlist', $this->lists['type'], 'type' , 'class="inputbox" size="1"', 'value', 'text', $this->type )."</td>";
	echo "<td>".JHTML::_('select.genericlist', $this->lists['frommatchday'], 'from' , 'class="inputbox" size="1"', 'value' ,'text' , $this->from )."</td>";
	echo "<td>".JHTML::_('select.genericlist', $this->lists['tomatchday'], 'to' , 'class="inputbox" size="1"', 'value', 'text', $this->to )."</td>";

	?>
		<td><input type="submit" class="button" name="reload View"
			value="<?php echo JText::_('COM_JOOMLEAGUE_RANKING_FILTER'); ?>"></td>
	</tr>
</table>
	<?php echo JHTML::_( 'form.token' ); ?></form>
<br />

