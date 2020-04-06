<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       editlineup_players_trikot_numbers.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage editmatch
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<fieldset class="adminform">
<legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_ELUP_TRIKOT_NUMBER'); ?></legend>
<?php    
if (isset($this->positions) ) {  
    foreach ($this->positions AS $position_id => $pos)
    {
        ?>
        <fieldset class="adminform">
        <legend><?php echo Text::_($pos->text); ?></legend>
    <table>    
    <?PHP
    // get players assigned to this position
    foreach ($this->starters[$position_id] AS $player)
    {
    ?>
    <tr>
    <td><?php echo $player->firstname; ?>
</td>
<td><?php echo $player->lastname; ?>
</td>
<td><?php echo $player->jerseynumber; ?>
</td>
<td><input type='' name='trikot_number[<?php echo $player->value;?>]' value="<?php echo $player->trikot_number; ?>" />
</td>
<td>
<?PHP    
$append=' style="background-color:#bbffff"';
    echo HTMLHelper::_(
        'select.genericlist',
        $this->lists['captain'],
        'captain['.$player->value.']',
        'class="inputbox" size="1" '.$append,
        'value', 'text', $player->captain
    );
    ?> 
    </td>                                                       
    </tr>
    <?PHP
    }
    ?>
    </table>
    </fieldset>   
    <?PHP	
    }
}
?>      
</fieldset>      
