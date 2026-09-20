<?php
/**
 * Native Joomla 5/6 administrator editor for prediction games.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictiongame;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictiongameModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\PredictiongamesModel;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator editor for prediction games. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public array $pred_admins = [];
    public array $pred_projects = [];

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof PredictiongameModel) {
            throw new \RuntimeException('PredictiongameModel is unavailable.', 500);
        }

        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if (!$this->form || !$this->item) {
            throw new \RuntimeException('Prediction game form data is unavailable.', 500);
        }

        $predictionId = (int) ($this->item->id ?? 0);
        $admins = $predictionId > 0 ? PredictiongamesModel::getAdmins($predictionId) : [];
        $projects = $predictionId > 0 ? $model->getPredictionProjectIDs($predictionId) : [];

        $this->pred_admins = is_array($admins) ? $admins : [];
        $this->pred_projects = is_array($projects) ? $projects : [];
        $this->form->setValue('user_ids', null, $this->pred_admins);
        $this->form->setValue('project_ids', null, $this->pred_projects);

        if (PredictiongameModel::$seasonid > 0) {
            $this->form->setValue('s', null, PredictiongameModel::$seasonid);
        }

        self::administratorApplication()->getInput()->set('hidemainmenu', true);
        $this->addToolbar();
        parent::display($tpl);
    }

    private function addToolbar(): void
    {
        $isNew = (int) ($this->item->id ?? 0) <= 0;

        ToolbarHelper::title(
            Text::_($isNew
                ? 'COM_SPORTSMANAGEMENT_ADMIN_PGAME_NEW'
                : 'COM_SPORTSMANAGEMENT_ADMIN_PGAME_EDIT'),
            'edit'
        );
        ToolbarHelper::apply('predictiongame.apply');
        ToolbarHelper::save('predictiongame.save');
        ToolbarHelper::cancel('predictiongame.cancel');
    }
    private static function administratorApplication(): AdministratorApplication
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement prediction game view requires the Joomla administrator application.', 500);
        }

        return $app;
    }

}
