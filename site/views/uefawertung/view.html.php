<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage uefawertung
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class sportsmanagementViewuefawertung extends sportsmanagementView
{
    public function init()
    {
        $this->project = sportsmanagementModelProject::getProject();
        $this->overallconfig = sportsmanagementModelProject::getOverallConfig();
        $this->config = sportsmanagementModelProject::getTemplateConfig('uefawertung');

        $selectYear = (string) ($this->model->coefficientyear ?? '');
        $coefficientYears = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SEASON')),
        ];
        $coefficientYears = array_merge($coefficientYears, $this->model->getcoefficientyears());

        $this->lists = [
            'coefficientyears' => HTMLHelper::_(
                'select.genericList',
                $coefficientYears,
                'coefficientyear',
                'class="inputbox" onChange="this.form.submit();" style="width:120px"',
                'id',
                'name',
                $selectYear
            ),
        ];

        $this->uefapoints = $this->model->getcoefficientyearspoints($selectYear);
        $this->seasonnames = $this->model->getSeasonNames($selectYear);
        asort($this->seasonnames);

        $this->document->setTitle($this->pagetitle);
    }
}
