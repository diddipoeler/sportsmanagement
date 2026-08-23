<?php
/**
 * SportsManagement professional league import view.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

class sportsmanagementViewjlextprofleagimport extends sportsmanagementView
{
    public function init()
    {
        $language = $this->app->getLanguage();
        $this->config = ComponentHelper::getParams('com_media');

        $languageParts = explode('-', $language->getTag());
        $country = JSMCountries::convertIso2to3((string) ($languageParts[1] ?? 'DE'));
        $this->country = $country;
        $countries = JSMCountries::getCountryOptions();
        $this->countries = HTMLHelper::_(
            'select.genericlist',
            $countries,
            'country',
            'class="inputbox" size="1"',
            'value',
            'text',
            $country
        );
    }

    protected function addToolbar()
    {
        $stylelink = '<link rel="stylesheet" href="'
            . Uri::root()
            . 'administrator/components/'
            . $this->option
            . '/assets/css/jlextusericons.css" type="text/css" />'
            . "\n";
        $this->document->addCustomTag($stylelink);

        ToolbarHelper::title(
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_TITLE_1'),
            'profleage-cpanel'
        );
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=extensions');
        ToolbarHelper::divider();
        parent::addToolbar();
    }
}
