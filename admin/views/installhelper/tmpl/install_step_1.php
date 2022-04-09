<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage installhelper
 * @file       install_step_1.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>

<?php
echo $this->loadTemplate('jsm_warnings');
?>

<!--Note box blau -->
<div class="color-box">
<div class="shadow">
<div class="info-tab note-icon" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NOTE') ?>"><i></i></div>
<div class="note-box">
<p><strong><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NOTE') ?></strong>
<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_INSTALLHELPER_0') ?>
</p>
</div>
</div>
</div>
<!--End:Note box-->
               
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
<?PHP

?>

<table class="table">
<tr>
<td class="nowrap" align="right"><?php echo $this->lists['sportstypes'] . '&nbsp;&nbsp;'; ?></td>
<td><button type="button" onclick="Joomla.submitform('installhelper.savesportstype', this.form);">
						<?php echo Text::_('JAPPLY'); ?></button></td>
</tr>
</table>

<input type="hidden" name="task" value=""/>
<input type="hidden" name="boxchecked" value="0"/>
<input type="hidden" name="filter_order" value=""/>
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>"/>

<?php echo HTMLHelper::_('form.token') . "\n"; ?>
</form>
<?PHP echo $this->loadTemplate('footer');?>
