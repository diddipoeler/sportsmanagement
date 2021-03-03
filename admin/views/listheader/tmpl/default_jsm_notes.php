<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage listheader
 * @file       default_jsm_notes.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
$boxicon = 'fas fa-edit fa-2x fa-pull-left';
}
else	
{
$boxicon = 'info-tab note-icon';	
}
?>

<?php
if ( $this->notes )
{
$notes = implode("<br>",$this->notes);  
?>
<!--Note box blau -->
<div class="color-box">
<div class="shadow">
<div class="<?php echo $boxicon;?>" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NOTE'); ?>"><i></i></div>
<div class="note-box">
<p><strong><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NOTE'); ?></strong>
<?php echo $notes; ?>
</p>
</div>
</div>
</div>
<!--Note box blau -->

<?php  
}
