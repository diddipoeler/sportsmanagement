<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_selectround.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage resultsranking
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\HTML\HTMLHelper;
?>
<div class="<?php echo $this->divclassrow;?>" id="selectround">
<?php
echo HTMLHelper::_('select.genericlist', $this->matchdaysoptions, 'select-round', 'onchange="sportsmanagement_changedoc(this);" style="float:right;"', 'value', 'text', $this->currenturl);
?>
</div>
