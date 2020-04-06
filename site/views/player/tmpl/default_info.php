<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 *
 * @version    1.0.05
 * @file       default_info.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage player
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
?>
<!-- person data START -->
<h4><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_PERSONAL_DATA'); ?></h4>

<div class="<?php echo $this->divclassrow;?> table-responsive" id="player">
    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">

        <?php
        if ($this->config['show_player_photo']) {
            $picturetext = Text::_('COM_SPORTSMANAGEMENT_PERSON_PICTURE');
            $imgTitle = Text::sprintf($picturetext, sportsmanagementHelper::formatName(null, $this->person->firstname, $this->person->nickname, $this->person->lastname, $this->config["name_format"]));
            $picture = isset($this->teamPlayer) ? $this->teamPlayer->picture : null;
            if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("player") )) {
                $picture = $this->person->picture;
            }

            echo sportsmanagementHelperHtml::getBootstrapModalImage(
                'playerinfo' . $this->person->id,
                $picture,
                $imgTitle,
                $this->config['picture_width'],
                '',
                $this->modalwidth,
                $this->modalheight,
                $this->overallconfig['use_jquery_modal']
            );
        }

        if ($this->config['show_player_logo_copyright']) {
            if ($this->person->cr_picture) {
                echo Text::sprintf('COM_SPORTSMANAGEMENT_PAINTER_INFO', '<i>' . $this->person->cr_picture . '</i>');
            }
        }
        ?>
        <br />
    </div>    
    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">    
        <?php
        if (!empty($this->person->country) && $this->config["show_nationality"]) {
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
        $outputName = sportsmanagementHelper::formatName(null, $this->person->firstname, $this->person->nickname, $this->person->lastname, $this->config["name_format"]);
        if ($this->person->user_id) {
            switch ($this->config['show_user_profile']) {
            case 1:  // Link to Joomla Contact Page
                $link = sportsmanagementHelperRoute::getContactRoute($this->person->user_id);
                $outputName = HTMLHelper::link($link, $outputName);
                break;

            case 2:  // Link to CBE User Page with support for SportsManagement Tab
                $link = sportsmanagementHelperRoute::getUserProfileRouteCBE($this->person->user_id, $this->project->id, $this->person->id);
                $outputName = HTMLHelper::link($link, $outputName);
                break;

            default: 
                break;
            }
        }
        ?>
        <address>
            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_NAME'); ?></strong>
            <?php echo $outputName; ?>
        </address>

        <?php
        if (!empty($this->person->nickname)) {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_NICKNAME'); ?></strong>
                <?php echo $this->person->nickname; ?>
            </address>
            <?php
        }

        $timestamp_birth = strtotime($this->person->birthday);
        $timestamp_death = strtotime($this->person->deathday);

        if (( $this->config['show_birthday'] > 0 ) 
            && ( $this->config['show_birthday'] < 5 ) 
            && ( $timestamp_birth )
        ) {
            // $this->config['show_birthday'] = 4;

            switch ($this->config['show_birthday']) {
            case 1:   // show Birthday and Age
                $outputStr = 'COM_SPORTSMANAGEMENT_PERSON_BIRTHDAY_AGE';
                break;

            case 2:   // show Only Birthday
                $outputStr = 'COM_SPORTSMANAGEMENT_PERSON_BIRTHDAY';
                break;

            case 3:   // show Only Age
                $outputStr = 'COM_SPORTSMANAGEMENT_PERSON_AGE';
                break;

            case 4:   // show Only Year of birth
                $outputStr = 'COM_SPORTSMANAGEMENT_PERSON_YEAR_OF_BIRTH';
                break;
            }

            switch ($this->config['show_birthday']) {
            case 1:  // show Birthday and Age
                $birthdateStr = $timestamp_birth ?
                    HTMLHelper::date($this->person->birthday, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE')) : "-";
                $birthdateStr .= "&nbsp;(" . sportsmanagementHelper::getAge($this->person->birthday, $this->person->deathday) . ")";
                break;

            case 2:  // show Only Birthday
                $birthdateStr = $timestamp_birth ?
                    HTMLHelper::date($this->person->birthday, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE')) : "-";
                break;

            case 3:  // show Only Age
                if ($timestamp_birth && $timestamp_death) {
                    $birthdateStr = sportsmanagementHelper::getAge($this->person->birthday, $this->person->deathday);
                }
                break;

            case 4:  // show Only Year of birth
                $birthdateStr = $timestamp_birth ?
                    HTMLHelper::date($this->person->birthday, Text::_('%Y')) : "-";
                break;

            default: $birthdateStr = "";
                break;
            }
            if ($this->person->birthday != "0000-00-00") {
                echo '<address>';
                echo '<strong>' . Text::_($outputStr) . '</strong> ';
                echo $birthdateStr;
                echo '</address>';
            }
        }
        if ($timestamp_death && $this->config['show_deathday']) {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_DEATHDAY'); ?></strong>
                <?php
                $deathdateStr = HTMLHelper::date($this->person->deathday, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DEATHDATE'));
                echo '&dagger; ' . $deathdateStr;
                ?>
            </address>    
            <?php
        }
        if (( $this->person->address != "" ) && $this->config['show_person_address'] && ($this->isContactDataVisible)) {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_ADDRESS'); ?></strong>
                <?php
                echo JSMCountries::convertAddressString('', $this->person->address, $this->person->state, $this->person->zipcode, $this->person->location, $this->person->address_country, 'COM_SPORTSMANAGEMENT_PERSON_ADDRESS_FORM');
                ?>
            </address>
            <?php
        }
        if (( $this->person->phone != "" ) && $this->config['show_person_phone'] && ($this->isContactDataVisible)) {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_PHONE'); ?></strong>
                <?php echo $this->person->phone; ?>
            </address>
            <?php
        }
        if (( $this->person->mobile != "" ) && $this->config['show_person_mobile'] && ($this->isContactDataVisible)) {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_MOBILE'); ?></strong>
                <?php echo $this->person->mobile; ?>
            </address>
            <?php
        }
        if (( $this->person->email != "" ) && $this->config['show_person_email'] && ($this->isContactDataVisible)) {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_EMAIL'); ?></strong>
                <?php
                $user = Factory::getUser();
                if (( $user->id ) || (!$this->overallconfig['nospam_email'] )) {
                    ?> <a href="mailto: <?php echo $this->person->email; ?>"> <?php
                        echo $this->person->email;
                        ?> </a> <?php
                } else {
                    echo HTMLHelper::_('email.cloak', $this->person->email);
                }
                ?>
            </address>
            <?php
        }
        if (( $this->person->website != "" ) && $this->config['show_person_website']) {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_WEBSITE'); ?></strong>
                <?php
                echo HTMLHelper::_('link', $this->person->website, $this->person->website, array('target' => '_blank'));
                ?>
            </address>
            <?php
        }
        if (( $this->person->height > 0 ) && $this->config['show_person_height']) {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_HEIGHT'); ?></strong>
                <?php echo str_replace("%HEIGHT%", $this->person->height, Text::_('COM_SPORTSMANAGEMENT_PERSON_HEIGHT_FORM')); ?>
            </address>
            <?php
        }
        if (( $this->person->weight > 0 ) && $this->config['show_person_weight']) {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_WEIGHT'); ?></strong>
                <?php
                echo str_replace("%WEIGHT%", $this->person->weight, Text::_('COM_SPORTSMANAGEMENT_PERSON_WEIGHT_FORM'));
                ;
                ?>
            </address>
            <?php
        }
        if (( $this->config['show_player_number'] ) 
            && isset($this->teamPlayer->jerseynumber) 
            && ( $this->teamPlayer->jerseynumber > 0 )
        ) {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_NUMBER'); ?></strong>
                <?php
                if ($this->config['player_number_picture']) {
                    $posnumber = $this->teamPlayer->jerseynumber;
                    echo HTMLHelper::image(Uri::root() . 'images/com_sportsmanagement/database/events/shirt.php?text=' . $posnumber, $posnumber, array('title' => $posnumber));
                } else {
                    echo $this->teamPlayer->jerseynumber;
                }
                ?>
            </address>
            <?php
        }
        if (isset($this->teamPlayer->position_id) && $this->teamPlayer->position_id != "") {
            ?>
            <address>
                <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_ROSTERPOSITION'); ?></strong>
                <?php echo Text::_($this->teamPlayer->position_name); ?>
            </address>
            <?php
        }
        if ((!empty($this->person->knvbnr) ) && $this->config['show_person_regnr']) {
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
