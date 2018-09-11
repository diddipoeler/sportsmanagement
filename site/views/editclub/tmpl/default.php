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

//echo ' club<br><pre>'.print_r($this->item,true).'</pre>';

//JFactory::getLanguage()->load('com_sportsmanagement', JPATH_ADMINISTRATOR);


//HTMLHelper::_('behavior.modal');
//jimport('joomla.html.pane');
//jimport('joomla.html.html.tabs' );

//echo 'form<pre>'.print_r($this->form , true).'</pre><br>';
//echo 'club<pre>'.print_r($this->club , true).'</pre><br>';

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();

?>
<form name="adminForm" id="adminForm" method="post" action="<?php echo $this->uri->toString(); ?>">

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
					<button type="button" onclick="Joomla.submitform('editclub.apply', this.form);">
						<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SAVE');?></button>
					<button type="button" onclick="Joomla.submitform('editclub.save', this.form);">
						<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SAVECLOSE');?></button>
					<button type="button" onclick="Joomla.submitform('editclub.cancel', this.form);">
<?php echo Text::_('JCANCEL');?></button>
				</div>
			<legend>
      <?php 
      echo Text::sprintf('COM_SPORTSMANAGEMENTE_ADMIN_CLUB_LEGEND_DESC','<i>'.$this->item->name.'</i>'); 
      ?>
      </legend>
</fieldset>
			
		
              

<?php

echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'details'));    
foreach ($fieldsets as $fieldset) :

switch ( $fieldset->name )
{
case 'details':
//case 'picture':
case 'extended':
echo HTMLHelper::_('bootstrap.addTab', 'myTab', $fieldset->name, Text::_($fieldset->label, true));
echo $this->loadTemplate($fieldset->name);
echo HTMLHelper::_('bootstrap.endTab');
break;    
}

endforeach; 

echo HTMLHelper::_('bootstrap.endTabSet');



/*
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
*/


?>
<div class="clr"></div>
<input type="hidden" name="option" value="com_sportsmanagement" />
<input type="hidden" name="close" id="close" value="0"/>
<input type="hidden" name="cid" value="<?php echo $this->club->id; ?>" />
<input type="hidden" name="task" value="" />	
<?php echo HTMLHelper::_('form.token'); ?>
</form>
