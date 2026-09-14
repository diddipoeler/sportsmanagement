<?php
/**
 * Native Joomla 5/6-compatible player personal information layout.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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
use Joomla\CMS\Uri\Uri;

$person = $this->person;
$config = is_array($this->config ?? null) ? $this->config : [];
$overallConfig = is_array($this->overallconfig ?? null) ? $this->overallconfig : [];
$teamPlayer = $this->teamPlayer ?? null;
$isContactDataVisible = (bool) ($this->isContactDataVisible ?? false);
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$name = PersonNameFormatter::format(
    null,
    (string) ($person->firstname ?? ''),
    (string) ($person->nickname ?? ''),
    (string) ($person->lastname ?? ''),
    (string) ($config['name_format'] ?? 0)
);
?>
<!-- person data START -->
<h4><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_PERSONAL_DATA'); ?></h4>

<div class="row table-responsive" id="player">
    <div class="col-xs-5 col-sm-5 col-md-5 col-lg-5">
        <?php
        if (!empty($config['show_player_photo'])) {
            $pictureText = Text::_('COM_SPORTSMANAGEMENT_PERSON_PICTURE');
            $imgTitle = Text::sprintf($pictureText, $name);
            $picture = PersonImageHelper::resolve(
                (string) ($teamPlayer->picture ?? ''),
                (string) ($person->picture ?? '')
            );

            echo ModalImageHelper::render(
                'playerinfo' . (int) ($person->id ?? 0),
                $picture,
                $imgTitle,
                (int) ($config['picture_height'] ?? 20),
                '',
                (int) ($this->modalwidth ?? 100),
                (int) ($this->modalheight ?? 200),
                (int) ($overallConfig['use_jquery_modal'] ?? 0)
            );
        }

        if (!empty($config['show_player_logo_copyright']) && !empty($person->cr_picture)) {
            echo Text::sprintf(
                'COM_SPORTSMANAGEMENT_PAINTER_INFO',
                '<i>' . $escape($person->cr_picture) . '</i>'
            );
        }
        ?>
        <br />
    </div>

    <div class="col-xs-5 col-sm-5 col-md-5 col-lg-5">
        <?php
        if (!empty($person->country) && !empty($config['show_nationality'])) {
            $countryCode = (string) $person->country;
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_NATIONALITY'); ?></strong>
                <?php echo CountryPresentationHelper::flag($countryCode) . ' ' . CountryPresentationHelper::name($countryCode); ?>
            </address>
            <?php

            if (!empty($person->second_country)) {
                $secondCountryCode = (string) $person->second_country;
                ?>
                <address>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_NATIONALITY'); ?></strong>
                    <?php echo CountryPresentationHelper::flag($secondCountryCode) . ' ' . CountryPresentationHelper::name($secondCountryCode); ?>
                </address>
                <?php
            }
        }

        $outputName = $name;
        $userId = (int) ($person->user_id ?? 0);

        if ($userId > 0) {
            switch ((int) ($config['show_user_profile'] ?? 0)) {
                case 1:
                    $outputName = HTMLHelper::link(
                        PersonProfileRouteHelper::contact($userId),
                        $outputName
                    );
                    break;

                case 2:
                    $outputName = HTMLHelper::link(
                        PersonProfileRouteHelper::cbe(
                            $userId,
                            (int) ($this->project->id ?? 0),
                            (int) ($person->id ?? 0)
                        ),
                        $outputName
                    );
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
        $birthday = (string) ($person->birthday ?? '');
        $deathday = (string) ($person->deathday ?? '');
        $timestampBirth = $birthday !== '' && $birthday !== '0000-00-00' ? strtotime($birthday) : false;
        $timestampDeath = $deathday !== '' && $deathday !== '0000-00-00' ? strtotime($deathday) : false;
        $showBirthday = (int) ($config['show_birthday'] ?? 0);

        if ($showBirthday > 0 && $showBirthday < 5 && $timestampBirth !== false) {
            $outputStr = '';
            $birthdateStr = '';

            switch ($showBirthday) {
                case 1:
                    $outputStr = 'COM_SPORTSMANAGEMENT_PERSON_BIRTHDAY_AGE';
                    $birthdateStr = HTMLHelper::date(
                        $birthday,
                        Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CALENDAR_DATE')
                    );
                    $birthdateStr .= '&nbsp;(' . PersonAgeHelper::calculate($birthday, $deathday) . ')';
                    break;

                case 2:
                    $outputStr = 'COM_SPORTSMANAGEMENT_PERSON_BIRTHDAY';
                    $birthdateStr = HTMLHelper::date(
                        $birthday,
                        Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CALENDAR_DATE')
                    );
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
            <?php
        }

        if ($timestampDeath !== false && !empty($config['show_deathday'])) {
            $deathdateStr = HTMLHelper::date(
                $deathday,
                Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DEATHDATE')
            );
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_DEATHDAY'); ?></strong>
                <?php echo '&dagger; ' . $deathdateStr; ?>
            </address>
            <?php
        }

        if (!empty($person->address) && !empty($config['show_person_address']) && $isContactDataVisible) {
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

        if (!empty($person->phone) && !empty($config['show_person_phone']) && $isContactDataVisible) {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_PHONE'); ?></strong>
                <?php echo $escape($person->phone); ?>
            </address>
            <?php
        }

        if (!empty($person->mobile) && !empty($config['show_person_mobile']) && $isContactDataVisible) {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_MOBILE'); ?></strong>
                <?php echo $escape($person->mobile); ?>
            </address>
            <?php
        }

        if (!empty($person->email) && !empty($config['show_person_email']) && $isContactDataVisible) {
            $user = $this->app->getIdentity();
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_EMAIL'); ?></strong>
                <?php if (!empty($user->id) || empty($overallConfig['nospam_email'])) : ?>
                    <a href="mailto:<?php echo $escape($person->email); ?>"><?php echo $escape($person->email); ?></a>
                <?php else : ?>
                    <?php echo HTMLHelper::_('email.cloak', $person->email); ?>
                <?php endif; ?>
            </address>
            <?php
        }

        if (!empty($person->website) && !empty($config['show_person_website'])) {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_WEBSITE'); ?></strong>
                <?php echo HTMLHelper::link(
                    (string) $person->website,
                    (string) $person->website,
                    ['target' => '_blank', 'rel' => 'noopener noreferrer']
                ); ?>
            </address>
            <?php
        }

        if (!empty($config['show_person_website'])) {
            foreach ([
                'twitter' => 'COM_SPORTSMANAGEMENT_EXT_PERSON_TWITTER',
                'facebook' => 'COM_SPORTSMANAGEMENT_EXT_PERSON_FACEBOOK',
                'instagram' => 'COM_SPORTSMANAGEMENT_EXT_PERSON_INSTAGRAM',
                'linkedin' => 'COM_SPORTSMANAGEMENT_EXT_PERSON_LINKEDIN',
            ] as $property => $label) {
                $url = trim((string) ($person->{$property} ?? ''));

                if ($url === '') {
                    continue;
                }
                ?>
                <address>
                    <strong><?php echo Text::_($label); ?></strong>
                    <?php echo HTMLHelper::link(
                        $url,
                        $url,
                        ['target' => '_blank', 'rel' => 'noopener noreferrer']
                    ); ?>
                </address>
                <?php
            }
        }

        if ((float) ($person->height ?? 0) > 0 && !empty($config['show_person_height'])) {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_HEIGHT'); ?></strong>
                <?php echo str_replace(
                    '%HEIGHT%',
                    $escape($person->height),
                    Text::_('COM_SPORTSMANAGEMENT_PERSON_HEIGHT_FORM')
                ); ?>
            </address>
            <?php
        }

        if ((float) ($person->weight ?? 0) > 0 && !empty($config['show_person_weight'])) {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_WEIGHT'); ?></strong>
                <?php echo str_replace(
                    '%WEIGHT%',
                    $escape($person->weight),
                    Text::_('COM_SPORTSMANAGEMENT_PERSON_WEIGHT_FORM')
                ); ?>
            </address>
            <?php
        }

        $jerseyNumber = (int) ($teamPlayer->jerseynumber ?? 0);
        if (!empty($config['show_player_number']) && $jerseyNumber > 0) {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_NUMBER'); ?></strong>
                <?php
                if (!empty($config['player_number_picture'])) {
                    echo HTMLHelper::image(
                        Uri::root() . 'images/com_sportsmanagement/database/teamplayers/shirt.php?text=' . $jerseyNumber . '#',
                        (string) $jerseyNumber,
                        ['title' => (string) $jerseyNumber]
                    );
                } else {
                    echo $jerseyNumber;
                }
                ?>
            </address>
            <?php
        }

        if (!empty($teamPlayer->position_id)) {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_ROSTERPOSITION'); ?></strong>
                <?php echo Text::_((string) ($teamPlayer->position_name ?? '')); ?>
            </address>
            <?php
        }

        if (!empty($person->knvbnr) && !empty($config['show_person_regnr'])) {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_REGISTRATIONNR'); ?></strong>
                <?php echo $escape($person->knvbnr); ?>
            </address>
            <?php
        }
        ?>
    </div>

    <div class="col-xs-2 col-sm-2 col-md-2 col-lg-2">
        <?php
        if ((string) ($this->project->sport_type_name ?? '') !== 'COM_SPORTSMANAGEMENT_ST_GOLF_BILLARD'
            && !empty($config['show_playfield'])) {
            echo $this->loadTemplate('playfield');
        }
        ?>
    </div>
</div>
