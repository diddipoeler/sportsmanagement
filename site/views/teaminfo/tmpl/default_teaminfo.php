<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage teaminfo
 * @file       deafult_teaminfo.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

?>
<?PHP
if (!isset($this->team))
{
	Log::add(Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_ERROR'), Log::WARNING, 'jsmerror');
} else
{
?>
<!-- anfang default_teaminfo -->
<div class="<?php echo $this->divclasscontainer; ?>" id="default_teaminfo">
    <div class="<?php echo $this->divclassrow; ?>" id="default_teaminfo_row">
        <!-- anfang default_teaminfo_left -->
        <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6" id="default_teaminfo-left">
			<?PHP
			?>

			<?PHP
			//dynamic object property string
			$pic     = $this->config['show_picture'];
			$picture = $this->team->$pic;
			if (empty($picture))
			{
				$picture = sportsmanagementHelper::getDefaultPlaceholder("team");
			}
			echo sportsmanagementHelperHtml::getBootstrapModalImage(
				'teaminfo' . $this->team->id,
				$picture,
				$this->team->name,
				$this->config['team_picture_width'],
				'',
				$this->modalwidth,
				$this->modalheight,
				$this->overallconfig['use_jquery_modal']
			);

			if ($this->team->cr_projectteam_picture)
			{
				echo Text::sprintf('COM_SPORTSMANAGEMENT_COPYRIGHT_INFO', '<i>' . $this->team->cr_projectteam_picture . '</i>');
			}
			?>
        </div>
        <!-- ende default_teaminfo_left -->
        <!-- anfang default_teaminfo_right -->
        <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6" id="default_teaminfo_right">
			<?php
			if ($this->config['show_club_info'] || $this->config['show_team_info'])
			{
				if ($this->config['show_club_info'])
				{
					if ($this->club->address || $this->club->zipcode || $this->club->location)
					{
						?>
                        <div class="jl_parentContainer">
                        <address>
                        <strong>
							<?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_CLUB_ADDRESS'); ?>
                        </strong>
						<?php
						$addressString = JSMCountries::convertAddressString($this->club->name, $this->club->address, $this->club->state, $this->club->zipcode, $this->club->location, $this->club->country, 'COM_SPORTSMANAGEMENT_TEAMINFO_CLUB_ADDRESS_FORM');

						echo $addressString;
					}
					?>
                    </address>
					<?PHP
					if ($this->club->phone)
					{
						?>
                        <address>
                            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_CLUB_PHONE'); ?></strong>
							<?php echo $this->club->phone; ?>
                        </address>
						<?php
					}
					if ($this->club->fax)
					{
						?>
                        <div class="jl_parentContainer">
                            <span class="clubinfo_listing_item"><strong> <?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_CLUB_FAX'); ?></strong></span>
                            <span class="clubinfo_listing_value"> <?php echo $this->club->fax; ?></span>
                        </div>
						<?php
					}
					if ($this->club->email)
					{
						?>
                        <div class="jl_parentContainer">
                            <span class="clubinfo_listing_item"><strong> <?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_CLUB_EMAIL'); ?></strong></span>
                            <span class="clubinfo_listing_value"> <?php
								$user = Factory::getUser();
								if (($user->id) or (!$this->overallconfig['nospam_email']))
								{
									echo HTMLHelper::link('mailto:' . $this->club->email, $this->club->email);
								}
								else
								{
									echo HTMLHelper::_('email.cloak', $this->club->email);
								}
								?>
                                </span>
                        </div>
						<?php
					}
					if ($this->club)
					{
						?>
                        <address>
                            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_CLUB_NAME'); ?></strong>
							<?php
							$link = sportsmanagementHelperRoute::getClubInfoRoute($this->project->slug, $this->club->slug);
							echo HTMLHelper::link($link, $this->club->name);
							?>
                        </address>
						<?php
					}
					if ($this->club->website)
					{
						?>
                        <address>
                            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_CLUB_SITE'); ?></strong>
							<?php
							if ($this->club->website)
							{
								echo HTMLHelper::link($this->club->website, $this->club->website, array("target" => "_blank"));
							}
							?>
                        </address>
						<?php
					}
					if (isset($this->merge_clubs))
					{
						?>
                        <div class="jl_parentContainer">
                            <fieldset class="adminform">
                                <legend>
									<?php
									echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_MERGE_CLUBS');
									?>
                                </legend>
								<?PHP
								foreach ($this->merge_clubs as $merge_clubs)
								{
									?>
                                    <span class="clubinfo_listing_item"> <?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_CLUB_NAME'); ?></span>
                                    <span class="clubinfo_listing_value"> <?php
										$link = sportsmanagementHelperRoute::getClubInfoRoute($this->project->slug, $merge_clubs->slug);
										echo HTMLHelper::link($link, $merge_clubs->name);
										?>
                                        </span>
									<?PHP
								}
								?>
                            </fieldset>
                        </div>
						<?php
					}
				}

				if ($this->config['show_team_info'])
				{
					?>
                    <address>
                        <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TEAM_NAME'); ?></strong>
						<?php
						$routeparameter                       = array();
						$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
						$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
						$routeparameter['p']                  = $this->project->slug;
						$routeparameter['tid']                = $this->team->slug;
						$routeparameter['ptid']               = Factory::getApplication()->input->getInt('ptid', 0);
						$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', $routeparameter);
						echo HTMLHelper::link($link, $this->team->tname);
						?>
                    </address>
                    <address>
                        <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TEAM_NAME_SHORT'); ?></strong>
						<?php
						$routeparameter                       = array();
						$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
						$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
						$routeparameter['p']                  = $this->project->slug;
						$routeparameter['tid']                = $this->team->slug;

						$link = sportsmanagementHelperRoute::getSportsmanagementRoute('teamstats', $routeparameter);
						echo HTMLHelper::link($link, $this->team->short_name);
						?>
                    </address>
					<?php
					if ($this->team->info)
					{
						?>
                        <address>
                            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_INFO'); ?></strong>
							<?php
							echo $this->team->info;
							?>
                        </address>
						<?php
					}
					if ($this->team->team_website)
					{
						?>
                        <address>
                            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TEAM_SITE'); ?></strong>
							<?php
							if ($this->team->team_website)
							{
								echo HTMLHelper::link($this->team->team_website, $this->team->team_website, array("target" => "_blank"));
							}
							?>
                        </address>
						<?php
					}
					if ($this->team->team_email)
					{
						?>
                        <address>
                            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TEAM_EMAIL'); ?></strong>
							<?php
							$user = Factory::getUser();
							if (($user->id) or (!$this->overallconfig['nospam_email']))
							{
								echo HTMLHelper::link('mailto:' . $this->team->team_email, $this->team->team_email);
							}
							else
							{
								echo HTMLHelper::_('email.cloak', $this->team->team_email);
							}
							?>
                        </address>
						<?php
					}
				}
				?>
                </div>
                <br/>
				<?php
			}
			}
			?>
            <!-- ende default_teaminfo_right -->
        </div>
        <!-- ende default_teaminfo -->
    </div>
</div>
<!-- ende default_teaminfo -->
