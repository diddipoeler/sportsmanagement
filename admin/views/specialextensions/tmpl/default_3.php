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
 * @subpackage specialextensions
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
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
                            <div id="dashboard-icons" class="btn-group">
                                <?php
                                foreach ($this->Extensions as $key => $value) {
                                    $logo = "components/com_sportsmanagement/assets/icons/" . Text::_($value) . '.png';
                                    if (!file_exists($logo)) {
                                        $logo = Uri::root() . 'images/com_sportsmanagement/database/placeholders/placeholder_150.png';
                                    }
                                    ?>
                                    <a class="btn" href="index.php?option=com_sportsmanagement&view=<?php echo Text::_($value) ?>">
                                        <img src="<?php echo $logo ?>" width="125" alt="<?php echo Text::_($value) ?>" /><br />
                                        <span><?php echo Text::_($value) ?></span>
                                    </a>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="span3">
                        <?php sportsmanagementHelper::jsminfo(); ?>
                    </div>
                </div>
            </section>
        </div>
    </div>                
    <?PHP
    echo "<div>";
    echo $this->loadTemplate('footer');
    echo "</div>";
    ?>   
