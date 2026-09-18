<?php
/**
 * Native Joomla 5/6 match statistics popup.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright (C) 2013-2026 Fussball in Europa
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$input = $this->app->getInput();
$close = $input->getInt('close', 0);
$refresh = $input->getBool('refresh', false);

$assets = $this->document->getWebAssetManager();
$assets->registerAndUseScript(
    'com_sportsmanagement.admin.matchstats',
    'administrator/components/com_sportsmanagement/assets/js/matchstats.js',
    ['version' => 'auto'],
    ['defer' => true],
    ['core']
);
?>
<form
    action="<?php echo Route::_('index.php?option=com_sportsmanagement'); ?>"
    id="adminform"
    name="adminform"
    method="post"
    data-jsm-match-stats-form
    data-jsm-close="<?php echo $close === 1 ? '1' : '0'; ?>"
    data-jsm-refresh="<?php echo $refresh ? '1' : '0'; ?>"
>
    <div class="d-flex justify-content-end gap-2 mb-3">
        <button type="button" class="btn btn-primary" data-match-stats-task="matches.savestats">
            <?php echo Text::_('JAPPLY'); ?>
        </button>
        <button type="button" class="btn btn-success" data-match-stats-task="matches.savestats" data-close-after-save="1">
            <?php echo Text::_('JSAVE'); ?>
        </button>
        <button type="button" class="btn btn-secondary" data-match-stats-cancel>
            <?php echo Text::_('JCANCEL'); ?>
        </button>
    </div>

    <?php
    $tabsOptions = ['active' => 'panel1', 'recall' => true];

    echo HTMLHelper::_('uitab.startTabSet', 'match-stats-tabs', $tabsOptions);
    echo HTMLHelper::_('uitab.addTab', 'match-stats-tabs', 'panel1', Text::_((string) $this->teams->team1));
    echo $this->loadTemplate('home');
    echo HTMLHelper::_('uitab.endTab');
    echo HTMLHelper::_('uitab.addTab', 'match-stats-tabs', 'panel2', Text::_((string) $this->teams->team2));
    echo $this->loadTemplate('away');
    echo HTMLHelper::_('uitab.endTab');
    echo HTMLHelper::_('uitab.endTabSet');
    ?>

    <input type="hidden" name="view" value="">
    <input type="hidden" name="close" id="close" value="0">
    <input type="hidden" name="task" value="">
    <input type="hidden" name="project_id" value="<?php echo (int) $this->project_id; ?>">
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>">
    <input type="hidden" name="match_id" value="<?php echo (int) $this->item->id; ?>">
    <input type="hidden" name="boxchecked" value="0">
    <input type="hidden" name="component" value="com_sportsmanagement">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
