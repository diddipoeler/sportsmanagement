<?php
/**
 * Native Joomla 5/6 administrator SportsManagement Division edit view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Division;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtendedFormHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Model\DivisionModel;
use Joomla\CMS\Application\AdministratorApplication;
use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator edit view for a division. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;
    public ?object $project = null;
    public ?Form $extended = null;

    /**
     * Render the native division edit form and its parent project context.
     *
     * Verifies the MVC model before reading its form, item and state.
     * Uses the model's selected database for project metadata and team
     * counts so external database records are not mixed with local data.
     *
     * @param string|null $tpl Optional layout suffix.
     * @return void
     * @throws \RuntimeException If the model or form is unavailable.
     */
    public function display($tpl = null)
    {
        $app = self::administratorApplication();
        $input = $app->getInput();
        $input->set('hidemainmenu', true);

        $model = $this->getModel();

        if (!$model instanceof DivisionModel) {
            throw new \RuntimeException('Division view requires DivisionModel.', 500);
        }

        $this->form = $model->getForm();
        $this->item = $model->getItem();
        $this->state = $model->getState();

        if ($errors = $model->getErrors()) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if (!$this->form || !$this->item) {
            throw new \RuntimeException('Division form could not be loaded.', 500);
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
            $this->project = $model->getProjectForDivision($projectId);
        }

        $teamCount = $divisionId > 0 ? $model->count_teams_division($divisionId) : 0;
        $this->extended = (new ExtendedFormHelper())->load(
            'extended',
            'division',
            (string) ($this->item->rankingparams ?? '')
        );

        if ($this->extended) {
            $this->extended->setFieldAttribute(
                'rankingparams',
                'rankingteams',
                (string) max(0, $teamCount)
            );
        }

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

    /**
     * Resolve the validated administrator application through the shared service.
     *
     * Keeps Joomla client and service-container checks in the component
     * resolver rather than duplicating them in the division view.
     *
     * @return AdministratorApplication Active Joomla backend application.
     */
    private static function administratorApplication(): AdministratorApplication
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }
}
