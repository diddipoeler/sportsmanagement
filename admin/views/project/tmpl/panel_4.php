<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 *
 * @version   1.0.60
 * @file      panel_4.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="row">
    <?php if (!empty($this->sidebar)) : ?>
        <div id="j-sidebar-container" class="col-md-2">
            <?php echo $this->sidebar; ?>
        </div>
        <div class="col-md-8">
        <?php else : ?>
            <div class="col-md-10">
        <?php endif; ?>      
            <div id="dashboard-iconss" class="dashboard-icons">
                <a class="btn btn-jsm-dash" href="index.php?option=com_sportsmanagement&task=project.edit&id=<?PHP echo $this->project->id; ?>">
                    <img src="components/com_sportsmanagement/assets/icons/projekte.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_PSETTINGS') ?>" /><br />
                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_PSETTINGS') ?></span>
                </a>
                <a class="btn btn-jsm-dash" href="index.php?option=com_sportsmanagement&view=templates&pid=<?PHP echo $this->project->id; ?>">
                    <img src="components/com_sportsmanagement/assets/icons/templates.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_FES') ?>" /><br />
                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_FES') ?></span>
                </a>
                <?php
                if ((isset($this->project->project_type)) 
                    && (($this->project->project_type == 'PROJECT_DIVISIONS') 
                    || ($this->project->project_type == 'DIVISIONS_LEAGUE'))
                ) {
                    ?>
                    <a class="btn btn-jsm-dash" href="index.php?option=com_sportsmanagement&view=divisions&pid=<?PHP echo $this->project->id; ?>">
                        <img src="components/com_sportsmanagement/assets/icons/divisionen.png" alt="<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_DIVISIONS', $this->count_projectdivisions) ?>" /><br />
                        <span><?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_DIVISIONS', $this->count_projectdivisions) ?></span>
                    </a>                                
                    <?php
                }
                if ((isset($this->project->project_type)) 
                    && (($this->project->project_type == 'TOURNAMENT_MODE') 
                    || ($this->project->project_type == 'DIVISIONS_LEAGUE'))
                ) {
                    ?>
                    <a class="btn btn-jsm-dash" href="index.php?option=com_sportsmanagement&view=treetos&pid=<?PHP echo $this->project->id; ?>">
                        <img src="components/com_sportsmanagement/assets/icons/turnierbaum.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_TREE') ?>" /><br />
                        <span><?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_TREE') ?></span>
                    </a>
                    <?PHP
                }
                if ($this->project->project_art_id != 3) {
                    ?>
                    <a class="btn btn-jsm-dash" href="index.php?option=com_sportsmanagement&view=projectpositions&pid=<?PHP echo $this->project->id; ?>">
                        <img src="components/com_sportsmanagement/assets/icons/positionen.png" alt="<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_POSITIONS', $this->count_projectpositions) ?>" /><br />
                        <span><?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_POSITIONS', $this->count_projectpositions) ?></span>
                    </a>
                    <?PHP
                }
                ?>
                <a class="btn btn-jsm-dash" href="index.php?option=com_sportsmanagement&view=projectreferees&persontype=3&pid=<?PHP echo $this->project->id; ?>">
                    <img src="components/com_sportsmanagement/assets/icons/projektschiedsrichter.png" alt="<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_REFEREES', $this->count_projectreferees) ?>" /><br />
                    <span><?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_REFEREES', $this->count_projectreferees) ?></span>
                </a>
                <a class="btn btn-jsm-dash" href="index.php?option=com_sportsmanagement&view=projectteams&pid=<?PHP echo $this->project->id; ?>">
                    <img src="components/com_sportsmanagement/assets/icons/mannschaften.png" alt="<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_TEAMS', $this->count_projectteams) ?>" /><br />
                    <span><?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_TEAMS', $this->count_projectteams) ?></span>
                </a>
                <a class="btn btn-jsm-dash" href="index.php?option=com_sportsmanagement&view=rounds&pid=<?PHP echo $this->project->id; ?>">
                    <img src="components/com_sportsmanagement/assets/icons/spieltage.png" alt="<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_MATCHDAYS', $this->count_matchdays) ?>" /><br />
                    <span><?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_MATCHDAYS', $this->count_matchdays) ?></span>
                </a>
                <a class="btn btn-jsm-dash" href="index.php?option=com_sportsmanagement&view=jlxmlexports&pid=<?PHP echo $this->project->id; ?>">
                    <img src="components/com_sportsmanagement/assets/icons/xmlexport.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_XML_EXPORT') ?>" /><br />
                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_XML_EXPORT') ?></span>
                </a>
            </div>        
        </div>
        <div class="col-md-2">
            <?php sportsmanagementHelper::jsminfo(); ?>
        </div>
    </div>
    <?php echo $this->table_data_div; ?>

