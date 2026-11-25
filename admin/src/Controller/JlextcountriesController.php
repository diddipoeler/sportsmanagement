<?php
/**
 * Joomla 5/6 SportsManagement administrator controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

/** Native Joomla 5/6 administrator controller for countries. */
final class JlextcountriesController extends SportsManagementAdminController
{
    /**
     * Resolve the administrator country model through Joomla MVC.
     *
     * Forwards the requested name, administrator namespace and factory
     * options without discarding additional caller configuration.
     *
     * @param string $name Requested country model name.
     * @param string $prefix Administrator MVC model namespace.
     * @param array $config Joomla model factory options.
     * @return mixed Resolved model or the parent's failure value.
     */
    public function getModel($name = 'Jlextcountry', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Import postal code information for selected countries.
     *
     * Requires a valid Joomla form token and component edit permission,
     * reads the selected country IDs from POST, removes invalid or duplicate
     * IDs and calls the native import model only when it is available.
     * Returns the administrator to the country overview after completion.
     *
     * @return void
     * @throws \RuntimeException If the current user cannot edit countries
     *     or the required import model is unavailable.
     */
    public function importplz(): void
    {
        $this->checkToken();
        $this->assertWritePermission('core.edit');

        $cid = $this->app->getInput()->post->get('cid', [], 'array');
        $cid = self::selectedCountryIds($cid);

        if ($cid !== []) {
            $model = $this->getModel();

            if (!is_object($model) || !is_callable([$model, 'importplz'])) {
                throw new \RuntimeException('SportsManagement country import model is unavailable.', 500);
            }

            $model->importplz($cid);
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=jlextcountries');
    }
    /**
     * Validate selected country IDs before invoking the postal code importer.
     * Accepts positive integers and decimal strings, rejecting malformed,
     * negative and overflowing values and removing duplicate references.
     * @param mixed $selection Raw Joomla POST selection.
     * @return array<int,int> Unique valid country identifiers.
     */
    private static function selectedCountryIds(mixed $selection): array
    {
        if (!is_array($selection)) {
            return [];
        }

        $ids = [];
        foreach ($selection as $value) {
            if (!is_int($value) && (!is_string($value) || strlen($value) > 64
                || preg_match('/^[0-9]+$/D', $value) !== 1)) {
                continue;
            }
            if (is_string($value)) {
                $value = ltrim($value, '0');
            }
            $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id !== false) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }
}
