<?php
/**
 * Native Joomla 5/6 staff information layout.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa https://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\CountryPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonAgeHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonProfileRouteHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$config = is_array($this->config ?? null) ? $this->config : [];
$overallConfig = is_array($this->overallconfig ?? null) ? $this->overallconfig : [];
$person = $this->person ?? null;

if (!$person) {
    return;
}

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$containerClass = $escape((string) ($this->divclassrow ?? ''));
?>
<!-- person data START -->
<h4><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_PERSONAL_DATA'); ?></h4>

<div class="<?php echo $containerClass; ?> table-responsive" id="staff">
    <div class="col-md-6">
        <?php
        if ($config['show_photo'] ?? false)
        {
            $picturetext = Text::_('COM_SPORTSMANAGEMENT_PERSON_PICTURE');
            $imgTitle = Text::sprintf(
                $picturetext,
                PersonNameFormatter::format(
                    null,
                    (string) ($person->firstname ?? ''),
                    (string) ($person->nickname ?? ''),
                    (string) ($person->lastname ?? ''),
                    (string) ($config['name_format'] ?? '')
                )
            );
            $placeholder = PersonImageHelper::placeholder();
            $picture = (string) ($this->inprojectinfo->season_picture ?? '');

            if ($picture === '' || $picture === $placeholder)
            {
                $picture = (string) ($person->picture ?? '');
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
                'staffinfo' . (int) ($person->id ?? 0),
                $picture,
                $imgTitle,
                (int) ($config['picture_width'] ?? 0),
                '',
                (int) ($this->modalwidth ?? 0),
                (int) ($this->modalheight ?? 0),
                (int) ($overallConfig['use_jquery_modal'] ?? 0)
            );
        }
        ?>
    </div>
    <div class="col-md-6">
        <?php
        if (!empty($person->country) && ($config['show_nationality'] ?? false))
        {
            $countryCode = (string) $person->country;
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_NATIONALITY'); ?></strong>
                <?php
                echo CountryPresentationHelper::flag($countryCode) . ' '
                    . $escape(CountryPresentationHelper::name($countryCode));
                ?>
            </address>
            <?php
        }

        $outputName = $escape(Text::sprintf('%1$s %2$s', (string) ($person->firstname ?? ''), (string) ($person->lastname ?? '')));
        if (!empty($person->user_id))
        {
            switch ((int) ($config['show_user_profile'] ?? 0))
            {
                case 1:
                    $link = PersonProfileRouteHelper::contact((int) $person->user_id);
                    $outputName = HTMLHelper::link($link, $outputName);
                    break;

                case 2:
                    $link = PersonProfileRouteHelper::cbe(
                        (int) $person->user_id,
                        (int) ($this->project->id ?? 0),
                        (int) ($person->id ?? 0)
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

        <?php if (!empty($person->nickname)) : ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_NICKNAME'); ?></strong>
                <?php echo $escape($person->nickname); ?>
            </address>
        <?php endif; ?>

        <?php
        $showBirthday = (int) ($config['show_birthday'] ?? 0);
        $birthday = (string) ($person->birthday ?? '');
        $deathday = (string) ($person->deathday ?? '');

        if ($showBirthday > 0 && $showBirthday < 5 && $birthday !== '' && $birthday !== '0000-00-00')
        {
            $outputStr = '';
            $birthdateStr = '';

            switch ($showBirthday)
            {
                case 1:
                    $outputStr = 'COM_SPORTSMANAGEMENT_PERSON_BIRTHDAY_AGE';
                    $birthdateStr = HTMLHelper::date($birthday, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE'));
                    $birthdateStr .= '&nbsp;(' . PersonAgeHelper::calculate($birthday, $deathday) . ')';
                    break;

                case 2:
                    $outputStr = 'COM_SPORTSMANAGEMENT_PERSON_BIRTHDAY';
                    $birthdateStr = HTMLHelper::date($birthday, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE'));
                    break;

                case 3:
                    $outputStr = 'COM_SPORTSMANAGEMENT_PERSON_AGE';
                    $birthdateStr = (string) PersonAgeHelper::calculate($birthday, $deathday);
                    break;

                case 4:
                    $outputStr = 'COM_SPORTSMANAGEMENT_PERSON_YEAR_OF_BIRTH';
                    $birthdateStr = HTMLHelper::date($birthday, '%Y');
                    break;
            }
            ?>
            <address>
                <strong><?php echo Text::_($outputStr); ?></strong>
                <?php echo $birthdateStr; ?>
            </address>

            <?php if ($deathday !== '' && $deathday !== '0000-00-00') : ?>
                <?php $deathdateStr = HTMLHelper::date($deathday, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE')); ?>
                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_DEATHDAY'); ?></strong>
                    <?php echo '&dagger; ' . $deathdateStr; ?>
                </address>
            <?php endif; ?>
        <?php } ?>

        <?php
        if (!empty($person->address) && ((int) ($config['show_person_address'] ?? 0) === 1))
        {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_ADDRESS'); ?></strong>
                <?php
                echo CountryPresentationHelper::address(
                    '',
                    (string) ($person->address ?? ''),
                    (string) ($person->state ?? ''),
                    (string) ($person->zipcode ?? ''),
                    (string) ($person->location ?? ''),
                    (string) ($person->address_country ?? ''),
                    'COM_SPORTSMANAGEMENT_PERSON_ADDRESS_FORM'
                );
                ?>
            </address>
            <?php
        }

        if (!empty($person->phone) && ($config['show_person_phone'] ?? false))
        {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_PHONE'); ?></strong>
                <?php echo $escape($person->phone); ?>
            </address>
            <?php
        }

        if (!empty($person->mobile) && ($config['show_person_mobile'] ?? false))
        {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_MOBILE'); ?></strong>
                <?php echo $escape($person->mobile); ?>
            </address>
            <?php
        }

        if (!empty($person->email) && ($config['show_person_email'] ?? false))
        {
            $email = (string) $person->email;
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_EMAIL'); ?></strong>
                <?php
                $user = $this->app->getIdentity();
                if ($user->id || !($overallConfig['nospam_email'] ?? false))
                {
                    ?>
                    <a href="mailto:<?php echo $escape($email); ?>"><?php echo $escape($email); ?></a>
                    <?php
                }
                else
                {
                    echo HTMLHelper::_('email.cloak', $email);
                }
                ?>
            </address>
            <?php
        }

        if (!empty($person->website) && ($config['show_person_website'] ?? false))
        {
            $website = (string) $person->website;
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_WEBSITE'); ?></strong>
                <?php echo HTMLHelper::_('link', $website, $escape($website), ['target' => '_blank', 'rel' => 'noopener noreferrer']); ?>
            </address>
            <?php
        }

        if ((float) ($person->height ?? 0) > 0 && ($config['show_person_height'] ?? false))
        {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_HEIGHT'); ?></strong>
                <?php echo $escape(str_replace('%HEIGHT%', (string) $person->height, Text::_('COM_SPORTSMANAGEMENT_PERSON_HEIGHT_FORM'))); ?>
            </address>
            <?php
        }

        if ((float) ($person->weight ?? 0) > 0 && ($config['show_person_weight'] ?? false))
        {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_WEIGHT'); ?></strong>
                <?php echo $escape(str_replace('%WEIGHT%', (string) $person->weight, Text::_('COM_SPORTSMANAGEMENT_PERSON_WEIGHT_FORM'))); ?>
            </address>
            <?php
        }

        if (isset($this->inprojectinfo->position_id) && (int) $this->inprojectinfo->position_id > 0)
        {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_POSITION'); ?></strong>
                <?php echo Text::_((string) ($this->inprojectinfo->position_name ?? '')); ?>
            </address>
            <?php
        }

        if (!empty($person->knvbnr) && ($config['show_person_regnr'] ?? false))
        {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_REGISTRATIONNR'); ?></strong>
                <?php echo $escape($person->knvbnr); ?>
            </address>
            <?php
        }
        ?>
    </div>
</div>
