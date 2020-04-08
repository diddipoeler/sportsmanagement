<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage matrix
 * @file       default_hint.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
?>
<div class="<?php echo $this->divclassrow;?> table-responsive" id="hint">
<table class="matrix">
<tr>
<td align="left"><br /> <?php echo Text :: _('COM_SPORTSMANAGEMENT_MATRIX_HINT');?>
</td>
</tr>
</table>
</div>
