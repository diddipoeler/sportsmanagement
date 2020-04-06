<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       edit.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage match
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

/**
 * Match Form
 *
 * @author  diddipoeler
 * @package SportManagement
 * @since   0.1
 */
?>
<div id="matchdetails">
  
    <form action="<?php echo Route::_('index.php?option=com_sportsmanagement&task=match.edit&tmpl=component'); ?>" id="adminForm" method="post" name="adminForm" >
        <!-- Score Table START -->
    <?php
    //save and close
    $close = Factory::getApplication()->input->getInt('close', 0);
    if($close == 1) {
        ?><script>
       window.addEvent('domready', function() {
      $('cancel').onclick();  
       });
       </script>
        <?php
    }
    ?>
            <fieldset>
                <div class="fltrt">
                    <button type="button" onclick="Joomla.submitform('match.apply', this.form);">
        <?php echo Text::_('JAPPLY');?></button>
                    <button type="button" onclick="Joomla.submitform('match.save', this.form);">
        <?php echo Text::_('JSAVE');?></button>
                    <button id="cancel" type="button" onclick="Joomla.submitform('match.cancelmodal', this.form);">
        <?php echo Text::_('JCANCEL');?></button>
                </div>
                <div class="configuration" >
        <?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_TITLE', $this->match->hometeam, $this->match->awayteam); ?>
                </div>
            </fieldset>
    <?php
    // focus matchreport tab when the match was already played
    $startOffset = 0;
    if (strtotime($this->match->match_date) < time() ) {
        $startOffset = 4;
    }
      
        // welche joomla version
    if(version_compare(JVERSION, '3.0.0', 'ge')) {
        // Define tabs options for version of Joomla! 3.1
        $tabsOptionsJ31 = array(
            "active" => "panel1" // It is the ID of the active tab.
            );

        echo HTMLHelper::_('bootstrap.startTabSet', 'ID-Tabs-J31-Group', $tabsOptionsJ31);
        echo HTMLHelper::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel1', Text::_('COM_SPORTSMANAGEMENT_TABS_MATCHPREVIEW'));
        echo $this->loadTemplate('matchpreview');
        echo HTMLHelper::_('bootstrap.endTab');
        echo HTMLHelper::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel2', Text::_('COM_SPORTSMANAGEMENT_TABS_MATCHDETAILS'));
        echo $this->loadTemplate('matchdetails');
        echo HTMLHelper::_('bootstrap.endTab');
        echo HTMLHelper::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel3', Text::_('COM_SPORTSMANAGEMENT_TABS_SCOREDETAILS'));
        echo $this->loadTemplate('scoredetails');
        echo HTMLHelper::_('bootstrap.endTab');
        echo HTMLHelper::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel5', Text::_('COM_SPORTSMANAGEMENT_TABS_MATCHREPORT'));
        echo $this->loadTemplate('matchreport');
        echo HTMLHelper::_('bootstrap.endTab');
        echo HTMLHelper::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel6', Text::_('COM_SPORTSMANAGEMENT_TABS_MATCHRELATION'));
        echo $this->loadTemplate('matchrelation');
        echo HTMLHelper::_('bootstrap.endTab');
        echo HTMLHelper::_('bootstrap.addTab', 'ID-Tabs-J31-Group', 'panel7', Text::_('COM_SPORTSMANAGEMENT_TABS_EXTENDED'));
        echo $this->loadTemplate('matchextended');
        echo HTMLHelper::_('bootstrap.endTab');
        echo HTMLHelper::_('bootstrap.endTabSet');  
    }
    else
    {
        echo HTMLHelper::_('tabs.start', 'tabs', array('startOffset'=>$startOffset));
        echo HTMLHelper::_('tabs.panel', Text::_('COM_SPORTSMANAGEMENT_TABS_MATCHPREVIEW'), 'panel1');
        echo $this->loadTemplate('matchpreview');
      
        echo HTMLHelper::_('tabs.panel', Text::_('COM_SPORTSMANAGEMENT_TABS_MATCHDETAILS'), 'panel2');
        echo $this->loadTemplate('matchdetails');
      
        echo HTMLHelper::_('tabs.panel', Text::_('COM_SPORTSMANAGEMENT_TABS_SCOREDETAILS'), 'panel3');
        echo $this->loadTemplate('scoredetails');
      

      
        echo HTMLHelper::_('tabs.panel', Text::_('COM_SPORTSMANAGEMENT_TABS_MATCHREPORT'), 'panel5');
        echo $this->loadTemplate('matchreport');
      
        echo HTMLHelper::_('tabs.panel', Text::_('COM_SPORTSMANAGEMENT_TABS_MATCHRELATION'), 'panel6');
        echo $this->loadTemplate('matchrelation');
      
        echo HTMLHelper::_('tabs.panel', Text::_('COM_SPORTSMANAGEMENT_TABS_EXTENDED'), 'panel7');
        echo $this->loadTemplate('matchextended');
      

      
        echo HTMLHelper::_('tabs.end');
    }  
    ?>
        <!-- Additional Details Table END -->
        <div class="clr"></div>
      
        <input type="hidden" name="task" value="match.edit"/>
        <input type="hidden" name="close" id="close" value="0"/>
        <input type="hidden" name="id" id="close" value="<?php echo $this->item->id; ?>"/>
        <input type="hidden" name="component" value="" />
    <?php echo HTMLHelper::_('form.token')."\n"; ?>
    </div>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?> 
