<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Templates;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\TemplatesModel;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator list view for project template settings. */
final class HtmlView extends BaseHtmlView
{
    public array $templates = [];
    public $projectws;
    public int $project_id = 0;
    public $pagination;
    public $state;
    public array $lists = [];
    public $master = false;

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof TemplatesModel) {
            throw new \RuntimeException('TemplatesModel could not be loaded.', 500);
        }

        $this->state = $model->getState();
        $this->project_id = $model->getProjectId();
        $this->projectws = $model->getProject();

        if (!$this->projectws) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
        }

        $templates = $model->getItems() ?: [];

        if (!empty($this->projectws->master_template)) {
            $masterTemplates = $model->getMasterTemplatesList(0);

            foreach ($masterTemplates as $template) {
                $template->text = Text::_((string) $template->text);
            }

            $options = [
                HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_SELECT_FROM_MASTER')),
            ];
            $options = array_merge($options, $masterTemplates);
            $this->lists['mastertemplates'] = HTMLHelper::_(
                'select.genericList',
                $options,
                'templateid',
                'class="form-select" onchange="Joomla.submitform(\'template.masterimport\', this.form)"',
                'value',
                'text',
                0
            );
            $this->master = $model->getMasterName();
            $templates = array_merge($templates, $model->getMasterTemplatesList(1));
        }

        $this->templates = $templates;
        $this->pagination = $model->getPagination();
        $this->addToolbar();

        parent::display($tpl);
    }

    private function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_TITLE'), 'copy');
        ToolbarHelper::back(
            'JPREV',
            'index.php?option=com_sportsmanagement&view=project&layout=panel&id=' . $this->project_id
        );
        ToolbarHelper::editList('template.edit');

        if (!empty($this->projectws->master_template)) {
            ToolbarHelper::deleteList('', 'template.remove', 'JTOOLBAR_DELETE');
        } else {
            ToolbarHelper::custom(
                'template.reset',
                'unblock',
                'unblock',
                Text::_('COM_SPORTSMANAGEMENT_GLOBAL_RESET'),
                true
            );
            ToolbarHelper::custom(
                'template.update',
                'wand',
                'wand',
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_UPDATE'),
                true
            );
        }

        ToolbarHelper::checkin('templates.checkin');
    }
}
