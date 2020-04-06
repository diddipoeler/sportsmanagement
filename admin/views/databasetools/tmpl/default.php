<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage databasetools
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
HTMLHelper::_('behavior.tooltip');
?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">

<?PHP
if(version_compare(JVERSION, '3.0.0', 'ge')) {
    echo $this->loadTemplate('joomla3');
}
else
{
    echo $this->loadTemplate('joomla2');  
}
?>

<!--	<div id="editcell"> -->
        <table class="table">
            <thead>
                <tr>
                    <th class="title" class="nowrap">
        <?php
        echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TOOL');
        ?>
                    </th>
                    <th class="title" class="nowrap">
        <?php
        echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_DESCR');
        ?>
                    </th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="2">
        <?php
        echo "&nbsp;";
        ?>
                    </td>
                </tr>
            </tfoot>
            <tbody>
          
            <tr>
                    <td class="nowrap" valign="top">
        <?php
        $link = Route::_('index.php?option=com_sportsmanagement&view=databasetool&task=databasetool.truncate');
        ?>
                      
                        <a href="<?php echo $link; ?>" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TRUNCATE2'); ?>">
        <?php
        echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TRUNCATE');
        ?>
                        </a>
                    </td>
                    <td>
        <?php
        echo Text::_("COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TRUNCATE_DESCR");
        ?>
                    </td>
                </tr>
              
                <tr>
                    <td class="nowrap" valign="top">
        <?php
        $link = Route::_('index.php?option=com_sportsmanagement&view=databasetool&task=databasetool.truncatejl');
        ?>
                      
                        <a href="<?php echo $link; ?>" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TRUNCATE2JL'); ?>">
        <?php
        echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TRUNCATEJL');
        ?>
                        </a>
                    </td>
                    <td>
        <?php
        echo Text::_("COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TRUNCATEJL_DESCR");
        ?>
                    </td>
                </tr>
              
                <tr>
                    <td class="nowrap" valign="top">
        <?php
        $link = Route::_('index.php?option=com_sportsmanagement&view=databasetool&task=databasetool.optimize');
        ?>
                        <a href="<?php echo $link; ?>" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_OPTIMIZE2'); ?>">
        <?php
        echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_OPTIMIZE');
        ?>
                        </a>
                    </td>
                    <td>
        <?php
        echo Text::_("COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_OPTIMIZE_DESCR");
        ?>
                    </td>
                </tr>

                <tr>
                    <td class="nowrap" valign="top">
        <?php
                        $link = Route::_('index.php?option=com_sportsmanagement&view=databasetool&task=databasetool.repair');
        ?>
                      
                            <a href="<?php echo $link; ?>" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_REPAIR2'); ?>">
                            <?php
                            echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_REPAIR');
        ?>
                        </a>
                    </td>
                    <td>
        <?php
        echo Text::_("COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_REPAIR_DESCR");
        ?>
                    </td>
                </tr>
              
                <tr>
                    <td class="nowrap" valign="top">
        <?php
        $link = Route::_('index.php?option=com_sportsmanagement&view=databasetool&task=databasetool.picturepath');
        ?>
                      
                        <a  href="<?php echo $link; ?>" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_PICTURE_PATH_MIGRATION2'); ?>">
        <?php
        echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_PICTURE_PATH_MIGRATION');
        ?>
                        </a>
                    </td>
                    <td>
        <?php
        echo Text::_("COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_PICTURE_PATH_MIGRATION_DESCR");
        ?>
                    </td>
                </tr>
              
                <tr>
                    <td class="nowrap" valign="top">
        <?php
        $link = Route::_('index.php?option=com_sportsmanagement&view=databasetool&task=databasetool.updatetemplatemasters');
        ?>
                      
                        <a  href="<?php echo $link; ?>" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_UPDATE_TEMPLATE_MASTERS2'); ?>">
        <?php
        echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_UPDATE_TEMPLATE_MASTERS');
        ?>
                        </a>
                    </td>
                    <td>
        <?php
        echo Text::_("COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_UPDATE_TEMPLATE_MASTERS_DESCR");
        ?>
                    </td>
                </tr>

            </tbody>
        </table>
<!--	</div> -->

    <input type="hidden" name="task" value="databasetool.execute" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>
