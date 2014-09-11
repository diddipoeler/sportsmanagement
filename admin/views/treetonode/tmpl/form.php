<?php defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');
jimport('joomla.html.pane');

JToolBarHelper::title(JText::_('COM_JOOMLEAGUE_ADMIN_TREETONODE_TITLE'));

JLToolBarHelper::save('treetonode.save');
JLToolBarHelper::apply('treetonode.apply');
JToolBarHelper::back('Back','index.php?option=com_joomleague&view=treetonodes&task=treetonode.display');
JLToolBarHelper::custom('treetonode.unpublishnode', 'delete.png','delete_f2.png', JText::_( 'COM_JOOMLEAGUE_ADMIN_TREETONODES_UNPUBLISH' ), false);

JToolBarHelper::help('screen.joomleague',true);
?>

<script>
		function submitbutton(pressbutton) {
			var form = $('adminForm');
			if (pressbutton == 'cancel') {
				submitform(pressbutton);
				return;
			}
			submitform(pressbutton);
			return;
		}

</script>

<style type="text/css">
	table.paramlist td.paramlist_key {
		width: 92px;
		text-align: left;
		height: 30px;
	}
</style>

<form action="index.php" method="post" id="adminForm">
	<div class="col50">

<?php
$iPanel = 1;
echo JHtml::_('tabs.start','tabs', array('useCookie'=>1));
echo JHtml::_('tabs.panel', $this->team1->name, 'panel'.$iPanel++);
echo $this->loadTemplate('description');
echo JHtml::_('tabs.end');
?>

		<div class="clr"></div>
		<input type="hidden" name="option"		value="com_joomleague" />
		<input type="hidden" name="id"			value="<?php echo $this->node->id; ?>" />
		<input type="hidden" name="project_id"		value="<?php echo $this->projectws->id; ?>" />
		<input type="hidden" name="task"		value="" />
	</div>
	<?php echo JHtml::_('form.token'); ?>
</form>