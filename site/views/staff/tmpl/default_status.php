<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_status.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage staff
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

if (( isset($this->inprojectinfo->injury) && $this->inprojectinfo->injury > 0 )
    || ( isset($this->inprojectinfo->suspension) && $this->inprojectinfo->suspension > 0 )
    || ( isset($this->inprojectinfo->away) && $this->inprojectinfo->away > 0 )
) {
    ?>
<div class="<?php echo $this->divclassrow;?> table-responsive" id="staff">
    <h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_STATUS');    ?></h2>

    <table class="status">
    <?php
    if ($this->inprojectinfo->injury > 0) {
        $injury_date = "";
        $injury_end  = "";

        if (is_array($this->roundsdata)) {
            //injury start
            if (array_key_exists($this->inprojectinfo->injury_date, $this->roundsdata)) {
                $injury_date = HTMLHelper::date($this->roundsdata[$this->inprojectinfo->injury_date]['date_first'], 'COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE');
                $injury_date .= " - ".$this->inprojectinfo->injury_date.". ".Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME');
            }

            //injury end
            if (array_key_exists($this->inprojectinfo->injury_end, $this->roundsdata)) {
                $injury_end = HTMLHelper::date($this->roundsdata[$this->inprojectinfo->injury_end]['date_last'], 'COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE');
                $injury_end .= " - ".$this->inprojectinfo->injury_end.". ".Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME');
            }
        }

        if ($this->inprojectinfo->injury_date == $this->inprojectinfo->injury_end) {
            ?>
            <tr>
            <td class="label">
            <?php
            $imageTitle = Text::_('COM_SPORTSMANAGEMENT_PERSON_INJURED');
            echo "&nbsp;&nbsp;" . HTMLHelper::image(
                'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/injured.gif',
                $imageTitle,
                array( 'title' => $imageTitle )
            );
              echo Text::_('COM_SPORTSMANAGEMENT_PERSON_INJURED');
                ?>
             </td>
             <td  class="data">
                <?php
                echo $injury_end;
                ?>
             </td>
            </tr>
            <?php
        }
        else
        {
            ?>
            <tr>
            <td class="label">
            <?php
            $imageTitle = Text::_('COM_SPORTSMANAGEMENT_PERSON_INJURED');
            echo "&nbsp;&nbsp;" . HTMLHelper::image(
                'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/injured.gif',
                $imageTitle,
                array( 'title' => $imageTitle )
            );
                ?>
             </td>
            </tr>
            <tr>
             <td class="label">
                <?php
                echo Text::_('COM_SPORTSMANAGEMENT_PERSON_INJURY_DATE');
                ?>
             </td>
             <td class="data">
                <?php
                echo $injury_date;
                ?>
             </td>
            </tr>
            <tr>
             <td class="label">
                      
                <?php
                echo Text::_('COM_SPORTSMANAGEMENT_PERSON_INJURY_END');
                ?>
                      
             </td>
             <td class="data">
                <?php
                echo $injury_end;
                ?>
             </td>
            </tr>
            <?php
        }
        ?>
       <tr>
      <td class="label">
                  
        <?php
        echo Text::_('COM_SPORTSMANAGEMENT_PERSON_INJURY_TYPE');
        ?>
                  
        </td>
        <td class="data">
            <?php
            printf("%s", htmlspecialchars($this->inprojectinfo->injury_detail));
            ?>
        </td>
       </tr>
        <?php
    }

    if ($this->inprojectinfo->suspension > 0) {
        $suspension_date = "";
        $suspension_end  = "";

        if (is_array($this->roundsdata)) {
            //suspension start
            if (array_key_exists($this->inprojectinfo->suspension_date, $this->roundsdata)) {
                $suspension_date = HTMLHelper::date($this->roundsdata[$this->inprojectinfo->suspension_date]['date_first'], 'COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE');
                $suspension_date .= " - ".$this->inprojectinfo->suspension_date.". ".Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME');
            }

            //suspension end
            if (array_key_exists($this->inprojectinfo->suspension_end, $this->roundsdata)) {
                $suspension_end = HTMLHelper::date($this->roundsdata[$this->inprojectinfo->suspension_end]['date_last'], 'COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE');
                $suspension_end .= " - ".$this->inprojectinfo->suspension_end.". ".Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME');
            }
        }

        if ($this->inprojectinfo->suspension_date == $this->inprojectinfo->suspension_end) {
            ?>
            <tr>
            <td class="label">
                      
            <?php
            $imageTitle = Text::_('COM_SPORTSMANAGEMENT_PERSON_SUSPENDED');
            echo "&nbsp;&nbsp;" . HTMLHelper::image(
                'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/suspension.gif',
                $imageTitle,
                array( 'title' => $imageTitle )
            );
              echo Text::_('COM_SPORTSMANAGEMENT_PERSON_SUSPENDED');
                ?>
                      
             </td>
             <td class="data">
                <?php
                echo $suspension_end;
                ?>
             </td>
            </tr>
            <?php
        }
        else
        {
            ?>
            <tr>
            <td class="label">
                      
            <?php
            $imageTitle = Text::_('COM_SPORTSMANAGEMENT_PERSON_SUSPENDED');
            echo "&nbsp;&nbsp;" . HTMLHelper::image(
                'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/suspension.gif',
                $imageTitle,
                array( 'title' => $imageTitle )
            );
                ?>
                      
             </td>
            </tr>
            <tr>
             <td class="label">
                      
                <?php
                echo Text::_('COM_SPORTSMANAGEMENT_PERSON_SUSPENSION_DATE');
                ?>
                      
             </td>
             <td class="data">
                <?php
                echo $suspension_date;
                ?>
             </td>
            </tr>
            <tr>
             <td class="label">
                      
                <?php
                echo Text::_('COM_SPORTSMANAGEMENT_PERSON_SUSPENSION_END');
                ?>
                      
             </td>
             <td class="data">
                <?php
                echo $suspension_end;
                ?>
             </td>
            </tr>
            <?php
        }
        ?>
       <tr>
      <td class="label">
                  
        <?php
        echo Text::_('COM_SPORTSMANAGEMENT_PERSON_SUSPENSION_REASON');
        ?>
                  
        </td>
        <td class="data">
            <?php
            printf("%s", htmlspecialchars($this->inprojectinfo->suspension_detail));
            ?>
        </td>
       </tr>
        <?php
    }

    if ($this->inprojectinfo->away > 0) {
        $away_date = "";
        $away_end  = "";

        if (is_array($this->roundsdata)) {
            //suspension start
            if (array_key_exists($this->inprojectinfo->away_date, $this->roundsdata)) {
                $away_date = HTMLHelper::date($this->roundsdata[$this->inprojectinfo->away_date]['date_first'], 'COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE');
                $away_date .= " - ".$this->inprojectinfo->away_date.". ".Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME');
            }

            //suspension end
            if (array_key_exists($this->inprojectinfo->away_end, $this->roundsdata)) {
                $away_end = HTMLHelper::date($this->roundsdata[$this->inprojectinfo->away_end]['date_last'], 'COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE');
                $away_end .= " - ".$this->inprojectinfo->away_end.". ".Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME');
            }
        }

        if ($this->inprojectinfo->away_date == $this->inprojectinfo->away_end) {
            ?>
            <tr>
            <td class="label">
                      
            <?php
            $imageTitle = Text::_('COM_SPORTSMANAGEMENT_PERSON_AWAY');
            echo "&nbsp;&nbsp;" . HTMLHelper::image(
                'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/away.gif',
                $imageTitle,
                array( 'title' => $imageTitle )
            );
              echo Text::_('COM_SPORTSMANAGEMENT_PERSON_AWAY');
                ?>
                      
             </td>
             <td class="data">
                <?php
                echo $away_end;
                ?>
             </td>
            </tr>
            <?php
        }
        else
        {
            ?>
            <tr>
            <td class="label">
                      
            <?php
            $imageTitle = Text::_('COM_SPORTSMANAGEMENT_PERSON_AWAY');
            echo "&nbsp;&nbsp;" . HTMLHelper::image(
                'images/com_sportsmanagement/database/events/'.$this->project->fs_sport_type_name.'/away.gif',
                $imageTitle,
                array( 'title' => $imageTitle )
            );
                ?>
                      
             </td>
            </tr>
            <tr>
             <td class="label">
                      
                <?php
                echo Text::_('COM_SPORTSMANAGEMENT_PERSON_AWAY_DATE');
                ?>
                      
             </td>
             <td class="data">
                <?php
                echo $away_date;
                ?>
             </td>
            </tr>
            <tr>
             <td class="label">
                      
                <?php
                echo Text::_('COM_SPORTSMANAGEMENT_PERSON_AWAY_END');
                ?>
                      
             </td>
             <td class="data">
                <?php
                echo $away_end;
                ?>
             </td>
            </tr>
            <?php
        }
        ?>
       <tr>
      <td class="label">
                  
        <?php
        echo Text::_('COM_SPORTSMANAGEMENT_PERSON_AWAY_REASON');
        ?>
                  
        </td>
        <td class="data">
            <?php
            printf("%s", htmlspecialchars($this->inprojectinfo->away_detail));
            ?>
        </td>
       </tr>
        <?php
    }
    ?>
    </table>
</div>
    <br/>
    <?php
}
?>
