<?php
/**
 * Native Joomla 5/6 administrator SIS import view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextsisimport;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
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
        $app = self::administratorApplication();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement');

        $this->project = $app->getUserState($option . 'project');
        $this->revisionDate = '2011-04-28 - 12:00';
        $this->import_version = 'NEW';
    }

    public function _displayDefaultUpdate(): void
    {
        $app = self::administratorApplication();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement');
        $model = $this->getModel();

        if (!is_object($model) || !method_exists($model, 'getUpdateData')) {
            throw new \RuntimeException('SIS import update data is unavailable.', 500);
        }

        $this->project = $app->getUserState($option . 'project');
        $this->uploadArray = (array) $app->getUserState($option . 'uploadArray', []);
        $this->importData = $model->getUpdateData();
    }
    private static function administratorApplication(): AdministratorApplication
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement SIS import view requires the Joomla administrator application.', 500);
        }

        return $app;
    }

}
