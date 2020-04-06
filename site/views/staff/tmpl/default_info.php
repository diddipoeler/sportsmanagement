<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_info.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage staff
 */

defined('_JEXEC') or die('Restricted access'); 
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

?>
<!-- person data START -->
<h4><?php	echo Text::_('COM_SPORTSMANAGEMENT_PERSON_PERSONAL_DATA');    ?></h4>

<div class="<?php echo $this->divclassrow;?> table-responsive" id="staff">
<div class="col-md-6">


    <?php
    if ($this->config['show_photo'] ) {
        ?>

        <?php
        $picturetext = Text::_('COM_SPORTSMANAGEMENT_PERSON_PICTURE');
        $imgTitle = Text::sprintf($picturetext, sportsmanagementHelper::formatName(null, $this->person->firstname, $this->person->nickname, $this->person->lastname, $this->config["name_format"]));
        $picture = $this->inprojectinfo->season_picture;
        if ((empty($picture))|| ($picture == sportsmanagementHelper::getDefaultPlaceholder("player")  )) {
            $picture = $this->person->picture;
        }
                
                
        echo sportsmanagementHelperHtml::getBootstrapModalImage(
            'staffinfo' . $this->person->id,
            $picture,
            $imgTitle,
            $this->config['picture_width'],
            '',
            $this->modalwidth,
            $this->modalheight,
            $this->overallconfig['use_jquery_modal']
        );                
        
        
        ?>

        <?php
    }
    ?>
</div>        
<div class="col-md-6">  

    <?php
    if(!empty($this->person->country) && $this->config["show_nationality"] ) {
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
        $outputName = Text::sprintf('%1$s %2$s', $this->person->firstname, $this->person->lastname);
        if ($this->person->user_id ) {
            switch ( $this->config['show_user_profile'] )
            {
            case 1:     // Link to Joomla Contact Page
                $link = sportsmanagementHelperRoute::getContactRoute($this->person->user_id);
                $outputName = HTMLHelper::link($link, $outputName);
                break;

            case 2:     // Link to CBE User Page with support for SportsManagement Tab
                $link = sportsmanagementHelperRoute::getUserProfileRouteCBE(
                    $this->person->user_id,
                    $this->project->id,
                    $this->person->id 
                );
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
                
                
                
                <?php if (! empty($this->person->nickname) ) {
                    
        ?>
                <address>
            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_NICKNAME'); ?></strong>
    <?php echo $this->person->nickname; ?>
            </address>
                <?php
                }
                
                
if (( $this->config[ 'show_birthday' ] > 0 ) 
    && ( $this->config[ 'show_birthday' ] < 5 ) 
    && ( $this->person->birthday != '0000-00-00' ) 
) {

    ?>
                    

    <?php
    switch ( $this->config['show_birthday'] )
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
    //echo Text::_( $outputStr );
    ?>

                        
    <?php
    switch ( $this->config['show_birthday'] )
    {
    case 1:     // show Birthday and Age
        $birthdateStr =    $this->person->birthday != "0000-00-00" ?
           HTMLHelper::date($this->person->birthday, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE')) : "-";
        $birthdateStr .= "&nbsp;(" . sportsmanagementHelper::getAge($this->person->birthday, $this->person->deathday) . ")";
        break;

    case 2:     // show Only Birthday
        $birthdateStr =    $this->person->birthday != "0000-00-00" ?
           HTMLHelper::date($this->person->birthday, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE')) : "-";
        break;

    case 3:     // show Only Age
        $birthdateStr = sportsmanagementHelper::getAge($this->person->birthday, $this->person->deathday);
        break;

    case 4:     // show Only Year of birth
        $birthdateStr =    $this->person->birthday != "0000-00-00" ?
           HTMLHelper::date($this->person->birthday, Text::_('%Y')) : "-";
        break;

    default:    $birthdateStr = "";
        break;
    }
                            
    ?>
                        
                    
            <address>
            <strong><?php echo Text::_($outputStr); ?></strong>
    <?php echo $birthdateStr; ?>
        </address>
                    
                    
        <?php if($this->person->deathday != '0000-00-00' ) {?>
                    
        <?php
        $outputStr = 'COM_SPORTSMANAGEMENT_PERSON_DEATHDAY';
        //echo Text::_( $outputStr );
        ?>
                        
        <?php
        $deathdateStr =    $this->person->deathday != "0000-00-00" ?
        HTMLHelper::date($this->person->deathday, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE')) : "-";
        echo '&dagger; '.$deathdateStr;
        ?>
                        <address>
            <strong><?php echo Text::_($outputStr); ?></strong>
    <?php echo '&dagger; '.$deathdateStr; ?>
            </address>
        <?php
        }
                    
                    
                    
}
                
if (( $this->person->address != "" ) && ( $this->config[ 'show_person_address' ] ==1  )) {
    ?>
                <address>
            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_ADDRESS'); ?></strong>
        <?php
        echo JSMCountries::convertAddressString(
            '',
            $this->person->address,
            $this->person->state,
            $this->person->zipcode,
            $this->person->location,
            $this->person->address_country,
            'COM_SPORTSMANAGEMENT_PERSON_ADDRESS_FORM' 
        );
        ?>
              </address>
                <?php
}
                
                
if (( $this->person->phone != "" ) && $this->config[ 'show_person_phone' ] ) {
    ?>
  <address>
            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_PHONE'); ?></strong>
    <?php echo $this->person->phone; ?>
      </address>
    <?php
}

if (( $this->person->mobile != "" ) && $this->config[ 'show_person_mobile' ] ) {
    ?>
  <address>
            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_MOBILE'); ?></strong>
    <?php echo $this->person->mobile; ?>
      </address>
    <?php
}

if (( $this->person->email != "" ) && $this->config['show_person_email'] ) {
    ?>
  <address>
            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_EMAIL'); ?></strong>
    <?php 
      $user = Factory::getUser();
    if (( $user->id ) || ( ! $this->overallconfig['nospam_email'] ) ) {
        ?> <a href="mailto: <?php echo $this->person->email; ?>"> <?php
        echo $this->person->email;
?> </a> <?php
    }
    else
                {
        echo HTMLHelper::_('email.cloak', $this->person->email);
    }
        ?>
         </address>
        <?php
}

if (( $this->person->website != "" ) && $this->config['show_person_website'] ) {
    ?>
  <address>
            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_WEBSITE'); ?></strong>
    <?php echo HTMLHelper::_(
        'link',
        $this->person->website,
        $this->person->website,
        array( 'target' => '_blank' ) 
    );
            ?>
            </address>
        <?php
}

if (( $this->person->height > 0 ) && $this->config['show_person_height'] ) {
    ?>
  <address>
            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_HEIGHT'); ?></strong>
    <?php echo str_replace("%HEIGHT%", $this->person->height, Text::_('COM_SPORTSMANAGEMENT_PERSON_HEIGHT_FORM')); ?>
      </address>
    <?php
}
                
if (( $this->person->weight > 0 ) && $this->config['show_person_weight'] ) {
    ?>
  <address>
            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_WEIGHT'); ?></strong>
    <?php echo str_replace("%WEIGHT%", $this->person->weight, Text::_('COM_SPORTSMANAGEMENT_PERSON_WEIGHT_FORM'));; ?>
      </address>
    <?php
}
                    
if (isset($this->inprojectinfo->position_id) && $this->inprojectinfo->position_id > 0 ) {
    ?>
                
        <address>
            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_POSITION'); ?></strong>
    <?php echo Text::_($this->inprojectinfo->position_name); ?>
    </address>
                
                
                
                <?php
}

if (( ! empty($this->person->knvbnr) ) && $this->config['show_person_regnr'] ) {
    ?>
              <address>
            <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_REGISTRATIONNR'); ?></strong>
    <?php echo $this->person->knvbnr; ?>
      </address>
                    
    <?php
}
                ?>
            </table>
        </td>
    </tr>
</table>
<br />

</div>  
</div>  
