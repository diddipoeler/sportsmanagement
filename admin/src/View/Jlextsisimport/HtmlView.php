<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextsisimport;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/** Native Joomla 5/6 SIS import administrator view. */
final class HtmlView extends BaseHtmlView
{
    public $project = null;
    public string $revisionDate = '2011-04-28 - 12:00';
    public string $import_version = 'NEW';
    public array $uploadArray = [];
    public $importData = null;

    public function display($tpl = null)
    {
        $layout = $this->getLayout();

        if (in_array($layout, ['default_3', 'default_4'], true)) {
            $this->setLayout('default');
            $layout = 'default';
        } elseif (in_array($layout, ['default_update_3', 'default_update_4'], true)) {
            $this->setLayout('default_update');
            $layout = 'default_update';
        }

        if ($layout === 'default_update') {
            $this->_displayDefaultUpdate();
        } else {
            $this->init();
        }

        parent::display($tpl);
    }

    public function init(): void
    {
        if (in_array($this->getLayout(), ['default', 'default_3', 'default_4'], true)) {
            $this->_displayDefault();
            return;
        }

        $this->revisionDate = '2011-04-28 - 12:00';
    }

    public function _displayDefault(): void
    {
        $app = Factory::getApplication();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement');

        $this->project = $app->getUserState($option . 'project');
        $this->revisionDate = '2011-04-28 - 12:00';
        $this->import_version = 'NEW';
    }

    public function _displayDefaultUpdate(): void
    {
        $app = Factory::getApplication();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement');
        $model = $this->getModel();

        if (!is_object($model) || !method_exists($model, 'getUpdateData')) {
            throw new \RuntimeException('SIS import update data is unavailable.', 500);
        }

        $this->project = $app->getUserState($option . 'project');
        $this->uploadArray = (array) $app->getUserState($option . 'uploadArray', []);
        $this->importData = $model->getUpdateData();
    }
}
