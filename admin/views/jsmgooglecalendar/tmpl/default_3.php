<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_3.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage jsmgooglecalendar
 */

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
Factory::getDocument()->addStyleSheet('components/com_sportsmanagement/views/jsmgooglecalendar/tmpl/default.css');   

$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>

<div id="jsm" class="admin override">

<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>

<section class="content-block" role="main">

<div class="row-fluid">
<div class="span7">
<div class="well well-small">   
<div class="module-title nav-header">
<h2>
<?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_WELCOME') ?>
</h2>
<p>
<?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_INTRO'); ?>
</p> 

</div>
     
<div id="dashboard-icons" class="btn-group">

<a class="btn" href="index.php?option=com_sportsmanagement&view=jsmgcalendars">
<img src="components/com_sportsmanagement/assets/images/48-calendar.png" width="50px"height="50px" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_GCALENDARS') ?>" /><br />
<span><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_GCALENDARS') ?></span>
</a>
<a class="btn" href="index.php?option=com_sportsmanagement&view=jsmgcalendarimport&layout=login">
<img src="components/com_sportsmanagement/assets/images/admin/import.png" width="50px"height="50px" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_IMPORT') ?>" /><br />
<span><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_IMPORT') ?></span>
</a>
<a class="btn" href="index.php?option=com_sportsmanagement&view=jsmgcalendar&layout=edit">
<img src="components/com_sportsmanagement/assets/images/admin/add.png" width="50px"height="50px" alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_ADD') ?>" /><br />
<span><?php echo Text::_('COM_SPORTSMANAGEMENT_JSMGCALENDAR_VIEW_CPANEL_ADD') ?></span>
</a>

     
        
</div>        
</div>
</div>

<div class="span5">
					<div class="well well-small">
						<div class="center">
							<img src="components/com_sportsmanagement/assets/icons/boxklein.png" />
						</div>
						<hr class="hr-condensed">
						<dl class="dl-horizontal">
							<dt><?php echo Text::_('COM_SPORTSMANAGEMENT_VERSION') ?>:</dt>
							<dd><?php echo Text::sprintf( '%1$s', sportsmanagementHelper::getVersion() ); ?></dd>
                            
							<dt><?php echo Text::_('COM_SPORTSMANAGEMENT_DEVELOPERS') ?>:</dt>
							<dd><?php echo Text::_('COM_SPORTSMANAGEMENT_DEVELOPER_TEAM'); ?></dd>

							
                            <dt><?php echo Text::_('COM_SPORTSMANAGEMENT_SITE_LINK') ?>:</dt>
							<dd><a href="http://www.fussballineuropa.de" target="_blank">fussballineuropa</a></dd>
							
                            <dt><?php echo Text::_('COM_SPORTSMANAGEMENT_COPYRIGHT') ?>:</dt>
							<dd>&copy; 2014 fussballineuropa, All rights reserved.</dd>
							
                            <dt><?php echo Text::_('COM_SPORTSMANAGEMENT_LICENSE') ?>:</dt>
							<dd>GNU General Public License</dd>
						</dl>
					</div>

					

				</div>


</div>

</section>
</div>
</div>