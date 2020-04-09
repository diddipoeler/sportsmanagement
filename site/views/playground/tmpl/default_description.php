<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage playground
 * @file       default_description.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<?php
if ($this->playground->notes)
{
	?>
    <h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_NOTES'); ?></h2>
    <div class="<?php echo $this->divclassrow; ?> table-responsive" id="playground_description">
		<?php
		$description = $this->playground->notes;
		$description = HTMLHelper::_('content.prepare', $description);
		echo $description;
		?>
    </div>
	<?php
}
