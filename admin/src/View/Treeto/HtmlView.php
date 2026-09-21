<?php
/**
 * Native Joomla 5/6 administrator view for tournament tree editing and node generation.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Treeto;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\TreetoModel;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator view for tournament tree editing and node generation. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;
    public $treeto;
    public $projectws;
    public int $project_id = 0;

    public function display($tpl = null)
    {
        $app = self::administratorApplication();
        $input = $app->getInput();
        $layout = preg_replace('/_(?:3|4|5)$/', '', (string) $this->getLayout()) ?: 'edit';

        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        $model = $this->getModel();

        if (!$model instanceof TreetoModel || !$this->form || !$this->item) {
            throw new \RuntimeException('Tournament tree could not be loaded.', 500);
        }

        $this->project_id = $input->getInt('pid')
            ?: (int) ($this->item->project_id ?? 0)
            ?: (int) $app->getUserState('com_sportsmanagement.pid', 0);

        if ($this->project_id > 0) {
            $app->setUserState('com_sportsmanagement.pid', $this->project_id);
        }

        $this->projectws = $model->getProject($this->project_id);

        if (!$this->projectws) {
            throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
        }

        $this->treeto = $this->item;

        if ($layout === 'gennode') {
            $this->setLayout('gennode');
            ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETO_TITLE_GENERATE'), 'tree-2');
            ToolbarHelper::back(
                'JPREV',
                'index.php?option=com_sportsmanagement&view=treetos&pid=' . $this->project_id
            );
        } else {
            $this->setLayout('edit');
            $input->set('hidemainmenu', true);
            ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETO_TITLE'), 'tree-2');
            ToolbarHelper::apply('treeto.apply');
            ToolbarHelper::save('treeto.save');
            ToolbarHelper::cancel('treeto.cancel', 'JTOOLBAR_CLOSE');
        }

        parent::display($tpl);
    }
    private static function administratorApplication(): AdministratorApplication
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement treeto view requires the Joomla administrator application.', 500);
        }

        return $app;
    }

}
