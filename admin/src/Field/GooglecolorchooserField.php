<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

final class GooglecolorchooserField extends FormField
{
    protected $type = 'GoogleColorChooser';

    private const COLORS = [
        'A32929', 'B1365F', '7A367A', '5229A3', '29527A', '2952A3', '1B887A',
        '28754E', '0D7813', '528800', '88880E', 'AB8B00', 'BE6D00', 'B1440E',
        '865A5A', '705770', '4E5D6C', '5A6986', '4A716C', '6E6E41', '8D6F47',
    ];

    public function getInput(): string
    {
        $value = trim((string) $this->value);

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

    private function registerScript(): void
    {
        static $registered = false;

        if ($registered) {
            return;
        }

        $registered = true;
        Factory::getApplication()->getDocument()->getWebAssetManager()->addInlineScript(<<<'JS'
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
    }
}
