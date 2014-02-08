<?php 
defined('_JEXEC') or die('Restricted access');

$templatesToLoad = array('footer');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">

<fieldset class="adminform">
<legend><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_EXT_IMAGES_IMPORT'); ?></legend>

<table>
<thead>
				<tr>
					<th width="5"><?php echo JText::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
					<th width="20">
						<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
					</th>
                    <th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_IMAGE'); ?>
                    <th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_PATH'); ?>
                    <th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_DIRECTORY'); ?>
                    <th><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMPORT_FILE'); ?>

</tr>
</thead>
                    
<?PHP
$k=0;
for ($i=0,$n=count($this->files); $i < $n; $i++)
{
$row =& $this->files[$i];
$checked = JHtml::_('grid.checkedout',$row,$i);
?>
<tr class="<?php echo "row$k"; ?>">
<td class="center"><?php echo ($i +1); ?></td>
<td class="center"><?php echo $checked; ?></td>
<td><?php echo $row->picture; ?></td>
<input type='hidden' name='picture[<?php echo $i; ?>]' value='<?php echo $row->picture; ?>' />
<td><?php echo $row->folder; ?></td>
<input type='hidden' name='folder[<?php echo $i; ?>]' value='<?php echo $row->folder; ?>' />
<td><?php echo $row->directory; ?></td>
<input type='hidden' name='directory[<?php echo $i; ?>]' value='<?php echo $row->directory; ?>' />
<td><?php echo $row->file; ?></td>
<input type='hidden' name='file[<?php echo $i; ?>]' value='<?php echo $row->file; ?>' />
</tr>
<?php
$k=1 - $k;
}

?>

</table>

</fieldset>

<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
<?php echo JHtml::_('form.token')."\n"; ?>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>  