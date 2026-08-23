<?php
/**
 * SportsManagement LMO import view compatibility implementation.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewjlextlmoimports extends sportsmanagementView
{
    public function init()
    {
        $app = Factory::getApplication();
        $language = $app->getLanguage();
        $this->config = ComponentHelper::getParams('com_media');

        $languageParts = explode('-', $language->getTag());
        $iso2 = (string) ($languageParts[1] ?? 'DE');
        $country = JSMCountries::convertIso2to3($iso2);
        $this->country = $country;

        $countries = JSMCountries::getCountryOptions();
        $this->countries = HTMLHelper::_(
            'select.genericlist',
            $countries,
            'country',
            'class="form-select" size="1"',
            'value',
            'text',
            $country
        );

        $agegroupOptions = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP')),
        ];

        $mvcFactory = $app->bootComponent('com_sportsmanagement')->getMVCFactory();
        $agegroupsModel = $mvcFactory->createModel(
            'Agegroups',
            'Administrator',
            ['ignore_request' => true]
        );

        if ($agegroupsModel && method_exists($agegroupsModel, 'getAgeGroups')) {
            $agegroups = $agegroupsModel->getAgeGroups('', 0);

            if ($agegroups) {
                $agegroupOptions = array_merge($agegroupOptions, $agegroups);
            }
        }

        $this->agegroup = HTMLHelper::_(
            'select.genericlist',
            $agegroupOptions,
            'agegroup',
            'class="form-select" size="1"',
            'value',
            'text',
            0
        );

        if (!class_exists('sportsmanagementModelJLXMLImport', false)) {
            require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/jlxmlimport.php';
        }

        $xmlImportModel = new sportsmanagementModelJLXMLImport();
        $this->templates = $xmlImportModel->getTemplateList();
    }

    protected function addToolbar()
    {
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=extensions');
        ToolbarHelper::divider();
        parent::addToolbar();
    }
}
