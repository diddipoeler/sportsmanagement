<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Division;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\DivisionModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

/** Native Joomla 5/6 administrator edit view for a division. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;
    public ?object $project = null;
    public ?Form $extended = null;

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $input->set('hidemainmenu', true);

        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if (!$this->form) {
            throw new \RuntimeException('Division form could not be loaded.', 500);
        }

        $model = $this->getModel();

        if (!$model instanceof DivisionModel) {
            throw new \RuntimeException('Division view requires DivisionModel.', 500);
        }

        $divisionId = (int) ($this->item->id ?? 0);
        $projectId = (int) ($this->item->project_id ?? 0);

        if ($projectId <= 0) {
            $projectId = (int) $app->getUserState(
                'com_sportsmanagement.pid',
                $input->getInt('pid', 0)
            );
        }

        if ($projectId > 0) {
            $app->setUserState('com_sportsmanagement.pid', $projectId);
            $input->set('pid', $projectId);
            $this->form->setValue('project_id', null, $projectId);
            $this->project = $this->loadProject($model, $projectId);
        }

        $teamCount = $divisionId > 0 ? $model->count_teams_division($divisionId) : 0;
        $this->extended = $this->loadExtendedForm(
            (string) ($this->item->rankingparams ?? ''),
            $teamCount
        );

        $isNew = $divisionId <= 0;
        ToolbarHelper::title(
            Text::_($isNew ? 'COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_NEW' : 'COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_EDIT'),
            'division'
        );
        ToolbarHelper::apply('division.apply');
        ToolbarHelper::save('division.save');
        ToolbarHelper::save2new('division.save2new');
        ToolbarHelper::save2copy('division.save2copy');
        ToolbarHelper::cancel('division.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        parent::display($tpl);
    }

    private function loadProject(DivisionModel $model, int $projectId): ?object
    {
        $db = $model->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    private function loadExtendedForm(string $stored, int $teamCount): ?Form
    {
        $path = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/assets/extended/division.xml';

        if (!is_file($path)) {
            return null;
        }

        try {
            $registry = new Registry();

            if ($stored !== '') {
                $registry->loadString($stored);
            }

            $form = Form::getInstance(
                'com_sportsmanagement.division.extended',
                $path,
                ['control' => 'extended'],
                false,
                '/config'
            );
            $form->bind($registry);
            $form->setFieldAttribute('rankingparams', 'rankingteams', (string) max(0, $teamCount));

            return $form;
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');

            return null;
        }
    }
}
