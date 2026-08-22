<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Github;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

/** Native Joomla 5/6 administrator view for the GitHub helper. */
final class HtmlView extends BaseHtmlView
{
    public array $commitlist = [];
    public array $lists = [];
    public string $issuetitle = '';
    public string $message = '';
    public int $milestone = 1;
    public bool $hasConfiguredToken = false;

    public function display($tpl = null): void
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $layout = preg_replace('/_(3|4)$/', '', (string) $this->getLayout()) ?: 'default';

        $this->issuetitle = '';
        $this->message = '';
        $this->milestone = $app->isClient('administrator') ? 1 : 2;
        $this->hasConfiguredToken = trim(
            (string) ComponentHelper::getParams('com_sportsmanagement')->get('gh_token', '')
        ) !== '';

        if ($app->isClient('administrator')) {
            $this->issuetitle = 'Backend-View: ' . $input->getCmd('issueview')
                . ' Layout: ' . $input->getCmd('issuelayout');
        }

        $this->addTemplatePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/tmpl/github');

        if ($layout === 'addissue') {
            $this->prepareAddIssue();
            $this->setLayout('add_issue');
        } elseif ($layout === 'github_result') {
            $this->setLayout('github_result');
        } else {
            Factory::getApplication()->getDocument()->getWebAssetManager()->registerAndUseStyle(
                'com_sportsmanagement.octicons',
                Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/octicons.css'
            );

            $model = $this->getModel();
            $this->commitlist = $model && method_exists($model, 'getGithubList')
                ? (array) $model->getGithubList()
                : [];
            $this->setLayout('default');
        }

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_TITLE'), 'github');
        ToolbarHelper::back();

        parent::display($tpl);
    }

    private function prepareAddIssue(): void
    {
        $labels = [
            'bug' => 'COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NI_BUG',
            'duplicate' => 'COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NI_DUPLICATE',
            'enhancement' => 'COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NI_ENHANCEMENT',
            'invalid' => 'COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NI_INVALID',
            'question' => 'COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NI_QUESTION',
            'wontfix' => 'COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NI_WONTFIX',
        ];
        $labelOptions = [];

        foreach ($labels as $value => $label) {
            $labelOptions[] = HTMLHelper::_('select.option', $value, Text::_($label));
        }

        $milestones = [
            2 => 'COM_SPORTSMANAGEMENT_ADMIN_GITHUB_MI_FRONTEND',
            3 => 'COM_SPORTSMANAGEMENT_ADMIN_GITHUB_MI_MODULES',
            4 => 'COM_SPORTSMANAGEMENT_ADMIN_GITHUB_MI_EXTENSIONS',
            1 => 'COM_SPORTSMANAGEMENT_ADMIN_GITHUB_MI_BACKEND',
        ];
        $milestoneOptions = [];

        foreach ($milestones as $value => $label) {
            $milestoneOptions[] = HTMLHelper::_('select.option', $value, Text::_($label));
        }

        $this->lists = [
            'labels' => HTMLHelper::_(
                'select.genericlist',
                $labelOptions,
                'labels',
                'class="form-select"',
                'value',
                'text',
                'bug'
            ),
            'milestones' => HTMLHelper::_(
                'select.genericlist',
                $milestoneOptions,
                'milestones',
                'class="form-select"',
                'value',
                'text',
                $this->milestone
            ),
        ];
    }
}
