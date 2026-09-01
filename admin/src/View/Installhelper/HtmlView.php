<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Installhelper;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator installation-helper wizard. */
final class HtmlView extends BaseHtmlView
{
    public int $install_step = 1;
    public array $sportstypeOptions = [];
    public string $selectedSportstype = '';

    public function display($tpl = null)
    {
        $input = $this->getApplication()->getInput();
        $step = $input->getInt('step', 1);
        $this->install_step = in_array($step, [1, 2], true) ? $step : 1;
        $this->selectedSportstype = $input->getCmd('filter_sports_type', '');
        $this->sportstypeOptions = $this->buildSportstypeOptions();
        $this->setLayout('install_step_' . $this->install_step);

        ToolbarHelper::title(
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_INSTALLHELPER_' . $this->install_step),
            'wrench'
        );
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement');

        parent::display($tpl);
    }

    private function buildSportstypeOptions(): array
    {
        $types = [
            '' => 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE_FILTER',
            'soccer' => 'COM_SPORTSMANAGEMENT_ST_SOCCER',
            'tablesoccer' => 'COM_SPORTSMANAGEMENT_ST_TABLESOCCER',
            'hockey' => 'COM_SPORTSMANAGEMENT_ST_HOCKEY',
            'floorball' => 'COM_SPORTSMANAGEMENT_ST_FLOORBALL',
            'skater_hockey' => 'COM_SPORTSMANAGEMENT_ST_SKATER_HOCKEY',
            'american_football' => 'COM_SPORTSMANAGEMENT_ST_AMERICAN_FOOTBALL',
            'icehockey' => 'COM_SPORTSMANAGEMENT_ST_ICEHOCKEY',
            'volleyball' => 'COM_SPORTSMANAGEMENT_ST_VOLLEYBALL',
            'korfball' => 'COM_SPORTSMANAGEMENT_ST_KORFBALL',
            'handball' => 'COM_SPORTSMANAGEMENT_ST_HANDBALL',
            'tennis' => 'COM_SPORTSMANAGEMENT_ST_TENNIS',
            'tabletennis' => 'COM_SPORTSMANAGEMENT_ST_TABLETENNIS',
            'basketball' => 'COM_SPORTSMANAGEMENT_ST_BASKETBALL',
            'australien_rules_football' => 'COM_SPORTSMANAGEMENT_ST_AUSTRALIEN_RULES_FOOTBALL',
            'dart' => 'COM_SPORTSMANAGEMENT_ST_DART',
            'waterpolo' => 'COM_SPORTSMANAGEMENT_ST_WATERPOLO',
            'small_bore_rifle_association' => 'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION',
            'fistball' => 'COM_SPORTSMANAGEMENT_ST_FAUSTBALL',
        ];

        $options = [];

        foreach ($types as $value => $label) {
            $options[] = HTMLHelper::_('select.option', $value, Text::_($label));
        }

        return $options;
    }
}
