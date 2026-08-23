<?php
/**
 * SportsManagement administrator rounds view.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Table\RoundTable;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

class sportsmanagementViewRounds extends sportsmanagementView
{
    public function init()
    {
        $this->massadd = 0;
        $this->populate = 0;
        $tpl = null;

        $this->document->addStyleSheet(
            Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/form_control.css'
        );

        switch ($this->getLayout()) {
            case 'default':
            case 'default_3':
            case 'default_4':
                $this->_displayDefault($tpl);
                return;

            case 'populate':
            case 'populate_3':
            case 'populate_4':
                $this->_displayPopulate($tpl);
                return;

            case 'massadd':
            case 'massadd_3':
            case 'massadd_4':
                $this->_displayMassadd($tpl);
                return;
        }
    }

    public function _displayDefault($tpl)
    {
        $this->get('Items');
        $this->table = new RoundTable($this->model->getDatabase());
        $this->project_id = (int) $this->app->getUserState('com_sportsmanagement.pid', 0);
        $this->project = $this->model->getProject($this->project_id);

        $lists = [];
        $lists['tournementround'] = [
            HTMLHelper::_('select.option', '0', Text::_('JNO')),
            HTMLHelper::_('select.option', '1', Text::_('JYES')),
        ];

        $this->lists = $lists;
        $this->matchday = $this->items;
    }

    public function _displayPopulate($tpl)
    {
        $this->document->setTitle(Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_TITLE'));

        $options = [
            HTMLHelper::_(
                'select.option',
                0,
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_TYPE_SINGLE_ROUND_ROBIN')
            ),
            HTMLHelper::_(
                'select.option',
                1,
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_TYPE_DOUBLE_ROUND_ROBIN')
            ),
            HTMLHelper::_(
                'select.option',
                2,
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_TYPE_TOURNAMENT_ROUND_ROBIN')
            ),
        ];
        $lists = [];
        $lists['scheduling'] = HTMLHelper::_(
            'select.genericlist',
            $options,
            'scheduling',
            '',
            'value',
            'text'
        );

        $this->project_id = (int) $this->app->getUserState('com_sportsmanagement.pid', 0);
        $teams = $this->model->getProjectTeamsOptions($this->project_id);
        $project = $this->model->getProject($this->project_id);
        $teamOptions = [];

        foreach ($teams as $team) {
            $teamOptions[] = HTMLHelper::_('select.option', $team->value, $team->text);
        }

        $lists['teamsorder'] = HTMLHelper::_(
            'select.genericlist',
            $teamOptions,
            'teamsorder[]',
            'multiple="multiple" size="20"'
        );

        $this->projectws = $project;
        $this->lists = $lists;
        $this->populate = 1;
        $this->setLayout('populate');
    }

    public function _displayMassadd($tpl)
    {
        $this->project_id = (int) $this->app->getUserState('com_sportsmanagement.pid', 0);
        $this->project = $this->model->getProject($this->project_id);
        $this->setLayout('massadd');
    }

    protected function addToolbar()
    {
        $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_TITLE');
        ToolbarHelper::back(
            'JPREV',
            'index.php?option=com_sportsmanagement&view=project&layout=panel&id=' . $this->project_id
        );

        if (!$this->massadd) {
            if ($this->populate) {
                $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_TITLE');
                ToolbarHelper::apply('round.startpopulate');
                ToolbarHelper::back();
                parent::addToolbar();
                return;
            }

            ToolbarHelper::publishList('rounds.publish');
            ToolbarHelper::unpublishList('rounds.unpublish');
            ToolbarHelper::divider();
            ToolbarHelper::custom(
                'rounds.populate',
                'purge.png',
                'purge_f2.png',
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_POPULATE_BUTTON'),
                false
            );
            ToolbarHelper::divider();
            ToolbarHelper::apply('rounds.saveshort');
            ToolbarHelper::divider();
            sportsmanagementHelper::ToolbarButton(
                'massadd',
                'new',
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_MASSADD_BUTTON')
            );
            ToolbarHelper::addNew('round.save');
            ToolbarHelper::divider();
            ToolbarHelper::deleteList(
                '',
                'rounds.deleteroundmatches',
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_MASSDEL_BUTTON')
            );
            parent::addToolbar();
            return;
        }

        ToolbarHelper::custom(
            'round.cancelmassadd',
            'cancel.png',
            'cancel_f2.png',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_MASSADD_CANCEL'),
            false
        );
    }
}
