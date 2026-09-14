<?php
/**
 * Native Joomla 5/6 administrator fieldsets edit-data layout.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\CountryOptionsHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;

$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$app = $this->app;
$input = $this->jinput;
$view = $input->getCmd('view', (string) ($this->view ?? 'cpanel'));
$document = $this->document;
$modalHeight = (int) ($this->modalheight ?? (\defined('COM_SPORTSMANAGEMENT_MODAL_POPUP_HEIGHT') ? COM_SPORTSMANAGEMENT_MODAL_POPUP_HEIGHT : 600));
$modalWidth = (int) ($this->modalwidth ?? (\defined('COM_SPORTSMANAGEMENT_MODAL_POPUP_WIDTH') ? COM_SPORTSMANAGEMENT_MODAL_POPUP_WIDTH : 900));
$helpBase = \defined('COM_SPORTSMANAGEMENT_HELP_SERVER') ? (string) COM_SPORTSMANAGEMENT_HELP_SERVER : '';
$database = null;

$document->getWebAssetManager()->useScript('showon');

foreach (['footer', 'fieldsets'] as $templateName) {
    $this->addTemplatePath(JPATH_COMPONENT . '/views/' . $templateName . '/tmpl');
    $this->addTemplatePath(
        JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_sportsmanagement/' . $templateName
    );
}

if (in_array($view, ['club', 'playground', 'player'], true)) {
    $leafletVersion = preg_replace('/[^0-9A-Za-z.\-_]/', '', (string) ($this->leaflet_version ?? '1.9.4'));
    $leafletCssIntegrity = $escape($this->leaflet_css_integrity ?? '');
    $leafletJsIntegrity = $escape($this->leaflet_js_integrity ?? '');
    ?>
    <link
        rel="stylesheet"
        href="<?php echo $escape('https://unpkg.com/leaflet@' . $leafletVersion . '/dist/leaflet.css'); ?>"
        integrity="<?php echo $leafletCssIntegrity; ?>"
        crossorigin=""
    >
    <script
        src="<?php echo $escape('https://unpkg.com/leaflet@' . $leafletVersion . '/dist/leaflet.js'); ?>"
        integrity="<?php echo $leafletJsIntegrity; ?>"
        crossorigin=""
    ></script>
    <?php
    $document->addScript('https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js');
}

try {
    $fieldsets = $this->form->getFieldsets();
} catch (\Throwable $throwable) {
    $fieldsets = [];
    $app->enqueueMessage(
        Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $throwable->getCode(), $throwable->getMessage()),
        'notice'
    );
}

$leftColumns = in_array($view, ['club', 'playground', 'player', 'events', 'statistics'], true) ? 6 : 12;
$rightColumns = $leftColumns === 6 ? 6 : 0;

$renderHelpButton = static function (string $helpUrl, string $modalId) use ($escape, $modalHeight, $modalWidth): string {
    if ($helpUrl === '') {
        return '';
    }

    $helpTitle = Text::_('COM_SPORTSMANAGEMENT_HELP_LINK');
    $button = '<button type="button" class="btn btn-link p-0 align-baseline"'
        . ' data-bs-toggle="modal" data-bs-target="#' . $escape($modalId) . '"'
        . ' title="' . $escape($helpTitle) . '" aria-label="' . $escape($helpTitle) . '">'
        . HTMLHelper::_('image', 'media/com_sportsmanagement/jl_images/help.png', $helpTitle, ['title' => $helpTitle])
        . '</button>';

    $modal = HTMLHelper::_(
        'bootstrap.renderModal',
        $modalId,
        [
            'title' => $helpTitle,
            'url' => $helpUrl,
            'height' => $modalHeight,
            'width' => $modalWidth,
            'bodyHeight' => 70,
            'modalWidth' => 80,
        ]
    );

    return $button . $modal;
};

$getPlaygroundPicture = static function (int $playgroundId) use (&$database): string {
    if ($playgroundId <= 0) {
        return '';
    }

    $database ??= (new SportsManagementDatabaseResolver())->resolve();
    $query = $database->getQuery(true)
        ->select($database->quoteName('picture'))
        ->from($database->quoteName('#__sportsmanagement_playground'))
        ->where($database->quoteName('id') . ' = ' . $playgroundId);
    $database->setQuery($query);
    $picture = trim((string) $database->loadResult());

    if ($picture === '' || !is_file(JPATH_SITE . '/' . ltrim($picture, '/'))) {
        $picture = trim((string) ComponentHelper::getParams('com_sportsmanagement')->get('ph_team', ''));
    }

    return $picture;
};

$renderPreview = static function (mixed $value) use ($escape): string {
    $url = trim((string) $value);

    if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
        return '';
    }

    $previewUrl = 'https://www.thumbshots.de/cgi-bin/show.cgi?url=' . rawurlencode($url);

    return '<img src="' . $escape($previewUrl) . '" alt="' . $escape($url) . '" loading="lazy">';
};
?>
<div>
    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details']); ?>

    <?php foreach ($fieldsets as $fieldset) : ?>
        <?php
        $fieldsetName = (string) $fieldset->name;
        echo HTMLHelper::_('uitab.addTab', 'myTab', $fieldsetName, Text::_($fieldset->label, true));

        if ($helpBase !== '') {
            $fieldsetHelpUrl = $helpBase . 'SM-Backend-Felder:' . $view . '-' . $fieldsetName;
            echo $renderHelpButton($fieldsetHelpUrl, 'jsm-fieldset-help-' . substr(sha1($fieldsetHelpUrl), 0, 12));
        }

        if ($app->get('debug')) {
            Log::add(
                __METHOD__ . ' ' . __LINE__ . ' fieldset name -> ' . $fieldsetName,
                Log::NOTICE,
                'jsmerror'
            );
        }

        switch ($fieldsetName) {
            case 'details':
            case 'description':
            case 'seasons':
            case 'seasonsteams':
            case 'COM_SPORTSMANAGEMENT_FES_PARAMS_GROUP_OPTIONS':
            case 'COM_SPORTSMANAGEMENT_FES_PARAMS_GROUP_DIV_OPTIONS':
            case 'COM_SPORTSMANAGEMENT_FES_PARAMS_GROUP_TEAMOPTIONS':
            case 'COM_SPORTSMANAGEMENT_FES_PARAMS_GROUP_ADVANCED_OPTIONS':
                ?>
                <div class="row">
                    <div class="col-lg-<?php echo (int) $leftColumns; ?>">
                        <?php foreach ($this->form->getFieldset($fieldsetName) as $field) : ?>
                            <div class="control-group">
                                <div class="control-label">
                                    <?php echo $field->label; ?>
                                </div>
                                <div class="controls">
                                    <?php echo $field->input; ?>
                                    <?php
                                    $fieldKey = str_replace(
                                        ['jform[', ']', 'request[', 'params['],
                                        ['', '', '', ''],
                                        (string) $field->name
                                    );

                                    if (
                                        $fieldKey !== 'ids'
                                        && !in_array((string) $field->type, ['extensionsubtitle', 'Hidden'], true)
                                        && $helpBase !== ''
                                    ) {
                                        $fieldHelpUrl = $helpBase
                                            . 'SM-Backend-Felder:'
                                            . $view
                                            . '-'
                                            . $this->form->getName()
                                            . '-'
                                            . $fieldKey;
                                        echo $renderHelpButton(
                                            $fieldHelpUrl,
                                            'jsm-field-help-' . substr(sha1($fieldHelpUrl), 0, 12)
                                        );
                                    }

                                    if ($field->name === 'jform[country]') {
                                        $database ??= (new SportsManagementDatabaseResolver())->resolve();
                                        echo CountryOptionsHelper::getFlag($database, (string) $field->value);
                                    }

                                    if ($field->name === 'jform[standard_playground]') {
                                        $picture = $getPlaygroundPicture((int) $field->value);

                                        if ($picture !== '') {
                                            $pictureUrl = Uri::root() . ltrim($picture, '/');
                                            ?>
                                            <a
                                                href="<?php echo $escape($pictureUrl); ?>"
                                                title="Playground"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                <img
                                                    src="<?php echo $escape($pictureUrl); ?>"
                                                    alt="Playground"
                                                    width="50"
                                                    loading="lazy"
                                                >
                                            </a>
                                            <?php
                                        }
                                    }

                                    if (in_array($field->name, ['jform[website]', 'jform[twitter]', 'jform[facebook]'], true)) {
                                        echo $renderPreview($field->value);
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (in_array($view, ['club', 'playground', 'player'], true)) : ?>
                        <?php
                        $latitude = is_numeric($this->item->latitude ?? null) ? (float) $this->item->latitude : 0.0;
                        $longitude = is_numeric($this->item->longitude ?? null) ? (float) $this->item->longitude : 0.0;
                        ?>
                        <div class="col-lg-<?php echo (int) $rightColumns; ?>">
                            <div class="control-group">
                                <div id="map" style="width: 100%; height: 400px; margin-top: 50px;"></div>
                                <script>
                                    (() => {
                                        const latitude = <?php echo json_encode($latitude, JSON_THROW_ON_ERROR); ?>;
                                        const longitude = <?php echo json_encode($longitude, JSON_THROW_ON_ERROR); ?>;
                                        const map = L.map('map').setView([latitude, longitude], 15);

                                        L.tileLayer('https://{s}.google.com/vt/lyrs=s,h&x={x}&y={y}&z={z}', {
                                            attribution: '&copy; <a href="https://www.openstreetmap.org">OpenStreetMap</a> Contributors',
                                            maxZoom: 20,
                                            subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                                        }).addTo(map);

                                        const markerLayer = L.layerGroup().addTo(map);
                                        markerLayer.addLayer(L.marker([latitude, longitude]));
                                        L.control.layers(null, {'markers': markerLayer}).addTo(map);
                                    })();
                                </script>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php
                break;

            case 'events':
                echo $this->loadTemplate('position_events');
                break;

            case 'statistics':
                echo $this->loadTemplate('position_statistics');
                break;

            default:
                $this->fieldset = $fieldsetName;
                echo $this->loadTemplate('fieldsets_4');
                break;
        }

        echo HTMLHelper::_('uitab.endTab');
        ?>
    <?php endforeach; ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
</div>

<div class="clr"></div>
<div>
    <input type="hidden" name="task" value="<?php echo $escape($view); ?>.edit">

    <?php if ($view === 'teamplayer') : ?>
        <input type="hidden" name="persontype" value="<?php echo $escape($this->persontype ?? ''); ?>">
        <input type="hidden" name="project_id" value="<?php echo (int) ($this->project_id ?? 0); ?>">
        <input type="hidden" name="pid" value="<?php echo (int) ($this->project_id ?? 0); ?>">
        <input type="hidden" name="team_id" value="<?php echo (int) ($this->team_id ?? 0); ?>">
        <input type="hidden" name="project_team_id" value="<?php echo (int) ($this->project_team_id ?? 0); ?>">
        <input type="hidden" name="season_id" value="<?php echo (int) ($this->season_id ?? 0); ?>">
        <input type="hidden" name="project_art_id" value="<?php echo (int) ($this->project_art_id ?? 0); ?>">
        <input type="hidden" name="sports_type_id" value="<?php echo (int) ($this->sports_type_id ?? 0); ?>">
        <input type="hidden" name="season_team_id" value="<?php echo (int) ($this->season_team_id ?? 0); ?>">
    <?php endif; ?>

    <?php if ($view === 'treetonode') : ?>
        <input type="hidden" name="project_id" value="<?php echo (int) ($this->projectws->id ?? 0); ?>">
        <input type="hidden" name="pid" value="<?php echo (int) ($this->projectws->id ?? 0); ?>">
        <input type="hidden" name="tid" value="<?php echo (int) ($this->item->treeto_id ?? 0); ?>">
    <?php endif; ?>

    <?php echo HTMLHelper::_('form.token'); ?>
</div>
</form>

<?php echo $this->loadTemplate('footer'); ?>
