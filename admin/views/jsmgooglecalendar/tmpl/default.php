<?php


defined('_JEXEC') or die();

JFactory::getDocument()->addStyleSheet('components/com_sportsmanagement/views/jsmgooglecalendar/tmpl/default.css');
?>
<div style="width:500px;">
<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_WELCOME'); ?></h2>
<p>
<?php echo JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_INTRO'); ?>
</p>
<br>

<div id="cpanel" style="float:left">
    <div style="float:left;margin-right: 20px">
            <div class="icon">
                <a href="index.php?option=com_sportsmanagement&view=jsmgcalendars" >
                <img src="<?php echo JURI::base(true);?>/../administrator/components/com_sportsmanagement/assets/images/48-calendar.png" height="50px" width="50px">
                <span><?php echo JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_GCALENDARS'); ?></span>
                </a>
            </div>
            <div class="icon">
                <a href="index.php?option=com_sportsmanagement&view=jsmgcalendarimport&layout=login" >
                <img src="<?php echo JURI::base(true);?>/../administrator/components/com_sportsmanagement/assets/images/admin/import.png" height="50px" width="50px">
                <span><?php echo JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_IMPORT'); ?></span>
                </a>
            </div>
            <div class="icon">
                <a href="index.php?option=com_sportsmanagement&view=jsmgcalendar&layout=edit" >
                <img src="<?php echo JURI::base(true);?>/../administrator/components/com_sportsmanagement/assets/images/admin/add.png" height="50px" width="50px">
                <span><?php echo JText::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_ADD'); ?></span>
                </a>
            </div>
            
            
    </div>
</div>
</div>
