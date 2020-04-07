<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_article.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage matchreport
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<!-- START of match summary -->
<div class="<?php echo $this->divclassrow;?> table-responsive" id="matchreport">
<?php
if (!empty($this->match_article->introtext))
{
	?>
	<table class="table " >
		<tr>
			<td class="contentheading">
				<?php
				echo '&nbsp;' . Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_ARTICLE');
				?>
			</td>
		</tr>
	</table>
	<table class="table " >
		<tr>
			<td>
	<?php
	$summary = $this->match_article->introtext;
	$summary = HTMLHelper::_('content.prepare', $summary);

	echo $summary;

	?>
			</td>
		</tr>
	</table>
	<?php
}

?>
<!-- END of match summary -->
</div>
