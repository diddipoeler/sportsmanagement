<?php
/**
 * Joomla 5/6 accordion renderer for shared SportsManagement frontend views.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage globalviews
 * @file       default_show_slider.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$output = is_array($this->output ?? null) ? $this->output : [];
$view = Factory::getApplication()->getInput()->getCmd('view');
?>
<div class="<?php echo htmlspecialchars((string) $this->divclassrow, ENT_QUOTES, 'UTF-8'); ?>" id="show_slider">
    <?php if ($output !== []) : ?>
        <?php echo HTMLHelper::_('bootstrap.startAccordion', 'collapseTypes', ['active' => '', 'parent' => 'collapseTypes']); ?>
        <?php $index = 1; ?>
        <?php foreach ($output as $key => $tab) : ?>
            <?php
            if ($view === 'player' && is_array($tab)) {
                $template = (string) ($tab['template'] ?? '');
                $text = (string) ($tab['text'] ?? $key);
            } else {
                $template = (string) $tab;
                $text = (string) $key;
            }

            if ($template === '') {
                continue;
            }

            $slideId = 'collapse' . $index++;
            echo HTMLHelper::_('bootstrap.addSlide', 'collapseTypes', Text::_($text), $slideId);
            echo $this->loadTemplate($template);
            echo HTMLHelper::_('bootstrap.endSlide');
            ?>
        <?php endforeach; ?>
        <?php echo HTMLHelper::_('bootstrap.endAccordion'); ?>
    <?php endif; ?>
</div>
