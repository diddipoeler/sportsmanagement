<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      panel.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

// Get a refrence of the page instance in joomla
$document  = Factory::getDocument();
$stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css' . '" type="text/css" />' . "\n";
$document->addCustomTag($stylelink);
$path = '/administrator/components/com_sportsmanagement/assets/icons/';
$user = Factory::getUser();
ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_CONTROL_PANEL_TITLE'), 'projects');

?>
<div id="j-main-container">
    <div id="jsm" class="admin override">

        <div id="j-main-container" class="span10">
            <section class="content-block" role="main">

                <div class="row-fluid">
                    <div class="span7">
<?php
echo $this->loadTemplate('jsm_warnings');
echo $this->loadTemplate('jsm_notes');			    
echo $this->loadTemplate('jsm_tips');			    
?>	
                        <div class="well well-small">
                            <div id="dashboard-icons" class="btn-group">
                                <div id="element-box">
                                    <div class="m">
                                        <div class="adminform">
                                            <legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_CONTROL_PANEL_LEGEND', '<i>' . $this->project->name . '</i>'); ?></legend>
                                            <div class="cpanel">
												<?php
												$link       = Route::_('index.php?option=com_sportsmanagement&task=project.edit&id=' . $this->project->id);
												$text       = Text::_('COM_SPORTSMANAGEMENT_P_PANEL_PSETTINGS');
												$imageFile  = 'projekte.png';
												$linkParams = "<span>$text</span>&nbsp;";
												$image      = HTMLHelper::_('image.administrator', $imageFile, $path, null, null, $text) . $linkParams;
												?>
                                                <div class="icon-wrapper">
                                                    <div class="icon"><?php echo HTMLHelper::link($link, $image); ?></div>
                                                </div>
												<?php
												$link       = Route::_('index.php?option=com_sportsmanagement&view=templates&pid=' . $this->project->id);
												$text       = Text::_('COM_SPORTSMANAGEMENT_P_PANEL_FES');
												$imageFile  = 'templates.png';
												$linkParams = "<span>$text</span>&nbsp;";
												$image      = HTMLHelper::_('image.administrator', $imageFile, $path, null, null, $text) . $linkParams;
												?>
                                                <div class="icon-wrapper">
                                                    <div class="icon"><?php echo HTMLHelper::link($link, $image); ?></div>
                                                </div>
												<?php
												if ((isset($this->project->project_type))
													&& (($this->project->project_type == 'PROJECT_DIVISIONS')
														|| ($this->project->project_type == 'DIVISIONS_LEAGUE'))
												)
												{
													$link       = Route::_('index.php?option=com_sportsmanagement&view=divisions&pid=' . $this->project->id);
													$text       = Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_DIVISIONS', $this->count_projectdivisions);
													$imageFile  = 'divisionen.png';
													$linkParams = "<span>$text</span>&nbsp;";
													$image      = HTMLHelper::_('image.administrator', $imageFile, $path, null, null, $text) . $linkParams;
													?>
                                                    <div class="icon-wrapper">
                                                        <div class="icon"><?php echo HTMLHelper::link($link, $image); ?></div>
                                                    </div>
													<?php
												}
												if ((isset($this->project->project_type))
													&& (($this->project->project_type == 'TOURNAMENT_MODE')
														|| ($this->project->project_type == 'DIVISIONS_LEAGUE'))
												)
												{
													$link       = Route::_('index.php?option=com_sportsmanagement&view=treetos&pid=' . $this->project->id);
													$text       = Text::_('COM_SPORTSMANAGEMENT_P_PANEL_TREE');
													$imageFile  = 'turnierbaum.png';
													$linkParams = "<span>$text</span>&nbsp;";
													$image      = HTMLHelper::_('image.administrator', $imageFile, $path, null, null, $text) . $linkParams;
													?>
                                                    <div class="icon-wrapper">
                                                        <div class="icon"><?php echo HTMLHelper::link($link, $image); ?></div>
                                                    </div>
													<?php
												}

												if ($this->project->project_art_id != 3)
												{
													$link       = Route::_('index.php?option=com_sportsmanagement&view=projectpositions&pid=' . $this->project->id);
													$text       = Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_POSITIONS', $this->count_projectpositions);
													$imageFile  = 'positionen.png';
													$linkParams = "<span>$text</span>&nbsp;";
													$image      = HTMLHelper::_('image.administrator', $imageFile, $path, null, null, $text) . $linkParams;
													?>
                                                    <div class="icon-wrapper">
                                                        <div class="icon"><?php echo HTMLHelper::link($link, $image); ?></div>
                                                    </div>
													<?php
												}
												$link       = Route::_('index.php?option=com_sportsmanagement&view=projectreferees&persontype=3&pid=' . $this->project->id);
												$text       = Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_REFEREES', $this->count_projectreferees);
												$imageFile  = 'projektschiedsrichter.png';
												$linkParams = "<span>$text</span>&nbsp;";
												$image      = HTMLHelper::_('image.administrator', $imageFile, $path, null, null, $text) . $linkParams;
												?>
                                                <div class="icon-wrapper">
                                                    <div class="icon"><?php echo HTMLHelper::link($link, $image); ?></div>
                                                </div>
												<?php
												$link       = Route::_('index.php?option=com_sportsmanagement&view=projectteams&pid=' . $this->project->id);
												$text       = Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_TEAMS', $this->count_projectteams);
												$imageFile  = 'mannschaften.png';
												$linkParams = "<span>$text</span>&nbsp;";
												$image      = HTMLHelper::_('image.administrator', $imageFile, $path, null, null, $text) . $linkParams;
												?>
                                                <div class="icon-wrapper">
                                                    <div class="icon"><?php echo HTMLHelper::link($link, $image); ?></div>
                                                </div>
												<?php
												$link       = Route::_('index.php?option=com_sportsmanagement&view=rounds&pid=' . $this->project->id);
												$text       = Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_MATCHDAYS', $this->count_matchdays);
												$imageFile  = 'spieltage.png';
												$linkParams = "<span>$text</span>&nbsp;";
												$image      = HTMLHelper::_('image.administrator', $imageFile, $path, null, null, $text) . $linkParams;
												?>
                                                <div class="icon-wrapper">
                                                    <div class="icon"><?php echo HTMLHelper::link($link, $image); ?></div>
                                                </div>
												<?php
												$link       = Route::_('index.php?option=com_sportsmanagement&view=jlxmlexports&pid=' . $this->project->id);
												$text       = Text::_('COM_SPORTSMANAGEMENT_P_PANEL_XML_EXPORT');
												$imageFile  = 'xmlexport.png';
												$linkParams = "<span>$text</span>&nbsp;";
												$image      = HTMLHelper::_('image.administrator', $imageFile, $path, null, null, $text) . $linkParams;
												?>
                                                <div class="icon-wrapper">
                                                    <div class="icon"><?php echo HTMLHelper::link($link, $image); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="element-box">
                                    <div class="m">
                                        <div class="adminform">
                                            <div class="cpanel"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_CONTROL_PANEL_HINT'); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <!-- bottom close main table opened in default_admin -->
                                </td>
                                </tr>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?> 
