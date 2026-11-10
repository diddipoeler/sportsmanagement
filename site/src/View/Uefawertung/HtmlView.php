<?php
/**
 * Native Joomla 5/6 frontend SportsManagement Uefawertung view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Uefawertung;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\UefawertungModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\Language\Text;

/** Native Joomla 5/6 view for the UEFA coefficient table. */
final class HtmlView extends SportsManagementProjectHtmlView
{
    public array $lists = [];
    public array $uefapoints = [];
    public array $seasonnames = [];
    public array $availableYears = [];
    public string $selectedYear = '';

    /**
     * Disable global legacy rendering dependencies for UEFA coefficients.
     * @return bool False because this view uses native presentation services.
     */
    protected function requiresLegacyPresentationDependencies(): bool
    {
        return false;
    }

    /**
     * Prepare UEFA five-year coefficients and season selection controls.
     * Requires UefawertungModel, loads coefficient years and points, sorts
     * season labels and sets the Joomla page title from the active menu.
     * Accepts only a year advertised by the active database and stores
     * the options as plain strings for safe native template escaping.
     * @return void
     * @throws \RuntimeException If the native UEFA model is unavailable.
     */
    protected function prepareView(): void
    {
        $model = $this->getModel();

        if (!$model instanceof UefawertungModel) {
            throw new \RuntimeException('Uefawertung view requires UefawertungModel.', 500);
        }

        // Populate the native <select> with plain data, not prebuilt markup.
        // Only an available season can reach a bound coefficient query;
        // malformed or unrelated POST/GET values render a blank selection.
        $years = [];

        foreach ($model->getcoefficientyears() as $year) {
            if (!is_object($year)) {
                continue;
            }

            $id = $year->id ?? null;
            $name = $year->name ?? null;

            if ((!is_string($id) && !is_int($id))
                || !is_string($name)
                || !preg_match('/^[0-9]{4}(?:[-\/][0-9]{2,4})?$/D', (string) $id)) {
                continue;
            }

            $years[(string) $id] = $name;

            if (count($years) >= 150) {
                break;
            }
        }

        $this->availableYears = $years;
        $candidate = trim($model->coefficientyear);
        $this->selectedYear = array_key_exists($candidate, $years) ? $candidate : '';
        $this->uefapoints = $this->selectedYear !== ''
            ? $model->getcoefficientyearspoints($this->selectedYear)
            : [];
        $this->seasonnames = $this->selectedYear !== ''
            ? $model->getSeasonNames($this->selectedYear)
            : [];
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
