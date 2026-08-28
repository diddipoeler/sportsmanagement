<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextlmoimports;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\CountryOptionsHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/** Native Joomla 5/6 LMO import administrator view. */
final class HtmlView extends BaseHtmlView
{
    public ?Registry $config = null;
    public string $country = '';
    public string $countries = '';
    public string $countryFlag = '';
    public string $agegroup = '';
    public array $templates = [];
    public string $request_url = '';

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->addTemplatePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/footer/tmpl');
        $this->addTemplatePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/listheader/tmpl');
    }

    public function display($tpl = null)
    {
        $this->init();
        $this->addToolbar();

        parent::display($tpl);
    }

    public function init(): void
    {
        $app = Factory::getApplication();
        $language = $app->getLanguage();
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $this->config = ComponentHelper::getParams('com_media');
        $this->request_url = Uri::getInstance()->toString();

        $languageParts = explode('-', $language->getTag());
        $this->country = CountryOptionsHelper::iso2To3($db, (string) ($languageParts[1] ?? 'DE'));
        $this->countries = HTMLHelper::_(
            'select.genericlist',
            CountryOptionsHelper::getOptions($db),
            'country',
            'class="form-select" size="1"',
            'value',
            'text',
            $this->country
        );
        $this->countryFlag = CountryOptionsHelper::getFlag($db, $this->country);

        $agegroupOptions = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP')),
        ];

        $mvcFactory = $app->bootComponent('com_sportsmanagement')->getMVCFactory();
        $agegroupsModel = $mvcFactory->createModel('Agegroups', 'Administrator', ['ignore_request' => true]);

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

        $xmlImportModel = $mvcFactory->createModel('Jlxmlimport', 'Administrator', ['ignore_request' => true]);
        $this->templates = $xmlImportModel && method_exists($xmlImportModel, 'getTemplateList')
            ? ($xmlImportModel->getTemplateList() ?: [])
            : [];
    }

    protected function addToolbar(): void
    {
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=extensions');
        ToolbarHelper::divider();

        if (Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_sportsmanagement')) {
            ToolbarHelper::preferences('com_sportsmanagement');
        }
    }
}
