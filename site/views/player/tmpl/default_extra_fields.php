<?php defined('_JEXEC') or die('Restricted access'); ?>

<table width="100%" class="contentpaneopen">
	<tr>
		<td class="contentheading"><?php echo '&nbsp;' . JText::_('COM_SPORTSMANAGEMENT_EXTRA_FIELDS'); ?>
		</td>
	</tr>
</table>

<table>
<tbody>
<?php
if ( $this->extrafields )
{
foreach ($this->extrafields as $field)
{
$value = $field->fvalue;
if (!empty($value)) // && !$field->backendonly)
{
?>
<tr>
<td class="label"><?php echo $field->name; ?></td>
<td class="data"><?php echo $field->fvalue; ?></td>
<tr>
<?php
}
}
}
?>
</tbody>
</table>