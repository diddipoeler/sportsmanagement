<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Staff;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ExtendedFormHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;
use Diddipoeler\Component\SportsManagement\Site\Model\PersonModel;
use Diddipoeler\Component\SportsManagement\Site\Model\StaffModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public ?object $person = null;
    public bool $showediticon = false;
    public $inprojectinfo = null;
    public array $history = [];
    public array $stats = [];
    public array $staffstats = [];
    public array $historystats = [];
    public string $title = '';
    public $extended = null;

    protected function prepareView(): void
    {
        /** @var StaffModel $model */
        $model = $this->getModel();
        if (!$model instanceof StaffModel) {
            throw new \RuntimeException('Staff view requires StaffModel.', 500);
        }

        $this->person = PersonModel::getPerson(0, $model::$cfg_which_database);
        $this->showediticon = PersonModel::getAllowed($this->config['edit_own_player'] ?? 0);
        $this->inprojectinfo = $model->getTeamStaff();
        $this->history = $model->getStaffHistory('DESC');
        $this->stats = $model->getStats();
        $this->staffstats = $model->getStaffStats();
        $this->historystats = $model->getHistoryStaffStats();

        if ($this->person) {
            $name = PersonNameFormatter::format(
                null,
                (string) ($this->person->firstname ?? ''),
                (string) ($this->person->nickname ?? ''),
                (string) ($this->person->lastname ?? ''),
                (string) ($this->config['name_format'] ?? '')
            );
            $this->title = Text::sprintf('COM_SPORTSMANAGEMENT_STAFF_ABOUT_AS_A_STAFF', $name);
            $this->extended = ExtendedFormHelper::load((string) ($this->person->extended ?? ''), 'staff');
        } else {
            $this->title = Text::_('COM_SPORTSMANAGEMENT_STAFF_ABOUT_AS_A_STAFF');
        }

        $this->headertitle = $this->title;
        $document = $this->getDocument();
        $document->setTitle($this->title);
        $document->getWebAssetManager()->registerAndUseStyle(
            'com_sportsmanagement.staff',
            Uri::root(true) . '/components/com_sportsmanagement/assets/css/staff.css'
        );
    }
}
