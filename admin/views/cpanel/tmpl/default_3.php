<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       default_3.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage cpanel
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
jimport('joomla.html.html.bootstrap');
?>
<div id="jsm" class="admin override">
    <?php if (!empty($this->sidebar)) : ?>
        <div id="j-sidebar-container" class="span2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="span10">
        <?php else : ?>
            <div id="j-main-container">
        <?php endif; ?>
            <section class="content-block" role="main">
                <div class="row-fluid">
                    <div class="span9">
                        <div class="well well-small">
                            <div class="module-title nav-header"><?php echo Text::_('COM_SPORTSMANAGEMENT_D_HEADING_BASIS_DATA') ?>
                            </div>
                            <hr class="hr-condensed">    
                            <div id="dashboard-icons" class="btn-group">
                                <a class="btn" href="index.php?option=com_sportsmanagement&view=sportstypes">
                                    <img src="components/com_sportsmanagement/assets/icons/sportarten.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_SPORTSTYPES') ?>" /><br />
                                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_SPORTSTYPES') ?></span>
                                </a>
                                <a class="btn" href="index.php?option=com_sportsmanagement&view=seasons">
                                    <img src="components/com_sportsmanagement/assets/icons/saisons.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_SEASONS') ?>" /><br />
                                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_SEASONS') ?></span>
                                </a>
                                <a class="btn" href="index.php?option=com_sportsmanagement&view=leagues">
                                    <img src="components/com_sportsmanagement/assets/icons/ligen.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_LEAGUES') ?>" /><br />
                                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_LEAGUES') ?></span>
                                </a>
                                <?PHP
                                if ($this->params->get('show_option_federation', 1)) {
                                    ?>
                                    <a class="btn" href="index.php?option=com_sportsmanagement&view=jlextfederations">
                                        <img src="components/com_sportsmanagement/assets/icons/federation.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_FEDERATIONS') ?>" /><br />
                                        <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_FEDERATIONS') ?></span>
                                    </a>
                                    <?PHP
                                }
                                ?>
                                <a class="btn" href="index.php?option=com_sportsmanagement&view=jlextcountries">
                                    <img src="components/com_sportsmanagement/assets/icons/laender.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_COUNTRIES') ?>" /><br />
                                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_COUNTRIES') ?></span>
                                </a>
                                <?PHP
                                if ($this->params->get('show_option_association', 1)) {
                                    ?>
                                    <a class="btn" href="index.php?option=com_sportsmanagement&view=jlextassociations">
                                        <img src="components/com_sportsmanagement/assets/icons/landesverbaende.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_ASSOCIATIONS') ?>" /><br />
                                        <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_ASSOCIATIONS') ?></span>
                                    </a>
                                    <?PHP
                                }
                                if ($this->params->get('show_option_position', 1)) {
                                    ?>
                                    <a class="btn" href="index.php?option=com_sportsmanagement&view=positions">
                                        <img src="components/com_sportsmanagement/assets/icons/positionen.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_POSITIONS') ?>" /><br />
                                        <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_POSITIONS') ?></span>
                                    </a>
                                    <?PHP
                                }
                                if ($this->params->get('show_option_eventtypes', 1)) {
                                    ?>
                                    <a class="btn" href="index.php?option=com_sportsmanagement&view=eventtypes">
                                        <img src="components/com_sportsmanagement/assets/icons/ereignisse.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_EVENTS') ?>" /><br />
                                        <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_EVENTS') ?></span>
                                    </a>
                                    <?PHP
                                }
                                if ($this->params->get('show_option_agegroup', 1)) {
                                    ?>
                                    <a class="btn" href="index.php?option=com_sportsmanagement&view=agegroups">
                                        <img src="components/com_sportsmanagement/assets/icons/altersklassen.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_AGEGROUPS') ?>" /><br />
                                        <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_AGEGROUPS') ?></span>
                                    </a>   
                                    <?PHP
                                }
                                ?>      
                            </div>

                            <hr class="hr-condensed">
                            <div class="module-title nav-header"><?php echo Text::_('COM_SPORTSMANAGEMENT_D_HEADING_PERSONAL_DATA') ?>
                            </div>
                            <hr class="hr-condensed">  
                            <div id="dashboard-icons" class="btn-group">

                                <a class="btn" href="index.php?option=com_sportsmanagement&view=clubs">
                                    <img src="components/com_sportsmanagement/assets/icons/vereine.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_CLUBS') ?>" /><br />
                                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_CLUBS') ?></span>
                                </a>

                                <a class="btn" href="index.php?option=com_sportsmanagement&view=teams">
                                    <img src="components/com_sportsmanagement/assets/icons/mannschaften.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_TEAMS') ?>" /><br />
                                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_TEAMS') ?></span>
                                </a>
                                <?PHP
                                if ($this->params->get('show_option_person', 1)) {
                                    ?>
                                    <a class="btn" href="index.php?option=com_sportsmanagement&view=players">
                                        <img src="components/com_sportsmanagement/assets/icons/personen.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_PERSONS') ?>" /><br />
                                        <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_PERSONS') ?></span>
                                    </a>
                                    <?PHP
                                }
                                if ($this->params->get('show_option_playground', 1)) {
                                    ?>
                                    <a class="btn" href="index.php?option=com_sportsmanagement&view=playgrounds">
                                        <img src="components/com_sportsmanagement/assets/icons/spielorte.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_VENUES') ?>" /><br />
                                        <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_VENUES') ?></span>
                                    </a>
                                    <?PHP
                                }
                                if ($this->params->get('show_option_rosterposition', 1)) {
                                    ?>
                                    <a class="btn" href="index.php?option=com_sportsmanagement&view=rosterpositions">
                                        <img src="components/com_sportsmanagement/assets/icons/spielfeldpositionen.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_ROSTER_POSITION') ?>" /><br />
                                        <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_ROSTER_POSITION') ?></span>
                                    </a>
                                    <?PHP
                                }
                                ?>
                            </div>
                            <hr class="hr-condensed">
                            <div class="module-title nav-header"><?php echo Text::_('COM_SPORTSMANAGEMENT_D_HEADING_SPECIAL_FUNCTION') ?>
                            </div>
                            <hr class="hr-condensed">  
                            <div id="dashboard-icons" class="btn-group">
                                <?PHP
                                if ($this->params->get('show_option_extrafields', 1)) {
                                    ?>
                                    <a class="btn" href="index.php?option=com_sportsmanagement&view=extrafields">
                                        <img src="components/com_sportsmanagement/assets/icons/extrafelder.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_EXTRAFIELDS') ?>" /><br />
                                        <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_EXTRAFIELDS') ?></span>
                                    </a>
                                    <?PHP
                                }
                                if ($this->params->get('show_option_statistics', 1)) {
                                    ?>
                                    <a class="btn" href="index.php?option=com_sportsmanagement&view=statistics">
                                        <img src="components/com_sportsmanagement/assets/icons/statistik.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_STATISTICS') ?>" /><br />
                                        <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_STATISTICS') ?></span>
                                    </a>
                                    <?PHP
                                }
                                if ($this->params->get('show_option_clubnames', 1)) {
                                    ?>
                                    <a class="btn" href="index.php?option=com_sportsmanagement&view=clubnames">
                                        <img src="components/com_sportsmanagement/assets/icons/statistik.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_CLUBNAMES') ?>" /><br />
                                        <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_CLUBNAMES') ?></span>
                                    </a>
                                    <?PHP
                                }
                                if ($this->params->get('show_option_github', 1)) {
                                    ?>
                                    <a class="btn" href="index.php?option=com_sportsmanagement&view=github">
                                        <img src="components/com_sportsmanagement/assets/icons/github.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_GITHUB') ?>" /><br />
                                        <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_GITHUB') ?></span>
                                    </a>
                                    <?PHP
                                }
                                ?>
                            </div>
                            <hr class="hr-condensed">
                            <div class="module-title nav-header"><?php echo Text::_('COM_SPORTSMANAGEMENT_D_HEADING_IMPORT_EXPORT_FUNCTION') ?>
                            </div>
                            <hr class="hr-condensed">  
                            <div id="dashboard-icons" class="btn-group">
                                <a class="btn" href="index.php?option=com_sportsmanagement&view=jlxmlimports&layout=default">
                                    <img src="components/com_sportsmanagement/assets/icons/xmlimport.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_XML_IMPORT') ?>" /><br />
                                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_XML_IMPORT') ?></span>
                                </a>
                                <!--
                                <a class="btn" href="index.php?option=com_sportsmanagement&view=smextxmleditors&layout=default">
                                    <img src="components/com_sportsmanagement/assets/icons/xmleditor.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_XML_EDITOR') ?>" /><br />
                                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_XML_EDITOR') ?></span>
                                </a> -->
                                <a class="btn" href="index.php?option=com_sportsmanagement&view=smimageimports&layout=default">
                                    <img src="components/com_sportsmanagement/assets/icons/imageimport.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_IMAGE_IMPORT') ?>" /><br />
                                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_IMAGE_IMPORT') ?></span>
                                </a>
                                <a class="btn" href="index.php?option=com_sportsmanagement&view=joomleagueimports&layout=default">
                                    <img src="components/com_sportsmanagement/assets/icons/joomleague.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_JOOMLEAGUE_IMPORT') ?>" /><br />
                                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_JOOMLEAGUE_IMPORT') ?></span>
                                </a>
                            </div>
                            <hr class="hr-condensed">
                            <div class="module-title nav-header"><?php echo Text::_('COM_SPORTSMANAGEMENT_D_HEADING_INSTALL_TOOLS') ?>
                            </div>
                            <hr class="hr-condensed">  
                            <div id="dashboard-icons" class="btn-group">
                                <a class="btn" href="index.php?option=com_sportsmanagement&view=updates">
                                    <img src="components/com_sportsmanagement/assets/icons/updates.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_UPDATES') ?>" /><br />
                                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_UPDATES') ?></span>
                                </a>
                                <a class="btn" href="index.php?option=com_sportsmanagement&view=databasetools">
                                    <img src="components/com_sportsmanagement/assets/icons/datenbanktools.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_TOOLS') ?>" /><br />
                                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_TOOLS') ?></span>
                                </a>
                                <?PHP
                                if ($this->params->get('show_option_smquotes', 1)) {
                                    ?>
                                    <a class="btn" href="index.php?option=com_sportsmanagement&view=smquotes">
                                        <img src="components/com_sportsmanagement/assets/icons/zitate.png" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_QUOTES') ?>" /><br />
                                        <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_QUOTES') ?></span>
                                    </a>
                                    <?PHP
                                }
                                ?>

<a class="btn" href="index.php?option=com_sportsmanagement&view=transifex">
<img src="components/com_sportsmanagement/assets/icons/transifex.png" width="48" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_TRANSIFEX') ?>" /><br />
<span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_TRANSIFEX') ?></span>
</a>                             
                           
                           
                            </div>
                            <hr class="hr-condensed">
                        </div>
                    </div>
                    <div class="span3">
                        <?php sportsmanagementHelper::jsminfo(); ?>
                        <div class="center">
                            <?php
                            $start = 1;
                            // Define slides options
                            $slidesOptions = array(
                                "active" => "slide1_id" // It is the ID of the active tab.
                            );
                            // Define tabs options for version of Joomla! 3.0
                            $tabsOptions = array(
                                "active" => "tab1_id" // It is the ID of the active tab.
                            );

                            echo HTMLHelper::_('bootstrap.startAccordion', 'slide-group-id', $slidesOptions);

                            if (is_array($this->importData)) {
                                foreach ($this->importData as $key => $value) {
                                    echo HTMLHelper::_('bootstrap.addSlide', 'slide-group-id', Text::_($key), 'slide' . $start . '_id');
                                    echo $value;
                                    echo HTMLHelper::_('bootstrap.endSlide');
                                    $start++;
                                }
                            }
                            if (is_array($this->importData2)) {
                                foreach ($this->importData2 as $key => $value) {
                                    echo HTMLHelper::_('bootstrap.addSlide', 'slide-group-id', Text::_($key), 'slide' . $start . '_id');
                                    echo $value;
                                    echo HTMLHelper::_('bootstrap.endSlide');
                                    $start++;
                                }
                            }
                            echo HTMLHelper::_('bootstrap.endAccordion');
                            ?>
                        </div>                       
                    </div>                 
                </div>
            </section>
        </div>
    </div>
