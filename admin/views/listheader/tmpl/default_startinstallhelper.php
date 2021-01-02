<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage listheader
 * @file       default_startinstallhelper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

?>
<section class="content-block" role="main">
<div class="row-fluid">
<div class="span9">
<div class="well well-small">

<div id="dashboard-icons" class="btn-group">
                                <a class="btn" href="index.php?option=com_sportsmanagement&view=installhelper&step=1">
                                    <img src="components/com_sportsmanagement/assets/icons/wizard-1.png"
                                         alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_INSTALLHELPER_STEP1') ?>"/><br/>
                                    <span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_INSTALLHELPER_STEP1') ?></span>
                                </a>
</div>                                

</div>
</div>
</div>
</section>