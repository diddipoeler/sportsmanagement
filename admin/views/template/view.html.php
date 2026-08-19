<?php
/**
 * SportsManagement administrator template edit view.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class sportsmanagementViewTemplate extends sportsmanagementView
{
    public function init()
    {
        $lists = [];
        $this->project_id = (int) $this->app->getUserState("$this->option.pid", 0);
        $project = $this->model->getProject($this->project_id);

        if (!$project) {
            $this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 'error');

            return;
        }

        if ($this->form && $this->form->getName() === 'ranking') {
            $projectTeamsCount = $this->model->getProjectTeamsCount($this->project_id);
            $this->teamscount = $projectTeamsCount;
            $this->form->setFieldAttribute('colors_ranking', 'rankingteams', $projectTeamsCount);
            $this->form->setFieldAttribute('colors', 'type', 'hidden');

            $colors = (string) $this->form->getValue('colors', null, '');
            $colorsRanking = $this->form->getValue('colors_ranking', null, []);
            $colorsRanking = is_array($colorsRanking) ? $colorsRanking : [];
            $count = 1;

            foreach (explode(';', $colors) as $value) {
                if ($colors === '') {
                    continue;
                }

                $parts = explode(',', $value);

                if (count($parts) <= 1) {
                    continue;
                }

                $colorsRanking[$count] = array_replace(
                    [
                        'von' => '',
                        'bis' => '',
                        'color' => '',
                        'text' => '',
                    ],
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

            if (ComponentHelper::getParams($this->option)->get('show_debug_info_backend')) {
                $this->app->enqueueMessage(
                    __METHOD__ . ' colors_ranking entries: ' . count($colorsRanking),
                    'notice'
                );
            }

            $this->form->setValue('colors_ranking', null, $colorsRanking);
        }

        $masterId = !empty($project->master_template) ? (int) $project->master_template : -1;
        $templates = $this->model->getAllTemplatesList((int) $project->id, $masterId);
        $lists['templates'] = HTMLHelper::_(
            'select.genericlist',
            $templates,
            'new_id',
            'class="inputbox" size="1" onchange="Joomla.submitbutton(\'templates.changetemplate\');"',
            'value',
            'text',
            $this->item->id
        );
        $lists['templates'] .= '<input type="hidden" name="pid" value="' . (int) $project->id . '">';

        $this->template = $this->item;
        $this->templatename = $this->form ? $this->form->getName() : '';
        $this->project = $project;
        $this->lists = $lists;

        $language = $this->app->getLanguage();
        $language->load('com_contact', JPATH_ADMINISTRATOR, 'en-GB', true);
        $language->load('com_contact', JPATH_ADMINISTRATOR, $language->getDefault(), true);
        $language->load('com_contact', JPATH_ADMINISTRATOR, null, true);
    }

    protected function addToolbar()
    {
        $input = $this->app->getInput();
        $input->set('hidemainmenu', true);
        $input->set('pid', $this->project_id);
        $this->item->name = $this->item->template;
        $this->title = Text::sprintf(
            'COM_SPORTSMANAGEMENT_ADMIN_TEMPLATE_EDIT',
            Text::_($this->item->title)
        );
        $this->icon = 'template';
        parent::addToolbar();
    }
}
