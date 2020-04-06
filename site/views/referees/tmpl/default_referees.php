<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_referees.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage referees
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

// Show referees as defined
if (!empty($this->rows) ) {
    ?>
<div class="<?php echo $this->divclassrow;?> table-responsive" id="referees">
    <table class="<?php echo $this->config['table_class'];?>">
    <?php
    $k                = 0;
    $position        = '';
    $totalEvents    = array();

    foreach ( $this->rows as $row )
    {
        if ($position != $row->position ) {
            $position    = $row->position;
            $k            = 0;
            $colspan    = ( ( $this->config['show_birthday'] > 0 ) ? '5' : '4' );
            ?>
            <tr class="sectiontableheader">
            <td width="60%" colspan="<?php echo $colspan; ?>">
            <?php
            echo '&nbsp;' . Text::_($row->position);
            ?>
            </td>
            <?php	if ($this->config['show_games_count'] ) : ?>
                                <td style="text-align:center; ">
            <?php
            $imageTitle = Text::_('COM_SPORTSMANAGEMENT_REFEREES_GAMES');
            echo HTMLHelper::image(
                'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/refereed.png',
                $imageTitle, array( 'title' => $imageTitle, 'height' => 20 ) 
            );
            ?>
                                </td>
            <?php endif;    ?>
            </tr>
            <?php
        }
        ?>
       <tr class="">
      <td width="30" style="text-align:center; ">
        <?php
        echo '&nbsp;';
            ?>
        </td>
        <td width="40" style="text-align:center; " class="nowrap">
            <?php
            $refereeName = sportsmanagementHelper::formatName(null, $row->firstname, $row->nickname, $row->lastname, $this->config["name_format"]);
            if ($this->config['show_icon'] == 1) {
                echo sportsmanagementHelperHtml::getBootstrapModalImage(
                    'referee'.$row->id,
                    $row->picture,
                    $refereeName,
                    $this->config['referee_picture_width'],
                    '',
                    $this->modalwidth,
                    $this->modalheight,
                    $this->overallconfig['use_jquery_modal']
                );

            }
            ?>
        </td>
        <td style="width:20%;">
            <?php
            if ($this->config['link_name'] == 1 ) {
                $routeparameter = array();
                $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
                $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
                $routeparameter['p'] = $this->project->slug;
                $routeparameter['pid'] = $row->slug;
                $link = sportsmanagementHelperRoute::getSportsmanagementRoute('referee', $routeparameter);

                echo HTMLHelper::link($link, '<i>' . $refereeName . '</i>');
            }
            else
            {
                echo '<i>' . $refereeName . '</i>';
            }
            ?>
        </td>
        <td style="width:16px; text-align:center; " class="nowrap" >
            <?php
            echo JSMCountries::getCountryFlag($row->country);
            ?>
        </td>
        <?php
        if ($this->config['show_birthday'] > 0 ) {
            ?>
           <td width="10%" class="nowrap" style="text-align:left; ">
            <?php

            switch ( $this->config['show_birthday'] )
            {
            case 1:     // show Birthday and Age
                $birthdateStr  = $row->birthday != "0000-00-00" ? HTMLHelper::date(
                    $row->birthday .' UTC', 
                    Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE'), 
                    sportsmanagementHelper::getTimezone($this->project, $this->overallconfig)
                ) : "-";
                $birthdateStr .= "&nbsp;(" . sportsmanagementHelper::getAge($row->birthday, $row->deathday) . ")";
                break;
            case 2:     // show Only Birthday
                $birthdateStr = $row->birthday != "0000-00-00" ? HTMLHelper::date(
                    $row->birthday .' UTC',
                    Text::_('COM_SPORTSMANAGEMENT_GLOBAL_DAYDATE'), 
                    sportsmanagementHelper::getTimezone($this->project, $this->overallconfig)
                ) : "-";
                break;
            case 3:     // show Only Age
                $birthdateStr = "(" . sportsmanagementHelper::getAge($row->birthday, $row->deathday) . ")";
                break;
            case 4:     // show Only Year of birth
                $birthdateStr  = $row->birthday != "0000-00-00" ? HTMLHelper::date(
                    $row->birthday .' UTC',
                    Text::_('%Y'), 
                    sportsmanagementHelper::getTimezone($this->project, $this->overallconfig) 
                ) : "-";
                break;

            default:
                $birthdateStr = "";
                break;
            }
            echo $birthdateStr;
        ?>
       </td>
        <?php
        }
        ?>
        <?php if ($this->config['show_games_count'] ) : ?>
                    <td>
        <?php echo $row->countGames; ?>
                    </td>
        <?php endif;    ?>
       </tr>
        <?php
        $k = 1 - $k;
    }
    $colspan = 9;
    if ($this->config['show_birthday'] > 0 ) {
        $colspan++;
    }
    ?>
    </table>
</div>
    <?php
}
?>
<br />
