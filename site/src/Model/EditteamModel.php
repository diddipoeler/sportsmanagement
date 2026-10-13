<?php
/**
 * Native Joomla 5/6 frontend team editor model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\TeamTable;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\AdminModel;

/** Joomla 5/6 frontend model for editing teams. */
final class EditteamModel extends AdminModel
{
    public $latitude = null;
    public $longitude = null;
    public $name = 'editteam';

    private ?TeamTable $team = null;

    private function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    /**
     * Preserve native jform data and legacy editor fields without accepting
     * nested record IDs that could redirect a write to another team.
     */
    public function updItem($data): bool
    {
        $data = (array) $data;
        $teamId = self::positiveId($data['id'] ?? null);

        if ($teamId <= 0) {
            $this->setError('Invalid team record.');
            return false;
        }

        $request = is_array($data['request'] ?? null) ? $data['request'] : [];
        $form = is_array($data['jform'] ?? null) ? $data['jform'] : [];

        // Joomla's form input is nested under jform. Keep zero/false/empty
        // values, while the top-level editor ID remains authoritative.
        unset($data['request'], $data['jform']);
        unset($request['id'], $form['id']);
        $data = array_replace($data, $request, $form);
        $data['id'] = $teamId;

        try {
            $table = $this->getTable('team');

            if (!$table->bind($data) || !$table->check()) {
                return false;
            }

            return (bool) $table->store();
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'jsmerror');
            $this->setError('Team changes could not be saved.');
            return false;
        }
    }

    /** Reject partial, negative, floating-point and overflowing team IDs. */
    private static function positiveId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $raw = trim((string) $value);

        if ($raw === '' || !ctype_digit($raw)) {
            return 0;
        }

        $digits = ltrim($raw, '0');
        $digits = $digits === '' ? '0' : $digits;
        $max = (string) PHP_INT_MAX;

        if ($digits === '0'
            || strlen($digits) > strlen($max)
            || (strlen($digits) === strlen($max) && strcmp($digits, $max) > 0)) {
            return 0;
        }

        return (int) $digits;
    }

    public function getTable($type = 'team', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'team') === 0) {
            return new TeamTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    public function getForm($data = [], $loadData = true)
    {
        FormHelper::addFormPath(JPATH_SITE . '/components/com_sportsmanagement/models/forms');
        FormHelper::addFieldPrefix('Diddipoeler\\Component\\SportsManagement\\Administrator\\Field');

        $form = $this->loadForm(
            'com_sportsmanagement.' . $this->name,
            $this->name,
            ['load_data' => $loadData]
        );

        return $form ?: false;
    }

    protected function loadFormData()
    {
        $app = $this->siteApplication();
        $data = $app->getUserState('com_sportsmanagement.edit.' . $this->name . '.data', []);

        if (empty($data)) {
            $data = $this->getData();
        }

        return $data;
    }

    public function getData(): TeamTable
    {
        if ($this->team === null) {
            $teamId = $this->siteApplication()->getInput()->getInt('tid', 0);
            $this->team = $this->getTable('team');

            if ($teamId > 0) {
                $this->team->load($teamId);
            }
        }

        return $this->team;
    }
}
