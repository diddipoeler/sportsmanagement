<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Round;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator edit view for a project round. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;
    public int $project_id = 0;
    public int $project_art_id = 0;
    public int $project = 0;

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
            throw new \RuntimeException('Round form could not be loaded.', 500);
        }

        $this->project_id = (int) $app->getUserState(
            'com_sportsmanagement.pid',
            $input->getInt('pid', (int) ($this->item->project_id ?? 0))
        );
        $this->project_art_id = (int) $app->getUserState('com_sportsmanagement.project_art_id', 0);
        $this->project = $this->project_id;

        if ($this->project_id > 0) {
            $app->setUserState('com_sportsmanagement.pid', $this->project_id);
            $input->set('pid', $this->project_id);
            $this->form->setValue('project_id', null, $this->project_id);
        }

        $isNew = empty($this->item->id);
        $today = date('Y-m-d');

        if ($isNew) {
            $this->form->setValue('round_date_first', null, $today);
            $this->form->setValue('round_date_last', null, $today);
            $this->form->setValue('name', null, 'Spieltag');
            $this->form->setValue('roundcode', null, '1');
            $this->item->round_date_first = '0000-00-00';
            $this->item->round_date_last = '0000-00-00';
        } else {
            if ((string) ($this->item->round_date_first ?? '') === '0000-00-00') {
                $this->form->setValue('round_date_first', null, $today);
            }

            if ((string) ($this->item->round_date_last ?? '') === '0000-00-00') {
                $this->form->setValue('round_date_last', null, $today);
            }
        }

        ToolbarHelper::title(
            Text::_($isNew ? 'COM_SPORTSMANAGEMENT_ADMIN_ROUND_NEW' : 'COM_SPORTSMANAGEMENT_ADMIN_ROUND_EDIT'),
            'round'
        );
        ToolbarHelper::apply('round.apply');
        ToolbarHelper::save('round.save');
        ToolbarHelper::save2new('round.save2new');
        ToolbarHelper::save2copy('round.save2copy');
        ToolbarHelper::cancel('round.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        parent::display($tpl);
    }
}
