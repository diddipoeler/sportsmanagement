<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       edit_matchrelation.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage editmatch
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
?>          
        <fieldset class="adminform">
            <legend>
                <?php
                echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_MREL_DETAILS');
                ?>
            </legend>
            <br/>
            <table class='admintable'>
                <tr>
                    <td align="right" class="key">
                        <label>
        <?php
        echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_MREL_OLD_ID');
        ?>
                        </label>
                    </td>
                    <td align="left">
        <?php echo $this->lists['old_match']; ?>
        <?php if($this->match->old_match_id >0) : ?>
                          <a href="index.php?option=com_sportsmanagement&tmpl=component&controller=match&task=edit&cid[]=<?php echo $this->match->old_match_id?>">Match Link</a>
        <?php endif ?>
                    </td>
                </tr>
                <tr>
                    <td align="right" class="key">
                        <label>
        <?php
        echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_MREL_NEW_ID');
        ?>
                        </label>
                    </td>
                    <td align="left">
        <?php echo $this->lists['new_match']; ?>
        <?php if($this->match->new_match_id >0) : ?>
                          <a href="index.php?option=com_sportsmanagement&tmpl=component&controller=match&task=edit&cid[]=<?php echo $this->match->new_match_id?>">Match Link</a>
        <?php endif ?>
                    </td>
                </tr>
              
            </table>
        </fieldset>