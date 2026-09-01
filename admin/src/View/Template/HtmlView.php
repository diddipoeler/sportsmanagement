<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Template;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\TemplateModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator edit view for project template settings. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;
    public $project;
    public int $project_id = 0;
    public int $teamscount = 0;
    public string $templatename = '';
    public array $lists = [];

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $app->getInput()->set('hidemainmenu', true);

        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        $model = $this->getModel();

        if (!$model instanceof TemplateModel || !$this->form || !$this->item) {
            throw new \RuntimeException('Template settings could not be loaded.', 500);
        }

        $this->project_id = (int) ($this->item->project_id ?? 0);
        if ($this->project_id <= 0) {
            $this->project_id = $app->getInput()->getInt('pid', (int) $app->getUserState('com_sportsmanagement.pid', 0));
        }

        if ($this->project_id > 0) {
            $app->setUserState('com_sportsmanagement.pid', $this->project_id);
        }

        $this->project = $model->getProject($this->project_id);

        if (!$this->project) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
        }

        $this->templatename = (string) $this->form->getName();
        $this->prepareRankingForm($model);
        $this->buildTemplateSelector($model);

        $language = $app->getLanguage();
        $language->load('com_contact', JPATH_ADMINISTRATOR, 'en-GB', true);
        $language->load('com_contact', JPATH_ADMINISTRATOR, $language->getDefault(), true);
        $language->load('com_contact', JPATH_ADMINISTRATOR, null, true);

        ToolbarHelper::title(
            Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_EDIT', Text::_((string) ($this->item->title ?? ''))),
            'copy'
        );
        ToolbarHelper::apply('template.apply');
        ToolbarHelper::save('template.save');
        ToolbarHelper::cancel('template.cancel', 'JTOOLBAR_CLOSE');

        parent::display($tpl);
    }

    private function prepareRankingForm(TemplateModel $model): void
    {
        if ($this->templatename !== 'ranking') {
            return;
        }

        $this->teamscount = $model->getProjectTeamsCount($this->project_id);
        $this->form->setFieldAttribute('colors_ranking', 'rankingteams', $this->teamscount);
        $this->form->setFieldAttribute('colors', 'type', 'hidden');

        $colors = (string) $this->form->getValue('colors', null, '');
        $colorsRanking = $this->form->getValue('colors_ranking', null, []);
        $colorsRanking = is_array($colorsRanking) ? $colorsRanking : [];
        $count = 1;

        foreach (explode(';', $colors) as $value) {
            if ($value === '') {
                continue;
            }

            $parts = explode(',', $value);
            if (count($parts) <= 1) {
                continue;
            }

            $colorsRanking[$count] = array_replace(
                ['von' => '', 'bis' => '', 'color' => '', 'text' => ''],
                $colorsRanking[$count] ?? []
            );
            [
                $colorsRanking[$count]['von'],
                $colorsRanking[$count]['bis'],
                $colorsRanking[$count]['color'],
                $colorsRanking[$count]['text'],
            ] = array_pad($parts, 4, '');
            ++$count;
        }

        if (ComponentHelper::getParams('com_sportsmanagement')->get('show_debug_info_backend')) {
            Factory::getApplication()->enqueueMessage(
                __METHOD__ . ' colors_ranking entries: ' . count($colorsRanking),
                'notice'
            );
        }

        $this->form->setValue('colors_ranking', null, $colorsRanking);
    }

    private function buildTemplateSelector(TemplateModel $model): void
    {
        $masterId = !empty($this->project->master_template) ? (int) $this->project->master_template : -1;
        $templates = $model->getAllTemplatesList($this->project_id, $masterId);
        $this->lists['templates'] = HTMLHelper::_(
            'select.genericlist',
            $templates,
            'new_id',
            'class="form-select" onchange="Joomla.submitbutton(\'templates.changetemplate\')"',
            'value',
            'text',
            (int) ($this->item->id ?? 0)
        );
    }
}
