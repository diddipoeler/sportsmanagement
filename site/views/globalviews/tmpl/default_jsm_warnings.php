<?php
/**
 * Joomla 5/6 shared frontend warnings layout.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage globalviews
 * @file       default_jsm_warnings.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if (!empty($this->warnings)) :
    $warnings = implode('<br>', (array) $this->warnings);
    ?>
    <div class="color-box">
        <div class="shadow">
            <div class="fas fa-exclamation-triangle fa-2x fa-pull-left"
                 title="<?php echo htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_WARNING'), ENT_QUOTES, 'UTF-8'); ?>">
                <i aria-hidden="true"></i>
            </div>
            <div class="warning-box">
                <p>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_WARNING'); ?></strong>
                    <?php echo $warnings; ?>
                </p>
            </div>
        </div>
    </div>
<?php endif; ?>
