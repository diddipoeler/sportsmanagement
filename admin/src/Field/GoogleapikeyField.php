<?php
/**
 * Native Joomla 5/6 Google API key field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Field\TextField;
use SimpleXMLElement;

final class GoogleapikeyField extends TextField
{
    protected $type = 'GoogleApiKey';

    /**
     * Initialise the Google API key field from saved component settings.
     * Uses the component default only when neither a saved field value nor
     * an explicit XML default is present. Rejects malformed structured values
     * before handing the key to Joomla's native text-field implementation.
     * @param SimpleXMLElement $element Field definition.
     * @param mixed $value Saved field value.
     * @param string|null $group Optional form group.
     * @return bool Whether Joomla accepted field setup.
     */
    public function setup(SimpleXMLElement $element, $value, $group = null): bool
    {
        if (($value === null || $value === '') && !isset($element['default'])) {
            $value = ComponentHelper::getParams('com_sportsmanagement')
                ->get('google_api_developerkey', '');
        }

        if ($value !== null && !is_string($value)) {
            $value = '';
        }

        return parent::setup($element, $value, $group);
    }
}
