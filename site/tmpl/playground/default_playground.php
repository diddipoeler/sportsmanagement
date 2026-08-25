<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage playground
 * @file       default_playground.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\SportsManagementDateHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="playground_default">
    <table class="table">
        <tr>
            <th colspan="2"><?php echo Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_DATA'); ?></th>
        </tr>

        <?php if (!empty($this->config['show_shortname'])) : ?>
            <tr>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_SHORT'); ?></th>
                <td><?php echo $escape($this->playground->short_name ?? ''); ?></td>
            </tr>
        <?php endif; ?>

        <?php if ($this->address_string !== '') : ?>
            <tr>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_ADDRESS'); ?></th>
                <td><?php echo $escape($this->address_string); ?></td>
            </tr>
        <?php endif; ?>

        <?php if (!empty($this->playground->website)) : ?>
            <tr>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_WEBSITE'); ?></th>
                <td>
                    <?php
                    echo HTMLHelper::link(
                        (string) $this->playground->website,
                        $escape($this->playground->website),
                        ['target' => '_blank', 'rel' => 'noopener noreferrer']
                    );
                    ?>
                </td>
            </tr>
        <?php endif; ?>

        <?php if (!empty($this->playground->max_visitors)) : ?>
            <tr>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_MAX_VISITORS'); ?></th>
                <td><?php echo (int) $this->playground->max_visitors; ?></td>
            </tr>
        <?php endif; ?>
    </table>
</div>
<br>

<?php
$this->notes = [Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_HISTORY_NOTIC')];
echo $this->loadTemplate('jsm_notes');
?>

<?php foreach ($this->playgroundnotic as $value) : ?>
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
            <?php echo $escape(SportsManagementDateHelper::convertDate((string) ($value->date_von ?? ''), 1)); ?>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
            <?php echo $escape(SportsManagementDateHelper::convertDate((string) ($value->date_bis ?? ''), 1)); ?>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
            <?php echo $escape($value->name_visitors ?? ''); ?>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
            <?php echo $escape($value->notes ?? ''); ?>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2">
            <?php echo (int) ($value->max_visitors ?? 0); ?>
        </div>
    </div>
<?php endforeach; ?>
