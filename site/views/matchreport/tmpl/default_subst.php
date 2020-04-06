<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_subst.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage matchreport
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
usort(
    $this->substitutes, function ($a, $b) {
        return $a->in_out_time > $b->in_out_time;
    }
);
?>
<!-- START of Substitutions -->
<div class="<?php echo $this->divclassrow;?> table-responsive" id="matchreport-subst">
<?php
if ($this->config['show_substitutions'] ) {
    if (!empty($this->substitutes)) {
        ?>
        <h2><?php echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTES'); ?></h2>    
        <table class="table ">
         <tr>
       <td class="list">
        <ul><?php
        foreach ($this->substitutes as $sub)
         {
            if ($sub->ptid == $this->match->projectteam1_id) {
                ?><li class="list"><?php echo $this->showSubstitution($sub); ?></li><?php
            }
        }
            ?></ul>
          </td>
          <td class="list">
           <ul><?php
            foreach ($this->substitutes as $sub)
            {
                if ($sub->ptid == $this->match->projectteam2_id) {
                    ?><li class="list"><?php echo $this->showSubstitution($sub); ?></li><?php
                }
            }
            ?></ul>
          </td>
         </tr>
        </table>
        <?php
    }
}
?>
</div>
<!-- END of Substitutions -->
