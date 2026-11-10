<?php
/**
 * Native Joomla 5/6 read-only tournament tree layout.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

// Team names and scores are data, never executable JavaScript. HTML escaping
// is needed separately for the heading and the bounded CSS configuration.
$bracketJson = json_encode(
    $this->treePayload,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR
);
$heading = $this->projectName !== ''
    ? $this->projectName
    : Text::_('MOD_SPORTSMANAGEMENT_NAVIGATION_NAVSELECT_JLTOURNAMENTTREE');
?>
<section class="jsm-tournament-tree"
         data-jsm-tournament-tree
         style="--jsm-tree-from: <?= htmlspecialchars($this->colorFrom, ENT_QUOTES, 'UTF-8') ?>;
                --jsm-tree-to: <?= htmlspecialchars($this->colorTo, ENT_QUOTES, 'UTF-8') ?>;
                --jsm-tree-font-size: <?= (int) $this->fontSize ?>px;
                --jsm-tree-round-width: <?= (int) $this->roundWidth ?>px">
    <h2 class="h4"><?= htmlspecialchars($heading, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>

    <?php if ($this->treePayload['teams'] === []) : ?>
        <p class="text-muted"><?= htmlspecialchars(Text::_('JGLOBAL_NO_MATCHING_RESULTS'), ENT_QUOTES, 'UTF-8') ?></p>
    <?php else : ?>
        <div class="jsm-tournament-tree__bracket" data-jsm-tournament-bracket></div>
        <script type="application/json" data-jsm-tournament-data><?= $bracketJson ?></script>
    <?php endif; ?>
</section>
