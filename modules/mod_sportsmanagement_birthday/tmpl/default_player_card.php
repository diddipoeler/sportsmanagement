<?php
/**
 * Joomla 5/6 player card layout for the SportsManagement birthday module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

$useFontAwesome = (bool) ComponentHelper::getParams('com_sportsmanagement')->get('use_fontawesome', 0);
?>
<div class="row g-3">
<?php foreach ($persons as $person) : ?>
    <div class="col-12 col-sm-6 col-lg-4">
        <article class="card h-100">
            <?php if (!empty($person['picture_url'])) : ?>
                <img class="card-img-top" src="<?php echo htmlspecialchars($person['picture_url'], ENT_QUOTES, 'UTF-8'); ?>"
                     alt="<?php echo htmlspecialchars($person['display_name'], ENT_QUOTES, 'UTF-8'); ?>">
            <?php endif; ?>
            <div class="card-body">
                <h5 class="card-title">
                    <?php echo $person['flag_html']; ?>
                    <?php echo htmlspecialchars($person['display_name'], ENT_QUOTES, 'UTF-8'); ?>
                </h5>
                <?php if (!empty($person['position_name']) || !empty($person['team_name'])) : ?>
                    <p class="card-text small text-body-secondary">
                        <?php echo htmlspecialchars(Text::_((string) ($person['position_name'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>
                        <?php if (!empty($person['position_name']) && !empty($person['team_name'])) : ?><br><?php endif; ?>
                        <?php echo htmlspecialchars((string) ($person['team_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                <?php endif; ?>
                <div class="card-text"><?php echo $person['birthday_text']; ?></div>
            </div>
            <div class="card-footer bg-transparent">
                <a href="<?php echo htmlspecialchars($person['person_link'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-outline-primary">
                    <?php if ($useFontAwesome) : ?><span class="fa fa-info-circle" aria-hidden="true"></span><?php endif; ?>
                    <?php echo Text::_('MOD_SPORTSMANAGEMENT_BIRTHDAY_PLAYER_CARD_INFO_BTN'); ?>
                </a>
            </div>
        </article>
    </div>
<?php endforeach; ?>
</div>
