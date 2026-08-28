<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextdbbimport;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/** Native Joomla 5/6 DBB import administrator view. */
final class HtmlView extends BaseHtmlView
{
    public $project = null;
    public string $request_url = '';
    public ?Registry $config = null;
    public string $revisionDate = '2011-04-28 - 12:00';
    public string $import_version = 'NEW';
    public array $uploadArray = [];
    public $importData = null;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->addTemplatePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/footer/tmpl');
        $this->addTemplatePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/listheader/tmpl');
    }

    public function display($tpl = null)
    {
        $layout = $this->getLayout();

        if (in_array($layout, ['default_3', 'default_4', 'default_5'], true)) {
            $this->setLayout('default');
            $layout = 'default';
        } elseif (in_array($layout, ['default_update_3', 'default_update_4', 'default_update_5'], true)) {
            $this->setLayout('default_update');
            $layout = 'default_update';
        }

        if ($layout === 'default_update') {
            $this->_displayDefaultUpdate();
        } else {
            $this->init();
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    public function init(): void
    {
        $app = Factory::getApplication();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement');

        $this->project = $app->getUserState($option . 'project');
        $this->request_url = Uri::getInstance()->toString();
        $this->config = ComponentHelper::getParams('com_media');
        $this->revisionDate = '2011-04-28 - 12:00';
        $this->import_version = 'NEW';
    }

    public function _displayDefault(): void
    {
        $this->init();
    }

    public function _displayDefaultUpdate(): void
    {
        $app = Factory::getApplication();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement');
        $model = $this->getModel();

        if (!is_object($model) || !method_exists($model, 'getUpdateData')) {
            throw new \RuntimeException('DBB import update data is unavailable.', 500);
        }

        $this->project = $app->getUserState($option . 'project');
        $this->request_url = Uri::getInstance()->toString();
        $this->config = ComponentHelper::getParams('com_media');
        $this->uploadArray = (array) $app->getUserState($option . 'uploadArray', []);
        $this->importData = $model->getUpdateData();
    }

    protected function addToolbar(): void
    {
        $this->getDocument()->getWebAssetManager()->registerAndUseStyle(
            'com_sportsmanagement.jlextdbbimport',
            Uri::root(true) . '/administrator/components/com_sportsmanagement/assets/css/jlextusericons.css',
            ['version' => 'auto']
        );

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT'), 'dbb-cpanel');
    }
}
