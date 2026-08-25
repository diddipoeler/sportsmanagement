<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage staff
 * @file       default_info.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\CountryPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonAgeHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonProfileRouteHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

?>
<!-- person data START -->
<h4><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_PERSONAL_DATA'); ?></h4>

<div class="<?php echo $this->divclassrow; ?> table-responsive" id="staff">
    <div class="col-md-6">
        <?php
        if ($this->config['show_photo'])
        {
            $picturetext = Text::_('COM_SPORTSMANAGEMENT_PERSON_PICTURE');
            $imgTitle = Text::sprintf(
                $picturetext,
                PersonNameFormatter::format(
                    null,
                    (string) ($this->person->firstname ?? ''),
                    (string) ($this->person->nickname ?? ''),
                    (string) ($this->person->lastname ?? ''),
                    (string) ($this->config['name_format'] ?? '')
                )
            );
            $placeholder = PersonImageHelper::placeholder();
            $picture = (string) ($this->inprojectinfo->season_picture ?? '');

            if ($picture === '' || $picture === $placeholder)
            {
                $picture = (string) ($this->person->picture ?? '');
            }

            if ($picture === '')
            {
                $picture = $placeholder;
            }
            elseif (!preg_match('#^https?://#i', $picture))
            {
                $picturePath = JPATH_ROOT . '/' . ltrim($picture, '/');

                if (!is_file($picturePath))
                {
                    $picture = $placeholder;
                }
            }

            echo ModalImageHelper::render(
                'staffinfo' . $this->person->id,
                $picture,
                $imgTitle,
                $this->config['picture_width'],
                '',
                $this->modalwidth,
                $this->modalheight,
                (int) ($this->overallconfig['use_jquery_modal'] ?? 0)
            );
        }
        ?>
    </div>
    <div class="col-md-6">
        <?php
        if (!empty($this->person->country) && ($this->config['show_nationality'] ?? false))
        {
            $countryCode = (string) $this->person->country;
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_NATIONALITY'); ?></strong>
                <?php
                echo CountryPresentationHelper::flag($countryCode) . ' '
                    . CountryPresentationHelper::name($countryCode);
                ?>
            </address>
            <?php
        }

        $outputName = Text::sprintf('%1$s %2$s', $this->person->firstname, $this->person->lastname);
        if ($this->person->user_id)
        {
            switch ((int) ($this->config['show_user_profile'] ?? 0))
            {
                case 1: // Link to Joomla Contact Page
                    $link = PersonProfileRouteHelper::contact((int) $this->person->user_id);
                    $outputName = HTMLHelper::link($link, $outputName);
                    break;

                case 2: // Link to CBE User Page with support for SportsManagement Tab
                    $link = PersonProfileRouteHelper::cbe(
                        (int) $this->person->user_id,
                        (int) $this->project->id,
                        (int) $this->person->id
                    );
                    $outputName = HTMLHelper::link($link, $outputName);
                    break;
            }
        }
        ?>

        <address>
            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_NAME'); ?></strong>
            <?php echo $outputName; ?>
        </address>

        <?php if (!empty($this->person->nickname)) : ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_NICKNAME'); ?></strong>
                <?php echo $this->person->nickname; ?>
            </address>
        <?php endif; ?>

        <?php
        $showBirthday = (int) ($this->config['show_birthday'] ?? 0);
        if ($showBirthday > 0 && $showBirthday < 5 && $this->person->birthday != '0000-00-00')
        {
            switch ($showBirthday)
            {
                case 1:
                    $outputStr = 'COM_SPORTSMANAGEMENT_PERSON_BIRTHDAY_AGE';
                    $birthdateStr = HTMLHelper::date(
                        $this->person->birthday,
                        Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE')
                    );
                    $birthdateStr .= '&nbsp;(' . PersonAgeHelper::calculate(
                        (string) $this->person->birthday,
                        (string) $this->person->deathday
                    ) . ')';
                    break;

                case 2:
                    $outputStr = 'COM_SPORTSMANAGEMENT_PERSON_BIRTHDAY';
                    $birthdateStr = HTMLHelper::date(
                        $this->person->birthday,
                        Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE')
                    );
                    break;

                case 3:
                    $outputStr = 'COM_SPORTSMANAGEMENT_PERSON_AGE';
                    $birthdateStr = PersonAgeHelper::calculate(
                        (string) $this->person->birthday,
                        (string) $this->person->deathday
                    );
                    break;

                case 4:
                    $outputStr = 'COM_SPORTSMANAGEMENT_PERSON_YEAR_OF_BIRTH';
                    $birthdateStr = HTMLHelper::date($this->person->birthday, '%Y');
                    break;
            }
            ?>
            <address>
                <strong><?php echo Text::_($outputStr); ?></strong>
                <?php echo $birthdateStr; ?>
            </address>

            <?php if ($this->person->deathday != '0000-00-00') :
                $deathdateStr = HTMLHelper::date(
                    $this->person->deathday,
                    Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE')
                );
                ?>
                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_DEATHDAY'); ?></strong>
                    <?php echo '&dagger; ' . $deathdateStr; ?>
                </address>
            <?php endif; ?>
        <?php } ?>

        <?php
        if (($this->person->address != '') && ((int) ($this->config['show_person_address'] ?? 0) === 1))
        {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_ADDRESS'); ?></strong>
                <?php
                echo CountryPresentationHelper::address(
                    '',
                    (string) $this->person->address,
                    (string) $this->person->state,
                    (string) $this->person->zipcode,
                    (string) $this->person->location,
                    (string) $this->person->address_country,
                    'COM_SPORTSMANAGEMENT_PERSON_ADDRESS_FORM'
                );
                ?>
            </address>
            <?php
        }

        if (($this->person->phone != '') && ($this->config['show_person_phone'] ?? false))
        {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_PHONE'); ?></strong>
                <?php echo $this->person->phone; ?>
            </address>
            <?php
        }

        if (($this->person->mobile != '') && ($this->config['show_person_mobile'] ?? false))
        {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_MOBILE'); ?></strong>
                <?php echo $this->person->mobile; ?>
            </address>
            <?php
        }

        if (($this->person->email != '') && ($this->config['show_person_email'] ?? false))
        {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_EMAIL'); ?></strong>
                <?php
                $user = Factory::getApplication()->getIdentity();
                if ($user->id || !($this->overallconfig['nospam_email'] ?? false))
                {
                    ?>
                    <a href="mailto:<?php echo htmlspecialchars((string) $this->person->email, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars((string) $this->person->email, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                    <?php
                }
                else
                {
                    echo HTMLHelper::_('email.cloak', $this->person->email);
                }
                ?>
            </address>
            <?php
        }

        if (($this->person->website != '') && ($this->config['show_person_website'] ?? false))
        {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_WEBSITE'); ?></strong>
                <?php echo HTMLHelper::_(
                    'link',
                    $this->person->website,
                    $this->person->website,
                    ['target' => '_blank', 'rel' => 'noopener noreferrer']
                ); ?>
            </address>
            <?php
        }

        if (($this->person->height > 0) && ($this->config['show_person_height'] ?? false))
        {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_HEIGHT'); ?></strong>
                <?php echo str_replace('%HEIGHT%', $this->person->height, Text::_('COM_SPORTSMANAGEMENT_PERSON_HEIGHT_FORM')); ?>
            </address>
            <?php
        }

        if (($this->person->weight > 0) && ($this->config['show_person_weight'] ?? false))
        {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_WEIGHT'); ?></strong>
                <?php echo str_replace('%WEIGHT%', $this->person->weight, Text::_('COM_SPORTSMANAGEMENT_PERSON_WEIGHT_FORM')); ?>
            </address>
            <?php
        }

        if (isset($this->inprojectinfo->position_id) && $this->inprojectinfo->position_id > 0)
        {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_POSITION'); ?></strong>
                <?php echo Text::_($this->inprojectinfo->position_name); ?>
            </address>
            <?php
        }

        if (!empty($this->person->knvbnr) && ($this->config['show_person_regnr'] ?? false))
        {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_REGISTRATIONNR'); ?></strong>
                <?php echo $this->person->knvbnr; ?>
            </address>
            <?php
        }
        ?>
    </div>
</div>
