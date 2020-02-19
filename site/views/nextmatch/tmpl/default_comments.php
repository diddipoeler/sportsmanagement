<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_preview.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage nextmatch
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * workaround to support {jcomments (off|lock)} in match preview
 * no comments are shown if {jcomments (off|lock)} is found in the match preview
 */
$commentsDisabled = 0;

if (!empty($this->match->preview) && preg_match('/{jcomments\s+(off|lock)}/is', $this->match->preview)) {
    $commentsDisabled = 1;
}

// Comments integration
if (!$commentsDisabled)
{ ?>
	<!-- START of match comments -->
	<div class="<?php echo $this->divclassrow;?> table-responsive" id="nextmatch-comments">
		<?php
	$commmentsInstance = sportsmanagementModelComments::CreateInstance($this->config);
	echo $commmentsInstance->showMatchComments($this->match, $this->teams[0], $this->teams[1], $this->config, $this->project);
		?>
	</div>
	<?php
}
?>
