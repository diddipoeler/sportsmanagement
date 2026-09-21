<?php
/**
 * Native Joomla 5/6 administrator view for tournament trees.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Treetos;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Diddipoeler\Component\SportsManagement\Administrator\Model\TreetosModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator view for tournament trees. */
final class HtmlView extends BaseHtmlView
{
    public array $items = [];
    public $pagination;
    public $state;
    public ?object $project = null;
    public ?object $projectws = null;
    public int $project_id = 0;
    public int $division = 0;
    public array $divisions = [];

    public function display($tpl = null)
    {
        $app = self::administratorApplication();
        $model = $this->getModel();

        if (!$model instanceof TreetosModel) {
            throw new \RuntimeException('Treetos model could not be loaded.', 500);
        }

        $this->state = $model->getState();
        $this->project_id = $model->getProjectId();
        $this->division = (int) $this->state->get('filter.division', 0);
        $this->project = $model->getProject();
        $this->projectws = $this->project;
        $this->items = $model->getItems() ?: [];
        $this->pagination = $model->getPagination();

        $this->divisions = [
            (object) [
                'value' => 0,
                'text' => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DIVISION'),
            ],
            ...$model->getDivisions(),
        ];

        if ($errors = $model->getErrors()) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if (!$this->project) {
            $app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 'error');
            $this->project = (object) [
                'id' => $this->project_id,
                'name' => '',
                'project_type' => '',
            ];
            $this->projectws = $this->project;
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    private function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_TITLE'), 'tree-2');

        if ($this->project_id > 0) {
            ToolbarHelper::back(
                'JPREV',
                Route::_(
                    'index.php?option=com_sportsmanagement&view=project&layout=panel&id=' . $this->project_id,
                    false
                )
            );
        }

        ToolbarHelper::apply('treeto.saveshort');
        ToolbarHelper::publishList('treetos.publish');
        ToolbarHelper::unpublishList('treetos.unpublish');
        ToolbarHelper::divider();
        ToolbarHelper::addNew('treetos.save');
        ToolbarHelper::deleteList(
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_WARNING'),
            'treeto.remove'
        );
    }
    private static function administratorApplication(): AdministratorApplication
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement treetos view requires the Joomla administrator application.', 500);
        }

        return $app;
    }

}
