<?php
/**
 * Native Joomla 5/6 Google color chooser field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Form\FormField;

final class GooglecolorchooserField extends FormField
{
    protected $type = 'GoogleColorChooser';

    private const COLORS = [
        'A32929', 'B1365F', '7A367A', '5229A3', '29527A', '2952A3', '1B887A',
        '28754E', '0D7813', '528800', '88880E', 'AB8B00', 'BE6D00', 'B1440E',
        '865A5A', '705770', '4E5D6C', '5A6986', '4A716C', '6E6E41', '8D6F47',
    ];

    /**
     * Render a readonly hex-color input with an accessible swatch palette.
     *
     * Rejects nested legacy values, validates six-digit hex colors and escapes HTML identifiers
     * and registers the module-independent click handler once per request.
     *
     * @return string Escaped color input and fixed palette-button HTML.
     */
    public function getInput(): string
    {
        // Corrupted or nested legacy settings must not produce PHP 8 warnings.
        $value = is_string($this->value) ? trim($this->value) : '';

        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
            $value = '';
        }

        $html = '<input type="text" name="' . htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8')
            . '" id="' . htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8')
            . '" readonly class="form-control" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            . '" style="background-color:' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '">';
        $html .= '<div class="d-flex flex-wrap gap-1 mt-2" role="group">';

        foreach (self::COLORS as $color) {
            $hex = '#' . $color;
            $html .= '<button type="button" class="btn p-0 border jsm-google-color" data-color="' . $hex
                . '" data-target="' . htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8')
                . '" aria-label="' . $hex . '" title="' . $hex
                . '" style="width:2rem;height:2rem;background-color:' . $hex . '"></button>';
        }

        $html .= '</div>';
        $this->registerScript();

        return $html;
    }

    /**
     * Register the color-swatch click handler with Joomla's asset manager.
     *
     * Registers JavaScript only for an HTML document and avoids duplicate
     * inline listeners when multiple color fields share one page.
     *
     * @return void
     */
    private function registerScript(): void
    {
        static $registered = false;

        if ($registered) {
            return;
        }

        $document = SportsManagementAdministratorApplicationResolver::resolve()->getDocument();

        if (!$document instanceof HtmlDocument) {
            return;
        }

        $document->getWebAssetManager()->addInlineScript(<<<'JS'
document.addEventListener('click', (event) => {
    const button = event.target.closest('.jsm-google-color');
    if (!button) return;
    const input = document.getElementById(button.dataset.target || '');
    if (!input) return;
    const color = button.dataset.color || '';
    input.value = color;
    input.style.backgroundColor = color;
    input.dispatchEvent(new Event('change', {bubbles: true}));
});
JS);
        $registered = true;
    }
}
