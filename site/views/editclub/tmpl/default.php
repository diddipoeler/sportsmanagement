<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage editclub
 */

defined('_JEXEC') or die('Restricted access');

//JFactory::getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR);

//JHTML::_('behavior.tooltip');
JHtml::_('behavior.modal');
jimport('joomla.html.pane');
jimport('joomla.html.html.tabs' );

//echo 'form<pre>'.print_r($this->form , true).'</pre><br>';
//echo 'club<pre>'.print_r($this->club , true).'</pre><br>';

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();

?>
<form name="adminForm" id="adminForm" method="post" action="index.php">

<?php
		//save and close 
		$close = JFactory::getApplication()->input->getInt('close',0);
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
					<button type="button" onclick="Joomla.submitform('editclub.apply');">
						<?php echo JText::_('JAPPLY');?></button>
					<button type="button" onclick="Joomla.submitform('editclub.save');">
						<?php echo JText::_('JSAVE');?></button>
					<button id="cancel" type="button" onclick="<?php echo JFactory::getApplication()->input->getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
						<?php echo JText::_('JCANCEL');?></button>
				</div>
			<legend>
      <?php 
      echo JText::sprintf('COM_SPORTSMANAGEMENTE_ADMIN_CLUB_LEGEND_DESC','<i>'.$this->club->name.'</i>'); 
      ?>
      </legend>
</fieldset>
			
		
              

<?php

$options = array(
    'onActive' => 'function(title, description){
        description.setStyle("display", "block");
        title.addClass("open").removeClass("closed");
    }',
    'onBackground' => 'function(title, description){
        description.setStyle("display", "none");
        title.addClass("closed").removeClass("open");
    }',
    'startOffset' => 0,  // 0 starts on the first tab, 1 starts the second, etc...
    'useCookie' => true, // this must not be a string. Don't use quotes.
);
 
echo JHtml::_('tabs.start', 'tab_group_id', $options);
 
echo JHtml::_('tabs.panel', JText::_('PANEL_1_TITLE'), 'panel_1_id');
//echo 'Panel 1 content can go here.';
echo $this->loadTemplate('details');
 
echo JHtml::_('tabs.panel', JText::_('PANEL_2_TITLE'), 'panel_2_id');
//echo 'Panel 2 content can go here.';
echo $this->loadTemplate('picture');

echo JHtml::_('tabs.panel', JText::_('PANEL_3_TITLE'), 'panel_3_id');
//echo 'Panel 3 content can go here.';
//echo $this->loadTemplate('extended');
 
echo JHtml::_('tabs.end');

/*
echo JHtml::_('sliders.start');
foreach ($fieldsets as $fieldset) :
if ($fieldset->name == 'details')
{
    echo JHtml::_('sliders.panel', JText::_($fieldset->label), $fieldset->name);
    echo $this->loadTemplate('details');
}
if ($fieldset->name == 'picture')
{
    echo JHtml::_('sliders.panel', JText::_($fieldset->label), $fieldset->name);
    echo $this->loadTemplate('picture');
}
if ($fieldset->name == 'extended')
{
    echo JHtml::_('sliders.panel', JText::_($fieldset->label), $fieldset->name);
    echo $this->loadTemplate('extended');
}
endforeach;
echo JHtml::_('sliders.end');
*/

//echo JHtml::_('tabs.start','tabs', array('useCookie'=>1));    
//echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_DETAILS'), 'panel1');
//echo $this->loadTemplate('details');
//echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_PICTURE'), 'panel2');
//echo $this->loadTemplate('picture');
//echo JHtml::_('tabs.panel',JText::_('COM_SPORTSMANAGEMENT_TABS_EXTENDED'), 'panel3');
//echo $this->loadTemplate('extended');
//echo JHtml::_('tabs.end');


?>
	<div class="clr"></div>
	<input type="hidden" name="option" value="com_sportsmanagement" />
    <input type="hidden" name="close" id="close" value="0"/>
	<input type="hidden" name="cid" value="<?php echo $this->club->id; ?>" />
    <input type="hidden" name="task" value="editclub.save" />	

	<?php echo JHtml::_('form.token'); ?>
	
</form>
