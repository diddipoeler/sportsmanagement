<?php 
defined('_JEXEC') or die('Restricted access');

//echo $this->task.'<br>';
//echo '<pre>'.print_r($this->sm_tables,true).'</pre><br>';

?>


    

<p class="nowarning"><?php echo JText::_('COM_JOOMLAUPDATE_VIEW_UPDATE_INPROGRESS') ?></p>
<div class="joomlaupdate_spinner" ></div>

<div id="update-progress">
	<div id="extprogress">
		<div class="extprogrow">
			<?php echo JHtml::_('image', 'media/bar.gif', JText::_('COM_JOOMLAUPDATE_VIEW_PROGRESS'),
					array('class' => 'progress', 'id' => 'progress'), true); ?>
		</div>
		<div class="extprogrow">
			<span class="extlabel"><?php echo JText::_('COM_JOOMLAUPDATE_VIEW_UPDATE_PERCENT'); ?></span>
			<span class="extvalue" id="extpercent"></span>
		</div>
		
		<div class="extprogrow">
			<span class="extlabel"><?php echo JText::_('COM_JOOMLAUPDATE_VIEW_UPDATE_FILESEXTRACTED'); ?></span>
			<span class="extvalue" id="extfiles"></span>
		</div>
	</div>
</div>




<?PHP
echo '<meta http-equiv="refresh" content="5; URL='.$this->request_url.$this->step.'">';
?>  