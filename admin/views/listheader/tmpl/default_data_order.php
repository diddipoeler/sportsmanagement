<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage listheader
 * @file       default_data_order.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

?>

 <?php if ($this->saveOrder) : ?>
                	<?php if ($this->sortDirection == 'asc') : ?>
								<span><?php echo $this->pagination->orderUpIcon($i, $row->ordering - 1, 'agegroups.orderup', 'JLIB_HTML_MOVE_UP', $this->ordering); ?></span>
								<span><?php echo $this->pagination->orderDownIcon($i, $n, $row->ordering + 1, 'agegroups.orderdown', 'JLIB_HTML_MOVE_DOWN', $this->ordering); ?></span>
							<?php elseif ($this->sortDirection == 'desc') : ?>
								<span><?php echo $this->pagination->orderUpIcon($i, $row->ordering - 1, 'agegroups.orderdown', 'JLIB_HTML_MOVE_UP', $this->ordering); ?></span>
								<span><?php echo $this->pagination->orderDownIcon($i, $n, $row->ordering + 1, 'agegroups.orderup', 'JLIB_HTML_MOVE_DOWN', $this->ordering); ?></span>
							<?php endif; ?>
                <?php endif; ?>
                        <?php $disabled = $this->saveOrder ? '' : 'disabled="disabled"';?>
                    <input type="text" name="order[]" size="5"
                           value="<?php echo $row->ordering; ?>" <?php echo $disabled; ?>
                           class="form-control form-control-inline" style="text-align: center"/>
                           <?php
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
$iconClass = '';
if (!$this->saveOrder)
{
$iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
}
?>
<span class="sortable-handler <?php echo $iconClass ?>">
<span class="fas fa-ellipsis-v" aria-hidden="true"></span>
</span>
<?php    
}    
?>