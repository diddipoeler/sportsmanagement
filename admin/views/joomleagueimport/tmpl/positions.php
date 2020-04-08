<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage joomleagueimport
 * @file       positions.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>

<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">


<table class="<?php echo $this->table_data_class; ?>">
            <thead>
            <tr>
            <?php echo $this->lists['whichtable']; ?>
            </tr>
                <tr>
                    <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
                    <th width="20"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" /></th>

                    <th width="" style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_JOOMLEAGUE_POSITIONS'); ?></th>
                    <th width="" style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_SPORTSMANAGEMENT_POSITIONS'); ?></th>
                    </tr>
            </thead>      
            <tbody>
                <?php
                $k=0;
                for ($i=0,$n=count($this->joomleague); $i < $n; $i++)
                {
                    $row =& $this->joomleague[$i];
                    $checked = HTMLHelper::_('grid.checkedout', $row, $i);
                    ?>
                    <tr class="<?php echo "row$k"; ?>">
                        <td class="center"><?php echo $i; ?></td>
                        <td class="center"><?php echo $checked; ?></td>
                      
                        <td><?php echo $row->name; ?>
                      
                        </td>
                        <td class="center">
                        <?php
                        $append =' onchange="document.getElementById(\'cb'.$i.'\').checked=true" ';
                        echo HTMLHelper::_(
                            'select.genericlist', $this->lists['position'], 'position'.$row->id,
                            'class="inputbox" size="1"'.$append, 'value', 'text', 0
                        );
                        ?>
                        </td>
                      
                      
                    </tr>
        <?php
        $k=1 - $k;
                }
                ?>
            </tbody>
        </table>      
                      
<?PHP



?>
    <input type="hidden" name="search_mode" value="<?php echo $this->lists['search_mode']; ?>" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />
    <?php echo HTMLHelper::_('form.token')."\n"; ?>

</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>  
