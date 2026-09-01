<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Joomleagueimports;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JoomleagueimportsModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

/** Native Joomla 5/6 administrator view for the JoomLeague import workflow. */
final class HtmlView extends BaseHtmlView
{
    public int $cfg_jl_import = 1;
    public string $jl_table_import_step = '0';
    public int $selectedSportstype = 0;
    public array $sportstypeOptions = [];
    public array $agegroupOptions = [];
    public array $get_info_fields = [];
    public mixed $success = [];
    public string $request_url = '';

    public function display($tpl = null)
    {
        $app = $this->getApplication();
        $input = $app->getInput();
        $model = $this->getModel();

        if (!$model instanceof JoomleagueimportsModel) {
            throw new \RuntimeException('JoomleagueimportsModel could not be loaded.', 500);
        }

        $layout = preg_replace('/_(?:3|4|5)$/', '', (string) $this->getLayout()) ?: 'default';
        $layout = $layout === 'infofield' ? 'infofield' : 'default';
        $this->setLayout($layout);

        $this->cfg_jl_import = (int) ComponentHelper::getParams('com_sportsmanagement')->get('cfg_jl_import', 1);
        $this->jl_table_import_step = $input->getString('jl_table_import_step', '0');
        $this->selectedSportstype = $input->getInt('filter_sports_type', 0);
        $this->request_url = Uri::getInstance()->toString();
        $this->success = $app->getUserState('com_sportsmanagement.jl_table_import_success', []);

        $sportsTypes = $this->createAdminModel('Sportstypes')->getSportsTypes();
        $this->sportstypeOptions = array_merge(
            [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE_FILTER'))],
            array_map(
                static fn (object $item): object => HTMLHelper::_('select.option', (int) $item->id, (string) $item->name),
                $sportsTypes ?: []
            )
        );

        if ($layout === 'infofield') {
            $this->agegroupOptions = array_merge(
                [HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP'))],
                $this->createAdminModel('Agegroups')->getAgeGroups()
            );
            $this->get_info_fields = $model->get_info_fields() ?: [];
        } elseif ($this->jl_table_import_step === '0') {
            $databaseError = (int) $model->check_database();

            Log::add(
                Text::_($this->cfg_jl_import
                    ? 'COM_SPORTSMANAGEMENT_ADMIN_JL_IMPORT_ALLOWED_YES'
                    : 'COM_SPORTSMANAGEMENT_ADMIN_JL_IMPORT_ALLOWED_NO'),
                $this->cfg_jl_import ? Log::NOTICE : Log::ERROR,
                'jsmerror'
            );

            if ($databaseError > 0) {
                Log::add(
                    Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ERROR', $databaseError),
                    Log::ERROR,
                    'jsmerror'
                );
            }
        }

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_JOOMLEAGUE_IMPORT'), 'database');

        if ($layout === 'infofield') {
            ToolbarHelper::custom(
                'joomleagueimports.joomleaguesetagegroup',
                'check',
                'check',
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_SETAGEGROUP_START_BUTTON'),
                false
            );
        } else {
            ToolbarHelper::custom(
                'joomleagueimports.importjoomleaguenew',
                'play',
                'play',
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_START_BUTTON'),
                false
            );
        }

        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=projects');

        parent::display($tpl);
    }

    private function createAdminModel(string $name): object
    {
        $model = Factory::getApplication()
            ->bootComponent('com_sportsmanagement')
            ->getMVCFactory()
            ->createModel($name, 'Administrator', ['ignore_request' => true]);

        if ($model === null) {
            throw new \RuntimeException('SportsManagement model not found: ' . $name, 500);
        }

        return $model;
    }
}
