<?php
/**
 * Joomla 5/6 shared frontend tips layout.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage globalviews
 * @file       default_jsm_tips.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if (!empty($this->tips)) :
    $tips = implode('<br>', (array) $this->tips);
    ?>
    <div class="color-box">
        <div class="shadow">
            <div class="fas fa-quote-right fa-2x fa-pull-left"
                 title="<?php echo htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_TIP'), ENT_QUOTES, 'UTF-8'); ?>">
                <i aria-hidden="true"></i>
            </div>
            <div class="tip-box">
                <p>
                    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_TIP'); ?></strong>
                    <?php echo $tips; ?>
                </p>
            </div>
        </div>
    </div>
<?php endif; ?>
