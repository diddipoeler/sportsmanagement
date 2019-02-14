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
//jimport('joomla.html.pane');
$params = $this->form->getFieldsets('params');

?>

<form name="adminForm" id="adminForm" method="post" action="<?php echo $this->uri->toString(); ?>">
	<div id="jlstatsform">
	<fieldset>
		<div class="fltrt">
<button type="button" onclick="Joomla.submitform('editmatch.cancel', this.form);">
						<?php echo Text::_('JCANCEL');?></button>        
<!--
					<button type="button" onclick="Joomla.submitform('editmatch.save', this.form);">
						<?php echo Text::_('JSAVE');?></button>
                        -->



				</div>
        
<div class="configuration" >

</div>
</fieldset>
<div class="clear"></div>
<?php
echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'home'));  
echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'home', Text::_('home', true));
echo $this->loadTemplate('home');
echo HTMLHelper::_('bootstrap.endTab');		
echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'away', Text::_('away', true));
echo $this->loadTemplate('away');
echo HTMLHelper::_('bootstrap.endTab');		
echo HTMLHelper::_('bootstrap.endTabSet');		

  
       
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
