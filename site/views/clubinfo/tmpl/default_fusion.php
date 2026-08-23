<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_fusion.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$this->notes = [Text::_('Fusionen')];
echo $this->loadTemplate('jsm_notes');
?>
<div class="<?php echo $this->divclassrow; ?>" id="default_fusion">
    <div class="panel-group" id="club-fusion-<?php echo (int) $this->club->id; ?>">
        <div class="panel panel-default">
            <div id="club-fusion-panel-<?php echo (int) $this->club->id; ?>">
                <div class="panel-body">
                    <div class="tree" style="display: flow-root;">
                        <ul>
                            <li>
                                <?php if (empty($this->config['show_bootstrap_tree'])) : ?>
                                    <span><i class="icon-folder-open"></i> aktueller Verein</span>
                                <?php endif; ?>

                                <a href="#">
                                    <?php
                                    if (!empty($this->club->logo_big)) {
                                        echo HTMLHelper::image(
                                            (string) $this->club->logo_big,
                                            (string) $this->club->name,
                                            ['width' => '30']
                                        ) . ' ';
                                    }
                                    echo $escape($this->club->name ?? '');
                                    ?>
                                </a>

                                <?php echo $this->familytree; ?>
                            </li>
                        </ul>

                        <?php if (!empty($this->club->new_club_id)) : ?>
                            <?php echo $this->clubhistoryhtml; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
