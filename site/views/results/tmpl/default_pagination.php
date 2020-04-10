<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage results
 * @file       default_pagination.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="defaultpagination">
    <form name="adminForm" id="adminForm" action="<?php echo htmlspecialchars($this->uri->toString()); ?>"
          method="post">
        <input type="hidden" name="limitstart" value=""/>
        <input type="hidden" name="view" value="<?php echo Factory::getApplication()->input->getVar('view'); ?>"/>
        <input type="hidden" name="option" value="<?php echo Factory::getApplication()->input->getCmd('option'); ?>"/>
        <input type="hidden" name="cfg_which_database"
               value="<?php echo Factory::getApplication()->input->getVar('cfg_which_database'); ?>"/>
        <input type="hidden" name="p" value="<?php echo Factory::getApplication()->input->getVar('p'); ?>"/>
        <input type="hidden" name="r" value="<?php echo Factory::getApplication()->input->getVar('r'); ?>"/>
        <input type="hidden" name="division"
               value="<?php echo Factory::getApplication()->input->getVar('division'); ?>"/>
        <div class="display-limit">
			<?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
			<?php echo $this->pagination->getLimitBox(); ?>
        </div>

        <div class="pagination">
            <p class="counter">
				<?php echo $this->pagination->getPagesCounter(); ?>
            </p>
            <p class="counter">
				<?php echo $this->pagination->getResultsCounter(); ?>
            </p>
			<?php echo $this->pagination->getPagesLinks(); ?>
        </div>

    </form>
</div>
