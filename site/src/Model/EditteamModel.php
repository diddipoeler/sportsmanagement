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

    public function updItem($data): bool
    {
        $data = (array) $data;
        $request = $data['request'] ?? [];

        if (is_array($request)) {
            foreach ($request as $key => $value) {
                $data[$key] = $value;
            }
        }

        unset($data['request']);

        // Keep explicit 0, false and empty-string values: administrators can
        // clear existing frontend fields and switch boolean options off.

        try {
            $table = $this->getTable('team');

            if (!$table->bind($data)) {
                return false;
            }

            if (!$table->check()) {
                return false;
            }

            return (bool) $table->store();
        } catch (\Throwable $e) {
            Log::add($e->getMessage(), Log::ERROR, 'jsmerror');
            $this->setError($e->getMessage());

            return false;
        }
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
