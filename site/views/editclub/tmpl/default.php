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
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
//JFactory::getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR);


HTMLHelper::_('behavior.modal');
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
						<?php echo Text::_('JAPPLY');?></button>
					<button type="button" onclick="Joomla.submitform('editclub.save');">
						<?php echo Text::_('JSAVE');?></button>
					<button id="cancel" type="button" onclick="<?php echo JFactory::getApplication()->input->getBool('refresh', 0) ? 'window.parent.location.href=window.parent.location.href;' : '';?>  window.parent.SqueezeBox.close();">
						<?php echo Text::_('JCANCEL');?></button>
				</div>
			<legend>
      <?php 
      echo Text::sprintf('COM_SPORTSMANAGEMENTE_ADMIN_CLUB_LEGEND_DESC','<i>'.$this->club->name.'</i>'); 
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
 
echo HTMLHelper::_('tabs.start', 'tab_group_id', $options);
 
echo HTMLHelper::_('tabs.panel', Text::_('PANEL_1_TITLE'), 'panel_1_id');
//echo 'Panel 1 content can go here.';
echo $this->loadTemplate('details');
 
echo HTMLHelper::_('tabs.panel', Text::_('PANEL_2_TITLE'), 'panel_2_id');
//echo 'Panel 2 content can go here.';
echo $this->loadTemplate('picture');

echo HTMLHelper::_('tabs.panel', Text::_('PANEL_3_TITLE'), 'panel_3_id');
//echo 'Panel 3 content can go here.';
//echo $this->loadTemplate('extended');
 
echo HTMLHelper::_('tabs.end');

/*
echo HTMLHelper::_('sliders.start');
foreach ($fieldsets as $fieldset) :
if ($fieldset->name == 'details')
{
    echo HTMLHelper::_('sliders.panel', Text::_($fieldset->label), $fieldset->name);
    echo $this->loadTemplate('details');
}
if ($fieldset->name == 'picture')
{
    echo HTMLHelper::_('sliders.panel', Text::_($fieldset->label), $fieldset->name);
    echo $this->loadTemplate('picture');
}
if ($fieldset->name == 'extended')
{
    echo HTMLHelper::_('sliders.panel', Text::_($fieldset->label), $fieldset->name);
    echo $this->loadTemplate('extended');
}
endforeach;
echo HTMLHelper::_('sliders.end');
*/




?>
	<div class="clr"></div>
	<input type="hidden" name="option" value="com_sportsmanagement" />
    <input type="hidden" name="close" id="close" value="0"/>
	<input type="hidden" name="cid" value="<?php echo $this->club->id; ?>" />
    <input type="hidden" name="task" value="editclub.save" />	

	<?php echo HTMLHelper::_('form.token'); ?>
	
</form>
