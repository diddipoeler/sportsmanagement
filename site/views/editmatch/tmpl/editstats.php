<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      editstats.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage editmatch
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

$params = $this->form->getFieldsets('params');

?>

<form  action="<?php echo $this->uri->toString(); ?>" id='adminform' method='post' style='display:inline' name='adminform' >
	<div id="jlstatsform">
	<fieldset>
		<div class="fltrt">
<button type="button" onclick="Joomla.submitform('editmatch.cancel', this.form);">
						<?php echo Text::_('JCANCEL');?></button>        
<!--
					<button type="button" onclick="Joomla.submitform('editmatch.save', this.form);">
						<?php echo Text::_('JSAVE');?></button>
                        -->

<!--
<input type='submit' name='save' value='<?php echo Text::_('JSAVE' );?>' />
-->

				</div>
        
		<div class="configuration" >
			Stats
		</div>
	</fieldset>
	<div class="clear"></div>
		<?php

if(version_compare(JVERSION,'3.0.0','ge')) 
{
?>    
<ul class="nav nav-tabs">
<li class="active"><a data-toggle="tab" href="#home"><?php echo Text::_($this->teams->team1);?></a></li>
<li><a data-toggle="tab" href="#menu1"><?php echo Text::_($this->teams->team2);?></a></li>
</ul>    

<div class="tab-content">
<div id="home" class="tab-pane fade in active"> 
<?PHP
echo $this->loadTemplate('home');
?>
</div>
<div id="menu1" class="tab-pane fade">
<?PHP
echo $this->loadTemplate('away');
?>
</div>

    
<?PHP    
}
else
{        
		echo HTMLHelper::_('tabs.start','tabs', array('useCookie'=>1));
		echo HTMLHelper::_('tabs.panel',Text::_($this->teams->team1), 'panel1');
		echo $this->loadTemplate('home');
		
		echo HTMLHelper::_('tabs.panel',Text::_($this->teams->team2), 'panel2');
		echo $this->loadTemplate('away');
		
		echo HTMLHelper::_('tabs.end');
}        
		?>
<input type='hidden' name='option' value='com_sportsmanagement' />		
<input type="hidden" name="view" value="" />
<input type="hidden" name="close" id="close" value="0" />
<input type="hidden" name="task" id="" value="" />
<input type="hidden" name="project_id"	value="<?php echo $this->project_id; ?>" />
<input type="hidden" name="id"	value="<?php echo $this->item->id; ?>" />
<input type="hidden" name="match_id"	value="<?php echo $this->item->id; ?>" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo HTMLHelper::_( 'form.token' ); ?>
</div>
</form>
<div style="clear: both"></div>
