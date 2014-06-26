<?php defined('_JEXEC') or die('Restricted access');
JFactory::getLanguage()->load('com_joomleague', JPATH_ADMINISTRATOR);
//JHTML::_('behavior.tooltip');
//JHTML::_('behavior.modal');
//jimport('joomla.html.pane');

//echo 'club<pre>'.print_r($this->club , true).'</pre><br>';

?>
<form name="adminForm" id="adminForm" method="post" action="index.php">

<?php
		//save and close 
		$close = JRequest::getInt('close',0);
		if($close == 1) {
			?><script>
			window.addEvent('domready', function() {
				$('cancel').onclick();	
			});
			</script>
			<?php 
		}
		?>
        <fieldset>
        <div class="fltrt">
					<button type="button" onclick="Joomla.submitform('editclub.save');">
						<?php echo JText::_('JAPPLY');?></button>
					<button type="button" onclick="$('close').value=1; Joomla.submitform('editclub.save');">
						<?php echo JText::_('JSAVE');?></button>
					<button id="cancel" type="button" onclick="<?php echo JRequest::getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
						<?php echo JText::_('JCANCEL');?></button>
				</div>
			<legend>
      <?php 
      echo JText::sprintf('COM_JOOMLEAGUE_ADMIN_CLUB_LEGEND_DESC','<i>'.$this->club->name.'</i>'); 
      ?>
      </legend>
</fieldset>
			
		
              

<?php


echo JHTML::_('tabs.start','tabs', array('useCookie'=>1));    
echo JHTML::_('tabs.panel',JText::_('COM_JOOMLEAGUE_TABS_DETAILS'), 'panel1');
echo $this->loadTemplate('details');


echo JHTML::_('tabs.panel',JText::_('COM_JOOMLEAGUE_TABS_PICTURE'), 'panel2');
echo $this->loadTemplate('picture');


echo JHTML::_('tabs.panel',JText::_('COM_JOOMLEAGUE_TABS_EXTENDED'), 'panel3');
echo $this->loadTemplate('extended');

echo JHTML::_('tabs.end');

?>
	<div class="clr"></div>
	<input type="hidden" name="option" value="com_joomleague" />
    <input type="hidden" name="close" id="close" value="0"/>
	<input type="hidden" name="cid" value="<?php echo $this->club->id; ?>" />
    <input type="hidden" name="task" value="editclub.save" />	

	<?php echo JHTML::_('form.token'); ?>
	
</form>