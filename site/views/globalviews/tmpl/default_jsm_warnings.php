<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage listheader
 * @file       default_jsm_warnings.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>

<?php
if ( $this->warnings )
{
$warnings = implode("<br>",$this->warnings);  
?>
<!--Warning box rot -->
<div class="color-box">
					<div class="shadow">
						<div class="info-tab warning-icon" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_WARNING'); ?>"><i></i></div>
						<div class="warning-box">
							<p>
                            <!-- <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_WARNING'); ?></strong> -->
                            <?php echo $warnings; ?>
                            
                            </p>
						</div>
					</div>
</div>
<!--Warning box rot -->



<?php  
}