<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage player
 * @file       default_description.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<!-- Team Player Description START -->
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="player">
	<?php
	$description = "";

	if (isset($this->teamPlayer) && !empty($this->teamPlayer->notes))
	{
		$description = $this->teamPlayer->notes;
	}
	else
	{
		if (!empty($this->person->notes))
		{
			$description = $this->person->notes;
		}
	}

	if (!empty($description))
	{
		?>
        <h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_INFO'); ?></h2>
        <div class="personinfo">
			<?php
			$description = HTMLHelper::_('content.prepare', $description);
			echo stripslashes($description);
			?>
        </div>
		<?php
	}
	?>
</div>
<!-- Team Player Description END -->
