<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Joomleagueimport;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

/** Native Joomla 5/6 administrator view for the JoomlaLeague import workflow. */
final class HtmlView extends BaseHtmlView
{
    public string $task = '';
    public string $request_url = '';
    public int $step = 0;
    public int $totals = 0;
    public int $bar_value = 0;
    public string $work_table = '';
    public array $joomleague = [];
    public array $sportsmanagement = [];
    public array $lists = [];
    public string $table_data_class = 'table table-striped';
    public string $sortColumn = '';
    public string $sortDirection = '';

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->addTemplatePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/footer/tmpl');
        $this->addTemplatePath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/listheader/tmpl');
    }

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $option = $input->getCmd('option', 'com_sportsmanagement') ?: 'com_sportsmanagement';
        $model = $this->getModel();

        if (!is_object($model) || !method_exists($model, 'newstructur')) {
            throw new \RuntimeException('JoomlaLeague import model is unavailable.', 500);
        }

        $layout = preg_replace('/_(?:3|4|5)$/', '', (string) $this->getLayout()) ?: 'default';
        $this->setLayout($layout);
        $this->task = $input->getCmd('task');
        $this->request_url = Uri::getInstance()->toString();

        if ($layout === 'positions') {
            $this->preparePositions($model);
        }

        $count = max(1, (int) ComponentHelper::getParams($option)->get('max_import_jl_import_steps', 1));
        $this->step = max(0, (int) $app->getUserState($option . '.step', 0));
        $this->totals = max(0, (int) $app->getUserState($option . '.totals', 0));

        if ($this->step <= $this->totals || $this->totals === 0) {
            $model->newstructur(0, $count);
            $this->totals = max(0, (int) $app->getUserState($option . '.totals', $this->totals));
            $this->bar_value = $this->totals > 0
                ? min(100, (int) round($this->step * 100 / $this->totals))
                : 0;
        } else {
            $this->step = 0;
            $this->bar_value = $this->totals > 0 ? 100 : 0;
            $this->work_table = '';
        }

        $this->registerProgressAssets($option);

        if ($this->totals > 0) {
            $this->step += $count;
            $app->setUserState($option . '.step', $this->step);
        }

        ToolbarHelper::title(
            'Bearbeitete Steps: ' . $this->step . ' von: ' . $this->totals,
            'joomleague-import'
        );

        parent::display($tpl);
    }

    private function preparePositions(object $model): void
    {
        if (!method_exists($model, 'getImportPositions')) {
            throw new \RuntimeException('JoomlaLeague position import data is unavailable.', 500);
        }

        $whichTable = Factory::getApplication()->getInput()->getCmd('filter_which_table', '');
        $this->joomleague = (array) ($model->getImportPositions('joomleague', $whichTable) ?: []);
        $this->sportsmanagement = (array) ($model->getImportPositions('sportsmanagement') ?: []);

        $positions = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_POSITION')),
        ];
        $result = $model->getImportPositions('sportsmanagement');
        if ($result) {
            $positions = array_merge($positions, (array) $result);
        }

        $tables = [
            HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TABLE')),
            HTMLHelper::_('select.option', 'project_position', Text::_('project_position')),
            HTMLHelper::_('select.option', 'person', Text::_('person')),
        ];

        $this->lists = [
            'whichtable' => HTMLHelper::_(
                'select.genericList',
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

        ToolbarHelper::custom(
            'joomleagueimports.updatepositions',
            'upload',
            'upload',
            Text::_('COM_SPORTSMANAGEMENT_JL_IMPORT_POSITION_UPDATE'),
            false
        );
        ToolbarHelper::custom(
            'joomleagueimports.updateplayerproposition',
            'upload',
            'upload',
            Text::_('COM_SPORTSMANAGEMENT_JL_IMPORT_PLAYER_PRO_POSITION_UPDATE'),
            false
        );
        ToolbarHelper::custom(
            'joomleagueimports.updatestaffproposition',
            'upload',
            'upload',
            Text::_('COM_SPORTSMANAGEMENT_JL_IMPORT_STAFF_PRO_POSITION_UPDATE'),
            false
        );
    }

    private function registerProgressAssets(string $option): void
    {
        $document = $this->getDocument();
        $assets = $document->getWebAssetManager();
        $assets->useScript('jquery');
        $assets->registerAndUseStyle(
            'com_sportsmanagement.joomleagueimport.progress',
            Uri::root(true) . '/administrator/components/' . $option . '/assets/css/progressbar.css',
            ['version' => 'auto']
        );

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

        $document->addScriptDeclaration($javascript);
    }
}
