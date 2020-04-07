<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage updates
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<style>
/*  #myModal1 .modal-dialog {
    width: 80%;
  }
*/

/*
.modaljsm {
    width: 80%;
    height: 60%;
  }
  */
.modal-dialog {
    width: 80%;
  }
.modal-dialog,
.modal-content {
    /* 95% of window height */
    height: 95%;
}


</style>

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

    <?php
    // tabs anzeigen
    $idxTab = 1;
    echo HTMLHelper::_('tabs.start', 'tabs_updates', array('useCookie'=>1));
    echo HTMLHelper::_('tabs.panel', Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_LIST'), 'panel'.($idxTab++));
    ?>
  <table class="table">
        <thead>
            <tr>
                <th width="5" style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
                <th class="title" class="nowrap"><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_UPDATES_FILE', 'name', $this->sortDirection, $this->sortColumn); ?></th>
                <th class="title" class="nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_DESCR'); ?></th>
                <th class="title" class="nowrap"><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_UPDATES_VERSION', 'version', $this->sortDirection, $this->sortColumn); ?></th>
                <th class="title" class="nowrap"><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_UPDATES_DATE', 'date', $this->sortDirection, $this->sortColumn); ?></th>
                <th class="title" class="nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_EXECUTED'); ?></th>
                <th class="title" class="nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_COUNT');?></th>
            </tr>
        </thead>
        <tfoot><tr><td colspan='7'><?php echo '&nbsp;'; ?></td></tr></tfoot>
        <tbody><?php
        $k=0;
        for ($i=0, $n=count($this->updateFiles); $i < $n; $i++)
        {
            $row =& $this->updateFiles[$i];
            $link=Route::_('index.php?option=com_sportsmanagement&view=updates&task=update.save&file_name='.$row['file_name']);
            ?>
           <tr class="<?php echo "row$k"; ?>">
          <td class="center"><?php echo $i+1; ?></td>
            <?php
            $linkTitle=$row['file_name'];
            $linkParams="title='".Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_MAKE_UPDATE')."'";
                    $link = 'index.php?option=com_sportsmanagement&tmpl=component&view=update&task=update.save&file_name='.$row['file_name'];
                    ?>
                    <td class="center" nowrap="nowrap">
                    <!--
                <a    rel="{handler: 'iframe',size: {x: <?php echo $modalwidth; ?>,y: <?php echo $modalheight; ?>}}"
                 href="index.php?option=com_sportsmanagement&tmpl=component&view=update&task=update.save&file_name=<?php echo $row['file_name']; ?>"
                  class="modal">
                                    <?php
                                    echo $row['file_name'];
                                    ?>
                            </a>
                  
                            <a    href="javascript:openLink('<?php echo $link; ?>')">
                    <?php
                                   
                    $image = 'icon-16-Teams.png';
                    $title=  '';
                    echo HTMLHelper::_(
                        'image', 'administrator/components/com_sportsmanagement/assets/images/'.$image,
                        Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_EDIT_DETAILS'),
                        'title= "' .$title. '"'
                    );
                                                   
                                      
                                                                            ?>
                </a>
                                    -->
        <?PHP
        $name = "myModal";
        $html = '<a href="#modal-' . $i.'" data-toggle="modal" class="btn">'.$row['file_name'].'</a>';
        $params = array();
        $params['title']  = "test";
        $params['url']    = 'index.php?option=com_sportsmanagement&tmpl=component&view=update&task=update.save&file_name='.$row['file_name'];
        $params['height'] = 400;
        $params['width']  = "100%";
        echo $html .= HTMLHelper::_('bootstrap.renderModal', 'modal-' . $i, $params);

        ?>                                               
                    </td>
                <td><?php
                if($row['updateDescription'] != "") {
                    echo $row['updateDescription'];
                }
                else
                    {
                    echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_UPDATE', $row['last_version'], $row['version']);
                }
        ?></td>
        <td class="center"><?php echo $row['version']; ?></td>
        <td class="center"><?php echo Text::_($row['updateFileDate']).' '.Text::_($row['updateFileTime']); ?></td>
        <td class="center"><?php echo $row['date']; ?></td>
        <td class="center"><?php echo $row['count']; ?></td>
           </tr>
            <?php
            $k=1 - $k;
        }
    ?></tbody>
    </table>
  
    <?PHP
    echo HTMLHelper::_('tabs.panel', Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_HISTORY'), 'panel'.($idxTab++));
    foreach ( $this->versionhistory as $history )
    {
        ?>
       <fieldset>
       <legend>
      <strong>
        <?php
        //echo $history->date;
        echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_VERSIONEN', $history->version, HTMLHelper::date($history->date, Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_DAYDATE')));
        ?>
      </strong>
      </legend>
        <?php
        //echo $history->text;
        echo Text::_($history->text);
        ?>
       </fieldset>
        <?PHP
    }
    echo HTMLHelper::_('tabs.end');
    ?>
  
  
    <input type="hidden" name="view" value="updates" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />
    <?php echo HTMLHelper::_('form.token')."\n"; ?>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?> 
