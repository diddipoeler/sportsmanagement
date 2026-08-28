<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Uefawertung;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\UefawertungModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** Native Joomla 5/6 view for the UEFA coefficient table. */
final class HtmlView extends SportsManagementProjectHtmlView
{
    public array $lists = [];
    public array $uefapoints = [];
    public array $seasonnames = [];

    protected function requiresLegacyPresentationDependencies(): bool
    {
        return false;
    }

    protected function prepareView(): void
    {
        $model = $this->getModel();

        if (!$model instanceof UefawertungModel) {
            throw new \RuntimeException('Uefawertung view requires UefawertungModel.', 500);
        }

        $selectedYear = trim($model->coefficientyear);
        $coefficientYears = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SEASON')),
            ...$model->getcoefficientyears(),
        ];

        $this->lists['coefficientyears'] = HTMLHelper::_(
            'select.genericList',
            $coefficientYears,
            'coefficientyear',
            'class="form-select" onchange="this.form.submit();" style="width:120px"',
            'id',
            'name',
            $selectedYear
        );
        $this->uefapoints = $model->getcoefficientyearspoints($selectedYear);
        $this->seasonnames = $model->getSeasonNames($selectedYear);
        asort($this->seasonnames, SORT_NATURAL);

        $activeMenu = $this->app->getMenu()->getActive();
        $pageTitle = trim((string) ($activeMenu->title ?? ''));

        if ($pageTitle === '') {
            $pageTitle = 'UEFA 5-Jahreswertung';
        }

        $this->headertitle = $pageTitle;
        $this->getDocument()->setTitle($pageTitle);
    }
}
