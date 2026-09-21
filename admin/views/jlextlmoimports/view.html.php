<?php
/**
 * Legacy Joomla 5/6 LMO import administrator view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
/**
 * SportsManagement LMO import view compatibility implementation.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class sportsmanagementViewjlextlmoimports extends sportsmanagementView
{
    public function init()
    {
        $app = self::administratorApplication();
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

        $xmlImportModel = $mvcFactory->createModel(
            'Jlxmlimport',
            'Administrator',
            ['ignore_request' => true]
        );
        $this->templates = $xmlImportModel && method_exists($xmlImportModel, 'getTemplateList')
            ? $xmlImportModel->getTemplateList()
            : [];
    }

    protected function addToolbar()
    {
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=extensions');
        ToolbarHelper::divider();
        parent::addToolbar();
    }
    private static function administratorApplication(): AdministratorApplication
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement LMO import view requires the Joomla administrator application.', 500);
        }

        return $app;
    }

}
