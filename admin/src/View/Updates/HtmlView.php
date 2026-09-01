<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Updates;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Diddipoeler\Component\SportsManagement\Administrator\Model\UpdatesModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

/** Native Joomla 5/6 administrator view for local update scripts and history. */
final class HtmlView extends BaseHtmlView
{
    public array $versions = [];
    public array $versionhistory = [];
    public array $updateFiles = [];
    public string $request_url = '';
    public string $sortColumn = 'dates';
    public string $sortDirection = '';

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $model = $this->getModel();

        if (!$model instanceof UpdatesModel) {
            throw new \RuntimeException('Updates model could not be loaded.', 500);
        }

        $app->setUserState('com_sportsmanagementupdate_part', 0);
        $this->sortColumn = (string) $app->getUserStateFromRequest(
            'com_sportsmanagementupdates_filter_order',
            'filter_order',
            'dates',
            'cmd'
        );
        $this->sortDirection = (string) $app->getUserStateFromRequest(
            'com_sportsmanagementupdates_filter_order_Dir',
            'filter_order_Dir',
            '',
            'word'
        );

        $this->versions = $model->getVersions();
        $this->versionhistory = $model->getVersionHistory();
        $this->updateFiles = $model->loadUpdateFiles();
        $this->request_url = Uri::getInstance()->toString();

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_TITLE'), 'refresh');

        parent::display($tpl);
    }
}
