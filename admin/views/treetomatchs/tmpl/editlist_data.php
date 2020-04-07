<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       editlist_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage treetomatchs
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
?>
<legend>
<?php
echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_ASSIGN_TITLE', '<i>' . $this->projectws->name . '</i>');
?>
</legend>
<table class="table" border="0">
<tr>
<td>
<b>
<?php
echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_ASSIGN_AVAIL_MATCHES');
?>
</b><br />
<?php
echo $this->lists['matches'];
?>
</td>
<td style="">
<input id="moveright" type="button" value="<?php echo Text::_('&gt;&gt;'); ?>" onclick="move_list_items('matcheslist','node_matcheslist');" />                      
<input id="moveleft" type="button" value="<?php echo Text::_('&lt;&lt;'); ?>" onclick="move_list_items('node_matcheslist','matcheslist');" />                      
</td>
<td>
<b>
<?php
echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOMATCH_ASSIGN_NODE_MATCHES');
?>
</b><br />
<?php
echo $this->lists['node_matches'];
?>
</td>
</tr>
</table>


	  
