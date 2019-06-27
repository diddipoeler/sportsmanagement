<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_description.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage referee
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
?>
<!-- Person description START -->
<?php
	$description = "";
	if ($this->referee)
	{
		if ( $this->referee->prnotes != '' )
		{
			$description = $this->referee->prnotes;
		}
		elseif ( $this->referee->notes != '' )
		{
			$description = $this->referee->notes;
		}
	}

	if ( $description != '' )
	{
		?>
		<h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_INFO'); ?></h2>
<div class="<?php echo $this->divclassrow;?> table-responsive" id="referee_description">
		<table class="table">
			<tr>
				<td>
					<?php
					$description = HTMLHelper::_('content.prepare', $description);
					echo stripslashes( $description );
					?>
				</td>
			</tr>
		</table>
</div>
		<br /><br />
		<?php
	}
?>
<!-- Person description END -->
