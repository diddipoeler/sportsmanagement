<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage matchreport
 * @file       default_result.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;

?>
<!-- START: game result -->
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="matchreport-result">
    <table class="table">
		<?php
		if ($this->config['show_team_logo'])
		{
			?>
            <tr>
                <td class="teamlogo">
					<?php
					// Dynamic object property string
					$pic = $this->config['show_picture'];

					if (!File::exists(JPATH_SITE . DIRECTORY_SEPARATOR . $this->team1->$pic))
					{
						$picture = sportsmanagementHelper::getDefaultPlaceholder("team");
					}
					else
					{
						$picture = $this->team1->$pic;
					}

					?>

					<?PHP
					echo sportsmanagementHelperHtml::getBootstrapModalImage(
						'team2mare' . $this->team1->id,
						$picture,
						$this->team1->name,
						$this->config['team_picture_width'],
						'',
						$this->modalwidth,
						$this->modalheight,
						$this->overallconfig['use_jquery_modal']
					);

					?>

                </td>
                <td>
                </td>
                <td>
                </td>
                <td>
                </td>
                <td class="teamlogo">
					<?php
					if (!File::exists(JPATH_SITE . DIRECTORY_SEPARATOR . $this->team2->$pic))
					{
						$picture = sportsmanagementHelper::getDefaultPlaceholder("team");
					}
					else
					{
						$picture = $this->team2->$pic;
					}

					echo sportsmanagementHelperHtml::getBootstrapModalImage(
						'team2mare' . $this->team2->id,
						$picture,
						$this->team2->name,
						$this->config['team_picture_width'],
						'',
						$this->modalwidth,
						$this->modalheight,
						$this->overallconfig['use_jquery_modal']
					);

					?>


                </td>
            </tr>

			<?php
		} // End team logo
		?>

        <tr>
            <td class="team">
				<?php
				if ($this->config['names'] == "short_name")
				{
					echo $this->team1->short_name;
				}


				if ($this->config['names'] == "middle_name")
				{
					echo $this->team1->middle_name;
				}


				if ($this->config['names'] == "name")
				{
					echo $this->team1->name;
				}
				?>
            </td>
            <td class="resulthome">
				<?php echo $this->showMatchresult($this->match->alt_decision, 1); ?>
            </td>
            <td>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_VS') ?>
            </td>
            <td class="resultaway">
				<?php echo $this->showMatchresult($this->match->alt_decision, 2); ?>
            </td>
            <td class="team">
				<?php
				if ($this->config['names'] == "short_name")
				{
					echo $this->team2->short_name;
				}


				if ($this->config['names'] == "middle_name")
				{
					echo $this->team2->middle_name;
				}


				if ($this->config['names'] == "name")
				{
					echo $this->team2->name;
				}
				?>
            </td>
        </tr>
		<?php
		if ($this->config['show_period_result'])
		{
			if ($this->showLegresult())
			{
				?>
                <tr>
                    <td>
                    </td>
                    <td class="legshome">
						<?php echo $this->showLegresult(1); ?>
                    </td>
                    <td>
						<?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_VS') ?>
                    </td>
                    <td class="legsaway">
						<?php echo $this->showLegresult(2); ?>
                    </td>
                    <td>
                    </td>
                </tr>
				<?php
			}
		}

		/**
		 * legs anzeigen
		 */
		if ($this->match->team1_legs)
		{
			?>
            <tr>
                <td>
                </td>
                <td class="legshome">
					<?php echo $this->match->team1_legs; ?>
                </td>
                <td>
					<?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_VS') ?>
                </td>
                <td class="legsaway">
					<?php echo $this->match->team2_legs; ?>
                </td>
                <td>
                </td>
            </tr>
			<?php
		}

		/**
		 * details anzeigen
		 */
		if ($this->match->match_result_detail)
		{
			?>
            <tr>
                <td colspan="5" class="match_result_detail">
					<?php echo $this->match->match_result_detail; ?>
                </td>
            </tr>
			<?php
		}
		?>

    </table>

	<?php
	if ($this->match->cancel > 0)
	{
		?>
        <table class="table">
            <tr>
                <td class="result">
					<?php echo $this->match->cancel_reason; ?>
                </td>
            </tr>
        </table>
		<?php
	}
	else
	{
		?>
        <table class="table">


			<?php

			if ($this->config['show_overtime_result'])
			{
				if ($this->showOvertimeResult())
				{
					?>
                    <tr>
                        <td class="legs" colspan="2">
							<?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_OVERTIME');
							echo " " . $this->showOvertimeresult(); ?>
                        </td>
                    </tr>
					<?php
				}
			}

			if ($this->config['show_shotout_result'])
			{
				if ($this->showShotoutResult())
				{
					?>
                    <tr>
                        <td class="legs" colspan="2">
							<?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SHOOTOUT');
							echo " " . $this->showShotoutResult(); ?>
                        </td>
                    </tr>
					<?php
				}
			}

			?>
        </table>
		<?php
	}

	if ($this->config['show_timeline'] && $this->config['show_timeline_under_results'])
	{
		echo $this->loadTemplate('timeline');
	}
	?>

    <!-- START of decision info -->
	<?php
	if ($this->match->decision_info != '')
	{
		?>
        <table class="table">
            <tr>
                <td>
                    <i><?php echo $this->match->decision_info; ?></i>
                </td>
            </tr>
        </table>

		<?php
	}
	?>
    <!-- END of decision info -->
</div>
<!-- END: game result -->
