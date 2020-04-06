<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_picture.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage roster
 */

defined('_JEXEC') or die('Restricted access'); 
use Joomla\CMS\Language\Text;
?>
<div class="<?php echo $this->divclassrow;?> table-responsive" id="roster"> 
<?php
    // Show team-picture if defined.
if ($this->config['show_team_logo'] ) {
    ?>
        <table class="table">
            <tr>
                <td align="center">
    <?php

    $picture = $this->projectteam->picture;
    if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("team") )) {
        $picture = $this->team->picture;
    }
                                        
    $imgTitle = Text::sprintf('COM_SPORTSMANAGEMENT_ROSTER_PICTURE_TEAM', $this->team->name);
           
    echo sportsmanagementHelperHtml::getBootstrapModalImage(
        'roster'.$this->team->name,
        $picture,
        $this->team->name,
        $this->config['team_picture_width'],
        '',
        $this->modalwidth,
        $this->modalheight,
        $this->overallconfig['use_jquery_modal']
    );      
    ?>

   
                </td>
            </tr>
        </table>
    <?php
}
    ?>
</div>
