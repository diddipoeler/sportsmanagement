<?php
/**
 * Native Joomla 5/6 roster layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="rosterdescription">
	<?php
	/** Show team-description if defined. */
	if (!isset($this->projectteam->notes))
	{
		$description = "";
	}
	else
	{
		$description = $this->projectteam->notes;
	}

	if (trim($description != ""))
	{
		?>
        <br/>
        <table class="table">
            <tr class="sectiontableheader">
                <th>
					<?php
					echo '&nbsp;' . Text::_('COM_SPORTSMANAGEMENT_ROSTER_TEAMINFORMATION');
					?>
                </th>
            </tr>
        </table>

        <table class="table">
            <tr>
                <td>
					<?php
					$description = HTMLHelper::_('content.prepare', $description);
					echo stripslashes($description);
					?>
                </td>
            </tr>
        </table>
		<?php
	}
	?>
    <br/>
</div>  
