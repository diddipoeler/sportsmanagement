<?php
/**
 * SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage listheader
 * @file       default_startinstallhelper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: � 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

$step = $this->jinput->get('step') ? $this->jinput->get('step') : 1;


?>
<section class="content-block" role="main">
<div class="row-fluid">
<div class="span9">
<div class="well well-small">
<div class="module-title nav-header"><?php echo Text::_('COM_SPORTSMANAGEMENT_D_HEADING_INSTALL_TOOLS'); ?>
</div>


<!--Note box blau -->
<div class="color-box">
<div class="shadow">
<div class="info-tab note-icon" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NOTE'); ?>"><i></i></div>
<div class="note-box">
<p><strong><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NOTE'); ?></strong>
<?php 
switch ( $step )
{
case 1:
echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_INSTALLHELPER');
break;
case 2:
echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_INSTALLHELPER_B');
break;     
}
 

?>
</p>
</div>
</div>
</div>
<!--End:Note box-->





<div id="dashboard-icons" class="btn-group">

<a class="btn" href="index.php?option=com_sportsmanagement&view=installhelper&step=<?php echo $step; ?>">
<img src="components/com_sportsmanagement/assets/icons/wizard-<?php echo $step; ?>.png" height="80" width="auto"
alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_INSTALLHELPER_STEP'.$step) ?>"/><br/>
<span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_INSTALLHELPER_STEP'.$step) ?></span>
</a>

</div>                                

</div>
</div>
</div>
</section>