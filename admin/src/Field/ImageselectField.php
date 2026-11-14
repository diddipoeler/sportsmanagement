<?php
/**
 * Joomla 5/6 image selector backed by the shared SportsManagement helper.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ImageSelectHelper;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Uri\Uri;

/**
 * Administrator form field implementing the Joomla 5/6 image-select control.
 *
 * Delegates rendering to the shared helper, which selects the currently active
 * administrator application so image dialogs and scripts use the backend document.
 */
final class ImageselectField extends FormField
{
    protected $type = 'imageselect';

    /**
     * Render the backend image selector with preview and reset state fields.
     *
     * Loads the namespaced helper when the legacy bridge has not loaded it
     * yet, builds upload and image-browser controls for this field's target
     * folder, and appends the preview and original-value inputs used by Joomla
     * league and other administrator forms.
     *
     * @return string Rendered input, preview and modal markup.
     */
    protected function getInput(): string
    {
        if (!class_exists(ImageSelectHelper::class)) {
            $helperFile = JPATH_SITE . '/components/com_sportsmanagement/src/Helper/ImageSelectHelper.php';

            if (is_file($helperFile)) {
                require_once $helperFile;
            }
        }

        if (!class_exists(ImageSelectHelper::class)) {
            return '';
        }

        $default = (string) $this->value;
        $targetFolder = (string) ($this->element['targetfolder'] ?? '');
        $output = ImageSelectHelper::getSelector(
            $this->name,
            $this->name . '_preview',
            $targetFolder,
            $this->value,
            $default,
            $this->name,
            $this->id
        );

        $output .= '<img class="imagepreview" src="'
            . htmlspecialchars(Uri::root(true) . '/media/com_sportsmanagement/jl_images/spinner.gif', ENT_QUOTES, 'UTF-8')
            . '" name="' . htmlspecialchars($this->name . '_preview', ENT_QUOTES, 'UTF-8')
            . '" id="' . htmlspecialchars($this->id . '_preview', ENT_QUOTES, 'UTF-8')
            . '" alt="Preview" title="Preview" />';
        $output .= '<input type="hidden" id="original_' . htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8')
            . '" value="' . htmlspecialchars((string) $this->value, ENT_QUOTES, 'UTF-8') . '" />';
        $output .= '<input type="hidden" id="copy_' . htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8')
            . '" value="' . htmlspecialchars((string) $this->value, ENT_QUOTES, 'UTF-8') . '" />';

        return $output;
    }
}
