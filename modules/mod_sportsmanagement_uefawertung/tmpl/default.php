<?php
/**
 * Joomla 5/6 UEFA ranking layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  (C) 2015-2026
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$escape = static fn(mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$tableClass = $escape($params->get('table_class', 'table'));
$moduleClass = $escape($params->get('moduleclass_sfx', ''));
?>
<div class="<?php echo $moduleClass; ?>">
    <p><?php echo Text::_('MOD_SPORTSMANAGEMENT_UEFAWERTUNG_BERECHNUNG'); ?></p>

    <?php if ($seasons === [] || $rankings === []) : ?>
        <div class="alert alert-info" role="status"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></div>
    <?php else : ?>
        <div class="table-responsive">
            <table class="<?php echo $tableClass; ?>">
                <thead>
                    <tr>
                        <th scope="col"></th>
                        <?php foreach ($seasons as $season) : ?>
                            <th scope="col"><?php echo $escape($season); ?></th>
                        <?php endforeach; ?>
                        <th scope="col">&Sigma;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rankings as $ranking) : ?>
                        <tr>
                            <th scope="row"><?php echo $escape($ranking['country']); ?></th>
                            <?php foreach ($seasons as $season) : ?>
                                <td><?php echo $escape($ranking['points'][$season] ?? 0); ?></td>
                            <?php endforeach; ?>
                            <td><strong><?php echo $escape($ranking['total']); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
