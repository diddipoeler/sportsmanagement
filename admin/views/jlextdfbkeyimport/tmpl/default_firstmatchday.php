<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jlextdfbkeyimport
 * @file       default_firstmatchday.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
$this->tips[] = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_INFO_2');

echo $this->loadTemplate('jsm_notes');
echo $this->loadTemplate('jsm_tips');

$url = 'administrator/components/com_sportsmanagement/assets/images/dfb-key.jpg';
$alt = 'Lmo Logo';
// $attribs['width'] = '170px';
// $attribs['height'] = '26px';
$attribs['align'] = 'left';
$logo             = HTMLHelper::_('image', $url, $alt, $attribs);

?>


<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm">
    <div id="editcell">
        <fieldset class="adminform">
            <legend>
				<?php
				echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_2', $this->dfbteams);
				?>
            </legend>


            <table class='adminlist'>
                <thead>
                <tr>
                    <th><?php echo HTMLHelper::_('image', $url, $alt, $attribs);; ?>

						<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_EDIT_LIST_DFBKEY_HINT11'); ?>
                    </th>
                </tr>
                </thead>
            </table>


            <table class="<?php echo $this->table_data_class; ?>">
                <thead>
                <tr>
                    <th class="title" nowrap="nowrap" style="vertical-align:top; ">
						<?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_3'); ?>
                    </th>
                    <th class="title" nowrap="nowrap" style="vertical-align:top; ">
						<?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_4'); ?>
                    </th>
                    <th class="title" nowrap="nowrap" style="vertical-align:top; ">
						<?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_5'); ?>
                    </th>
                    <th class="title" nowrap="nowrap" style="vertical-align:top; ">
						<?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_6'); ?>
                    </th>
                    <th class="title" nowrap="nowrap" style="vertical-align:top; ">
						<?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_7'); ?>
                    </th>
                    <th class="title" nowrap="nowrap" style="vertical-align:top; ">
						<?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_8'); ?>
                    </th>
                    <th class="title" nowrap="nowrap" style="vertical-align:top; ">
						<?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_9'); ?>
                    </th>
                </tr>
                </thead>

				<?PHP

				$startteamdfb = 0;
				foreach ($this->lists['dfbday'] as $rowdfb)
				{
					echo "<tr><td>" . $rowdfb->schluessel . "</td>";
					echo "<td>" . $rowdfb->spieltag . "</td>";

					$teile = explode(",", $rowdfb->paarung);
					echo "<td>" . $teile[0] . "</td>"; // Teil1
					echo "<td>" . HTMLHelper::_('select.genericlist', $this->lists['projectteams'], 'chooseteam_' . $teile[0], 'class="inputbox" size="1"', 'value', 'text', $startteamdfb) . "</td>";
					echo "<td>" . $teile[1] . "</td>"; // Teil2
					echo "<td>" . HTMLHelper::_('select.genericlist', $this->lists['projectteams'], 'chooseteam_' . $teile[1], 'class="inputbox" size="1"', 'value', 'text', $startteamdfb) . "</td>";

					echo "<td>" . $rowdfb->spielnummer . "</td></tr>";
				}
				?>

            </table>

        </fieldset>
    </div>

    <fieldset class="actions">

    </fieldset>

    <input type="hidden" name="sent" value="1"/>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="projectid" value="<?php echo $this->project_id; ?> "/>
    <input type="hidden" name="divisionid" value="<?php echo $this->division_id; ?> "/>
</form>
<?php

?>
