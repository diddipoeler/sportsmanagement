<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage roster
 * @file       default_picture.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

//echo 'team <pre>'.print_r($this->team,true).'</pre>';
//echo 'projectteam<pre>'.print_r($this->projectteam,true).'</pre>';

?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="roster">
	<?php
	// Show team-picture if defined.
	if ($this->config['show_team_logo'])
	{
		?>
        <table class="table" id="tableteampicture" width="100%">
            <tr>
                <td class="" width="">
					<?php

					$picture = $this->projectteam->picture;

					if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("team")))
					{
						$picture = $this->team->picture;
					}

					$imgTitle = Text::sprintf('COM_SPORTSMANAGEMENT_ROSTER_PICTURE_TEAM', $this->team->name);

					echo sportsmanagementHelperHtml::getBootstrapModalImage(
						'roster' . $this->team->name,
						$picture,
						$this->team->name,
						$this->config['team_picture_height'],
						'',
						$this->modalwidth,
						$this->modalheight,
						$this->overallconfig['use_jquery_modal']
					);
					?>
                </td>
                <td class="" width="">
                <?php
					echo sportsmanagementHelperHtml::getBootstrapModalImage(
						'rosterclub' . $this->team->name,
						$this->team->logo_big,
						$this->team->name,
						$this->config['club_picture_height'],
						'',
						$this->modalwidth,
						$this->modalheight,
						$this->overallconfig['use_jquery_modal']
					);
					?>
                </td>
            </tr>
        </table>
		<?php
	}
	?>
</div>
