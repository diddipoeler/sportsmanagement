<?php
/** SportsManagement JoomlaLeague import administrator view. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

class sportsmanagementViewjoomleagueimport extends sportsmanagementView
{
    public function init(): void
    {
        $input = $this->app->getInput();
        $option = $input->getCmd('option', 'com_sportsmanagement');
        $model = $this->getModel();

        $this->task = $input->getCmd('task');
        $this->request_url = Uri::getInstance()->toString();

        if ($this->getLayout() === 'positions') {
            $this->initPositions();
        }

        $count = max(1, (int) ComponentHelper::getParams($option)->get('max_import_jl_import_steps', 1));
        $this->step = max(0, (int) $this->app->getUserState("$option.step", 0));
        $this->totals = max(0, (int) $this->app->getUserState("$option.totals", 0));

        if ($this->step <= $this->totals || $this->totals === 0) {
            $model->newstructur(0, $count);
            $this->totals = max(0, (int) $this->app->getUserState("$option.totals", $this->totals));
            $this->bar_value = $this->totals > 0
                ? min(100, (int) round($this->step * 100 / $this->totals))
                : 0;
        } else {
            $this->step = 0;
            $this->bar_value = $this->totals > 0 ? 100 : 0;
            $this->work_table = '';
        }

        $javascript = "\n"
            . 'jQuery(function() {' . "\n"
            . '  var progressbar = jQuery("#progressbar"),' . "\n"
            . '      progressLabel = jQuery(".progress-label");' . "\n"
            . '  progressbar.progressbar({' . "\n"
            . '    value: ' . (int) $this->bar_value . ',' . "\n"
            . '    create: function() {' . "\n"
            . '      progressLabel.text(' . json_encode($this->task . ' -> ') . ' + progressbar.progressbar("value") + "%");' . "\n"
            . '    },' . "\n"
            . '    change: function() {' . "\n"
            . '      progressLabel.text(progressbar.progressbar("value") + "%");' . "\n"
            . '    },' . "\n"
            . '    complete: function() {' . "\n"
            . '      progressLabel.text("Complete!");' . "\n"
            . '    }' . "\n"
            . '  });' . "\n"
            . '  function progress() {' . "\n"
            . '    var val = progressbar.progressbar("value") || 0;' . "\n"
            . '    progressbar.progressbar("value", ' . (int) $this->bar_value . ');' . "\n"
            . '    if (val < 99) {' . "\n"
            . '      setTimeout(progress, 100);' . "\n"
            . '    }' . "\n"
            . '  }' . "\n"
            . '  setTimeout(progress, 3000);' . "\n"
            . '});' . "\n";
        $this->document->addScriptDeclaration($javascript);

        if ($this->totals > 0) {
            $this->step += $count;
            $this->app->setUserState("$option.step", $this->step);
        }

        $this->document->addStylesheet(Uri::base() . 'components/' . $option . '/assets/css/progressbar.css');
        ToolbarHelper::title('Bearbeitete Steps: ' . $this->step . ' von: ' . $this->totals, 'joomleague-import');
    }

    public function initPositions(): void
    {
        $input = $this->app->getInput();
        $model = $this->getModel();
        $whichTable = $input->getCmd('filter_which_table', '');

        $this->joomleague = $model->getImportPositions('joomleague', $whichTable);
        $this->sportsmanagement = $model->getImportPositions('sportsmanagement');

        $positions = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_POSITION')),
        ];

        if ($result = $model->getImportPositions('sportsmanagement')) {
            $positions = array_merge($positions, $result);
        }

        $tables = [
            HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TABLE')),
            HTMLHelper::_('select.option', 'project_position', Text::_('project_position')),
            HTMLHelper::_('select.option', 'person', Text::_('person')),
        ];

        $this->lists = [
            'whichtable' => HTMLHelper::Select::genericlist(
                $tables,
                'filter_which_table',
                'class="inputbox" style="width:140px;" onchange="this.form.submit();"',
                'value',
                'text',
                $whichTable
            ),
            'position' => $positions,
            'search_mode' => '',
        ];

        ToolbarHelper::custom('joomleagueimports.updatepositions', 'upload', 'upload', Text::_('COM_SPORTSMANAGEMENT_JL_IMPORT_POSITION_UPDATE'), false);
        ToolbarHelper::custom('joomleagueimports.updateplayerproposition', 'upload', 'upload', Text::_('COM_SPORTSMANAGEMENT_JL_IMPORT_PLAYER_PRO_POSITION_UPDATE'), false);
        ToolbarHelper::custom('joomleagueimports.updatestaffproposition', 'upload', 'upload', Text::_('COM_SPORTSMANAGEMENT_JL_IMPORT_STAFF_PRO_POSITION_UPDATE'), false);
    }
}
