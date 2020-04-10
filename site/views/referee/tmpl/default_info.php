<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage referee
 * @file       default_info.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

?>
<!-- person data START -->
<?php if ($this->referee) { ?>
    <h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_PERSONAL_DATA'); ?></h2>

    <div class="<?php echo $this->divclassrow; ?> table-responsive" id="referee_info">

        <div class="col-md-6">


			<?php
			if ($this->config['show_photo'])
			{
				?>

				<?php
				$picturetext = Text::_('COM_SPORTSMANAGEMENT_PERSON_PICTURE');
				$imgTitle    = Text::sprintf($picturetext, sportsmanagementHelper::formatName(null, $this->referee->firstname, $this->referee->nickname, $this->referee->lastname, $this->config["name_format"]));
				$picture     = $this->referee->picture;
				if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("player")))
				{
					$picture = $this->person->picture;
				}
				if (!curl_init($picture))
				{
					$picture = sportsmanagementHelper::getDefaultPlaceholder("player");
				}

				echo sportsmanagementHelperHtml::getBootstrapModalImage(
					'referee' . $this->referee->id,
					$picture,
					$imgTitle,
					$this->config['picture_width'],
					'',
					$this->modalwidth,
					$this->modalheight,
					$this->overallconfig['use_jquery_modal']
				);

			}
			?>
        </div>

        <div class="col-md-6">

			<?php
			if (!empty($this->person->country) && ($this->config["show_nationality"] == 1))
			{
				?>


                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_NATIONALITY'); ?></strong>
					<?php
					echo JSMCountries::getCountryFlag($this->person->country) . " " .
						Text::_(JSMCountries::getCountryName($this->person->country));
					?>
                </address>

				<?php
			}
			?>

			<?php
			$outputName = Text::sprintf('%1$s %2$s', $this->referee->firstname, $this->referee->lastname);
			if ($this->referee->user_id)
			{
				switch ($this->config['show_user_profile'])
				{
					case 1:     // Link to Joomla Contact Page
						$link       = sportsmanagementHelperRoute::getContactRoute($this->referee->user_id);
						$outputName = HTMLHelper::link($link, $outputName);
						break;

					case 2:     // Link to CBE User Page with support for SportsManagement Tab
						$link       = sportsmanagementHelperRoute::getUserProfileRouteCBE(
							$this->referee->user_id,
							$this->project->id,
							$this->referee->id
						);
						$outputName = HTMLHelper::link($link, $outputName);
						break;

					default:
						break;
				}
			}
			//echo $outputName;
			?>


            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_NAME'); ?></strong>
				<?php
				echo $outputName;
				?>
            </address>

			<?php
			if (!empty($this->referee->nickname))
			{
				?>

                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_NICKNAME'); ?></strong>
					<?php
					echo $this->referee->nickname;
					?>
                </address>


				<?php
			}
			if (($this->config['show_birthday'] > 0)
				&& ($this->config['show_birthday'] < 5)
				&& ($this->referee->birthday != '0000-00-00')
			)
			{
				// $this->config['show_birthday'] = 4;
				?>


				<?php
				switch ($this->config['show_birthday'])
				{
					case     1:            // show Birthday and Age
						$outputStr = 'COM_SPORTSMANAGEMENT_PERSON_BIRTHDAY_AGE';
						break;
					case     2:            // show Only Birthday
						$outputStr = 'COM_SPORTSMANAGEMENT_PERSON_BIRTHDAY';
						break;
					case     3:            // show Only Age
						$outputStr = 'COM_SPORTSMANAGEMENT_PERSON_AGE';
						break;
					case     4:            // show Only Year of birth
						$outputStr = 'COM_SPORTSMANAGEMENT_PERSON_YEAR_OF_BIRTH';
						break;
				}

				?>

				<?php
				switch ($this->config['show_birthday'])
				{
					case 1:     // show Birthday and Age
						$birthdateStr = $this->referee->birthday != "0000-00-00" ?
							HTMLHelper::date($this->referee->birthday, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE')) : "-";
						$birthdateStr .= "&nbsp;(" . sportsmanagementHelper::getAge($this->referee->birthday, $this->referee->deathday) . ")";
						break;
					case 2:     // show Only Birthday
						$birthdateStr = $this->referee->birthday != "0000-00-00" ?
							HTMLHelper::date($this->referee->birthday, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE')) : "-";
						break;
					case 3:     // show Only Age
						$birthdateStr = sportsmanagementHelper::getAge($this->referee->birthday, $this->referee->deathday);
						break;
					case 4:     // show Only Year of birth
						$birthdateStr = $this->referee->birthday != "0000-00-00" ?
							HTMLHelper::date($this->referee->birthday, Text::_('%Y')) : "-";
						break;
					default:
						$birthdateStr = "";
						break;
				}

				?>


                <address>
                    <strong><?php echo Text::_($outputStr); ?></strong>
					<?php echo $birthdateStr; ?>
                </address>

				<?php
			}

			if (($this->referee->address != "") && ($this->config['show_person_address'] == 1))
			{
				?>

                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_ADDRESS'); ?></strong>
					<?php echo Countries::convertAddressString(
						'',
						$this->referee->address,
						$this->referee->state,
						$this->referee->zipcode,
						$this->referee->location,
						$this->referee->address_country,
						'COM_SPORTSMANAGEMENT_PERSON_ADDRESS_FORM'
					); ?>
                </address>


				<?php
			}

			if (($this->referee->phone != "") && ($this->config['show_person_phone'] == 1))
			{
				?>

                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_PHONE'); ?></strong>
					<?php echo $this->referee->phone; ?>
                </address>

				<?php
			}

			if (($this->referee->mobile != "") && ($this->config['show_person_mobile'] == 1))
			{
				?>


                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_MOBILE'); ?></strong>
					<?php echo $this->referee->mobile; ?>
                </address>

				<?php
			}

			if (($this->config['show_person_email'] == 1) && ($this->referee->email != ""))
			{
				?>

                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_EMAIL'); ?></strong>
					<?php
					$user = Factory::getUser();
					if (($user->id) || (!$this->overallconfig['nospam_email']))
					{
						?>
                        <a href="mailto: <?php echo $this->referee->email; ?>">
							<?php
							echo $this->club->email;
							?>
                        </a>
						<?php
					}
					else
					{
						echo HTMLHelper::_('email.cloak', $this->referee->email);
					}
					?>
                </address>


				<?php
			}

			if (($this->referee->website != "") && ($this->config['show_person_website'] == 1))
			{
				?>

                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_WEBSITE'); ?></strong>
					<?php echo HTMLHelper::_(
						'link',
						$this->referee->website,
						$this->referee->website,
						array('target' => '_blank')
					); ?>
                </address>
				<?php
			}

			if (($this->referee->height > 0) && ($this->config['show_person_height'] == 1))
			{
				?>


                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_HEIGHT'); ?></strong>
					<?php echo str_replace("%HEIGHT%", $this->referee->height, Text::_('COM_SPORTSMANAGEMENT_PERSON_HEIGHT_FORM')); ?>
                </address>
				<?php
			}
			if (($this->referee->weight > 0) && ($this->config['show_person_weight'] == 1))
			{
				?>


                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_WEIGHT'); ?></strong>
					<?php echo str_replace("%WEIGHT%", $this->referee->weight, Text::_('COM_SPORTSMANAGEMENT_PERSON_WEIGHT_FORM')); ?>
                </address>
				<?php
			}
			if ($this->referee->position_name != "")
			{
				?>


                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_POSITION'); ?></strong>
					<?php echo Text::_($this->referee->position_name); ?>
                </address>

				<?php
			}
			if ((!empty($this->referee->knvbnr)) && ($this->config['show_person_regnr'] == 1))
			{
				?>


                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_REGISTRATIONNR'); ?></strong>
					<?php echo $this->referee->knvbnr; ?>
                </address>
				<?php
			}
			?>

        </div>


    </div>

    <br/>
<?php } ?>
