<?php
/**
 * Native Joomla 5/6 administrator professional-league import view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextprofleagimport;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\CountryOptionsHelper;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/** Native Joomla 5/6 professional-league import administrator view. */
final class HtmlView extends BaseHtmlView
{
    public ?Registry $config = null;
    public string $country = '';
    public string $countries = '';
    public string $countryFlag = '';
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
        $app = self::administratorApplication();
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $languageParts = explode('-', $app->getLanguage()->getTag());

        $this->config = ComponentHelper::getParams('com_media');
        $this->request_url = Uri::getInstance()->toString();
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
    }

    protected function addToolbar(): void
    {
        $document = $this->getDocument();

        if ($document instanceof HtmlDocument) {
            $document->getWebAssetManager()->registerAndUseStyle(
                'com_sportsmanagement.admin.user-icons',
                'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css',
                ['version' => 'auto']
            );
        }

        ToolbarHelper::title(
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROF_LEAGUE_IMPORT_TITLE_1'),
            'profleage-cpanel'
        );
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=extensions');
        ToolbarHelper::divider();

        if (self::administratorApplication()->getIdentity()->authorise('core.admin', 'com_sportsmanagement')) {
            ToolbarHelper::preferences('com_sportsmanagement');
        }
    }
    private static function administratorApplication(): AdministratorApplication
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement professional-league import view requires the Joomla administrator application.', 500);
        }

        return $app;
    }

}
