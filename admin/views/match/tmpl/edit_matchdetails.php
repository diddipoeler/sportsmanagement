<?php
/**
 * SportsManagement match details editor for Joomla 5/6.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$playgroundPictures = [];

foreach ($this->playgrounds as $playground) {
    $value = isset($playground->value) ? (string) $playground->value : '';
    $picture = isset($playground->playgroundpicture) ? trim((string) $playground->playgroundpicture) : '';

    if ($value !== '' && $picture !== '') {
        $playgroundPictures[$value] = $picture;
    }
}

$jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
$pictureMap = json_encode($playgroundPictures, $jsonFlags) ?: '{}';
$rootUrl = json_encode(Uri::root(), $jsonFlags) ?: '""';

$this->document->addStyleDeclaration(
    '.sportsmanagement-playground-preview {'
    . 'display: block; max-height: 80px; max-width: 160px; margin-top: .5rem; object-fit: contain;'
    . '}'
);
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('playground_id');
    const preview = document.getElementById('playground-picture-preview');
    const pictures = <?php echo $pictureMap; ?>;
    const rootUrl = <?php echo $rootUrl; ?>;

    if (!select || !preview) {
        return;
    }

    const updatePreview = function () {
        const picture = pictures[select.value] || '';

        if (!picture) {
            preview.hidden = true;
            preview.removeAttribute('src');
            return;
        }

        preview.src = /^(?:https?:)?\/\//i.test(picture) || picture.startsWith('/')
            ? picture
            : rootUrl + picture.replace(/^\/+/, '');
        preview.hidden = false;
    };

    select.addEventListener('change', updatePreview);
    updatePreview();
});
</script>

<fieldset class="adminform">
    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_MD'); ?></legend>

    <?php echo $this->form->renderField('cancel'); ?>
    <?php echo $this->form->renderField('cancel_reason'); ?>

    <div class="control-group">
        <div class="control-label">
            <label for="playground_id"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_MD_VENUE'); ?></label>
        </div>
        <div class="controls">
            <?php
            echo HTMLHelper::_(
                'select.genericlist',
                $this->playgrounds,
                'playground_id',
                'class="form-select" size="6"',
                'value',
                'text',
                (int) $this->match->playground_id
            );
            ?>
            <img id="playground-picture-preview"
                 class="sportsmanagement-playground-preview"
                 src=""
                 alt=""
                 hidden>
        </div>
    </div>

    <?php echo $this->form->renderField('overtime'); ?>
</fieldset>

<fieldset class="adminform">
    <legend><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD'); ?></legend>

    <?php echo $this->form->renderField('count_result'); ?>
    <?php echo $this->form->renderField('alt_decision'); ?>
    <?php echo $this->form->renderField('decision_info'); ?>
    <?php echo $this->form->renderField('team1_result_decision'); ?>
    <?php echo $this->form->renderField('team2_result_decision'); ?>
    <?php echo $this->form->renderField('team_won'); ?>
</fieldset>
