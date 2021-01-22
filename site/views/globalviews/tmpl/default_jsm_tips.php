<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage listheader
 * @file       default_jsm_tips.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>

<?php
if ( $this->tips )
{
$tips = implode("<br>",$this->tips);  
?>
<!--Tip Box grün -->
<div class="color-box">
					<div class="shadow">
						<div class="info-tab tip-icon" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_TIP'); ?>"><i></i></div>
						<div class="tip-box">
							<p>
                            <!-- <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_TIP'); ?></strong> -->
                            <?php echo $tips; ?>
                            
                            </p>
						</div>
					</div>
</div>
<!--Tip Box grün -->

<?php  
}