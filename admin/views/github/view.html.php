<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

/** Native-compatible GitHub helper view. */
class sportsmanagementViewgithub extends sportsmanagementView
{
    public function init(): void
    {
        $input = $this->app->getInput();
        $this->issuetitle = '';
        $this->message = '';
        $this->milestone = $this->app->isClient('administrator') ? 1 : 2;
        $this->hasConfiguredToken = trim((string) ComponentHelper::getParams($this->option)->get('gh_token', '')) !== '';

        if ($this->app->isClient('administrator')) {
            $this->issuetitle = 'Backend-View: ' . $input->getCmd('issueview')
                . ' Layout: ' . $input->getCmd('issuelayout');
        }

        switch ($this->getLayout()) {
            case 'addissue':
            case 'addissue_3':
            case 'addissue_4':
                $this->displayAddIssue();
                return;

            case 'github_result':
            case 'github_result_3':
            case 'github_result_4':
                $this->setLayout('github_result');
                return;
        }

        $this->document->getWebAssetManager()->registerAndUseStyle(
            'com_sportsmanagement.octicons',
            Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/octicons.css'
        );
        $this->commitlist = $this->model->getGithubList();
    }

    private function displayAddIssue(): void
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
        $this->setLayout('add_issue');
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_TITLE');
        ToolbarHelper::back();
    }
}
