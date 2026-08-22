<?php
/** Joomla 5/6 layout for the SportsManagement liveticker module. */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

if ($isAjax) {
    echo $ajaxReturn;
    $app->close();
    return;
}

$wa = $app->getDocument()->getWebAssetManager();
$wa->registerAndUseScript(
    'mod_sportsmanagement_liveticker',
    Uri::root(true) . '/modules/mod_sportsmanagement_liveticker/js/turtushout.js',
    [],
    ['defer' => true]
);

if ($cssFile !== '') {
    $wa->registerAndUseStyle(
        'mod_sportsmanagement_liveticker',
        Uri::root(true) . '/modules/mod_sportsmanagement_liveticker/css/' . $cssFile
    );
}

$moduleClass = htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$moduleId = htmlspecialchars($module->module . '-' . $module->id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$endpointValue = htmlspecialchars($endpoint, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<div class="<?php echo $moduleClass; ?> js-sportsmanagement-liveticker"
     id="<?php echo $moduleId; ?>"
     data-endpoint="<?php echo $endpointValue; ?>"
     data-update-timeout="<?php echo (int) $updateTimeout * 1000; ?>">
    <div class="turtushout-warning">
        <?php echo Text::_('!Warning! JavaScript must be enabled for proper operation.'); ?>
    </div>

    <?php if ($displayAddBox) : ?>
        <form class="turtushout-form" hidden>
            <?php if ($userId && $displayWelcome) : ?>
                Hi, you logged in as <?php echo htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?><br>
            <?php elseif ($displayUsername) : ?>
                <label><?php echo Text::_('Name'); ?></label>
                <input class="inputbox" type="text" name="created_by_alias" size="<?php echo (int) $size; ?>"><br>
            <?php endif; ?>

            <?php if ($displayTitle) : ?>
                <label><?php echo Text::_('Title'); ?></label>
                <input class="inputbox" type="text" name="title" size="<?php echo (int) $size; ?>"><br>
            <?php endif; ?>

            <label><?php echo Text::_('Text'); ?></label>
            <textarea class="inputbox" name="text" rows="<?php echo (int) $rows; ?>" cols="<?php echo (int) $cols; ?>"></textarea>
            <input type="submit" name="Submit" class="button" value="<?php echo Text::_('Submit'); ?>">
        </form>
    <?php endif; ?>

    <div class="turtushout-status" hidden></div>
    <div class="turtushout-shout"><?php echo $listHtml; ?></div>
</div>
