<?php 
defined( '_JEXEC' ) or die( 'Restricted access' ); 
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
?>

<form name="adminForm" id="adminForm" method="post"
	action="<?php echo $this->action;?>">
<table>
	<tr>
	<?php
	echo "<td>".HTMLHelper::_('select.genericlist', $this->lists['type'], 'type' , 'class="inputbox" size="1"', 'value', 'text', $this->type )."</td>";
	echo "<td>".HTMLHelper::_('select.genericlist', $this->lists['frommatchday'], 'from' , 'class="inputbox" size="1"', 'value' ,'text' , $this->from )."</td>";
	echo "<td>".HTMLHelper::_('select.genericlist', $this->lists['tomatchday'], 'to' , 'class="inputbox" size="1"', 'value', 'text', $this->to )."</td>";

	?>
		<td><input type="submit" class="button" name="reload View"
			value="<?php echo Text::_('COM_SPORTSMANAGEMENT_RANKING_FILTER'); ?>"></td>
	</tr>
</table>
	<?php echo HTMLHelper::_( 'form.token' ); ?></form>
<br />

