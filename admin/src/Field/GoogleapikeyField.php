<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Field\TextField;
use SimpleXMLElement;

final class GoogleapikeyField extends TextField
{
    protected $type = 'GoogleApiKey';

    public function setup(SimpleXMLElement $element, $value, $group = null): bool
    {
        if (($value === null || $value === '') && !isset($element['default'])) {
            $value = (string) ComponentHelper::getParams('com_sportsmanagement')
                ->get('google_api_developerkey', '');
        }

        return parent::setup($element, $value, $group);
    }
}
