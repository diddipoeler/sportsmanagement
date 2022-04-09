<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jsmgooglecalendar
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

Factory::getDocument()->addStyleSheet('components/com_sportsmanagement/views/jsmgooglecalendar/tmpl/default.css');

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>

<?php if (!empty($this->sidebar)) : ?>
<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
	<?php else : ?>
    <div id="j-main-container">
		<?php endif; ?>

        <div style="width:500px;">
            <h2><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_WELCOME'); ?></h2>
            <p>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_INTRO'); ?>
            </p>
            <br>

            <div id="cpanel" style="float:left">
                <div style="float:left;margin-right: 20px">
                    <div class="icon">
                        <a href="index.php?option=com_sportsmanagement&view=jsmgcalendars">
                            <img src="<?php echo Uri::base(true); ?>/../administrator/components/com_sportsmanagement/assets/images/48-calendar.png"
                                 height="50px" width="50px">
                            <span><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_GCALENDARS'); ?></span>
                        </a>
                    </div>
                    <div class="icon">
                        <a href="index.php?option=com_sportsmanagement&view=jsmgcalendarimport&layout=login">
                            <img src="<?php echo Uri::base(true); ?>/../administrator/components/com_sportsmanagement/assets/images/admin/import.png"
                                 height="50px" width="50px">
                            <span><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_IMPORT'); ?></span>
                        </a>
                    </div>
                    <div class="icon">
                        <a href="index.php?option=com_sportsmanagement&view=jsmgcalendar&layout=edit">
                            <img src="<?php echo Uri::base(true); ?>/../administrator/components/com_sportsmanagement/assets/images/admin/add.png"
                                 height="50px" width="50px">
                            <span><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_ADD'); ?></span>
                        </a>
                    </div>


                </div>
            </div>
        </div>
		<?PHP
		
		echo $this->loadTemplate('footer');
		
		?>
