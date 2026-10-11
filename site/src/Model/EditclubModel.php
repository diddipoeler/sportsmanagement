<?php
/**
 * Joomla 5/6 frontend model for editing clubs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\ClubTable;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;

final class EditclubModel extends AdminModel
{
    public $latitude = null;
    public $longitude = null;
    public $projectid = 0;
    public $clubid = 0;
    public $club = null;
    public $name = 'club';

    public function __construct(
        $config = [],
        ?MVCFactoryInterface $factory = null,
        ?FormFactoryInterface $formFactory = null
    ) {
        parent::__construct($config, $factory, $formFactory);

        $input = $this->siteApplication()->getInput();
        $this->projectid = $input->getInt('p', 0);
        $this->clubid = $input->getInt('cid', 0);
    }

    public function updItem($data): bool
    {
        // Joomla form data can contain a nested request payload. Merge it
        // before date validation so callers cannot reintroduce an invalid
        // date or override a normalized date after checking it.
        $request = is_array($data['request'] ?? null) ? $data['request'] : [];
        unset($data['request'], $request['id']);
        $data = array_replace($data, $request);

        foreach (['founded', 'dissolved'] as $field) {
            $provided = trim((string) ($data[$field] ?? ''));

            if ($provided === '' || $provided === '0000-00-00') {
                $data[$field] = '0000-00-00';
                $data[$field . '_timestamp'] = 0;
                $data[$field . '_year'] = $field === 'founded' ? 'kein' : 0;
                continue;
            }

            $normalized = $this->normaliseFrontendDate($provided);

            if ($normalized === '') {
                $this->setError('Invalid ' . $field . ' date.');

                return false;
            }

            $data[$field] = $normalized;
            $data[$field . '_year'] = (int) substr($normalized, 0, 4);
            $data[$field . '_timestamp'] = $this->dateTimestamp($normalized);
        }

        try {
            $table = $this->getTable('club');

            if (!$table->bind($data) || !$table->check() || !$table->store()) {
                $this->setError((string) $table->getError());

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'jsmerror');
            $this->setError('Club changes could not be saved.');

            return false;
        }
    }

    public function getTable($type = 'club', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'club') === 0) {
            return new ClubTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    public function getForm($data = [], $loadData = true)
    {
        $option = $this->siteApplication()->getInput()->getCmd('option', 'com_sportsmanagement');
        $params = ComponentHelper::getParams($option);
        $mediaTool = trim((string) $params->get('cfg_which_media_tool', 'media'));
        $showTeamCommunity = (bool) $params->get('show_team_community', 0);

        if ($mediaTool === '' || ctype_digit($mediaTool)) {
            $mediaTool = 'media';
        }

        FormHelper::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');
        FormHelper::addFieldPrefix('Diddipoeler\\Component\\SportsManagement\\Administrator\\Field');

        $form = $this->loadForm(
            'com_sportsmanagement.' . $this->name,
            $this->name,
            ['load_data' => $loadData]
        );

        if (!$form) {
            return false;
        }

        if (!$showTeamCommunity) {
            $form->setFieldAttribute('merge_teams', 'type', 'hidden');
        }

        $mediaFields = [
            'logo_small' => ['ph_logo_small', 'com_sportsmanagement/database/clubs/small'],
            'logo_middle' => ['ph_logo_medium', 'com_sportsmanagement/database/clubs/medium'],
            'logo_big' => ['ph_logo_big', 'com_sportsmanagement/database/clubs/large'],
            'trikot_home' => ['ph_logo_small', 'com_sportsmanagement/database/clubs/trikot_home'],
            'trikot_away' => ['ph_logo_small', 'com_sportsmanagement/database/clubs/trikot_away'],
        ];

        foreach ($mediaFields as $field => [$placeholder, $directory]) {
            if (!$form->getField($field)) {
                continue;
            }

            $form->setFieldAttribute($field, 'default', (string) $params->get($placeholder, ''));
            $form->setFieldAttribute($field, 'directory', $directory);
            $form->setFieldAttribute($field, 'type', $mediaTool);
        }

        return $form;
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

    public function getData()
    {
        if ($this->club === null) {
            $this->club = $this->getTable('Club', 'sportsmanagementTable');
            $this->club->load($this->clubid);
        }

        return $this->club;
    }

    private function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    private function normaliseFrontendDate(string $date): string
    {
        $date = trim($date);

        // Joomla's calendar uses day-month-year, while native date inputs and
        // stored club dates may already use the ISO year-month-day format.
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $parts)) {
            [$year, $month, $day] = [(int) $parts[1], (int) $parts[2], (int) $parts[3]];
        } elseif (preg_match('/^(\d{2})[-.](\d{2})[.-](\d{4})$/', $date, $parts)) {
            [$day, $month, $year] = [(int) $parts[1], (int) $parts[2], (int) $parts[3]];
        } elseif (preg_match('/^(\d{2})(\d{2})(\d{4})$/', $date, $parts)) {
            [$day, $month, $year] = [(int) $parts[1], (int) $parts[2], (int) $parts[3]];
        } elseif (preg_match('/^(\d{2})(\d{2})(\d{2})$/', $date, $parts)) {
            [$day, $month, $year] = [
                (int) $parts[1],
                (int) $parts[2],
                (int) (substr(date('Y'), 0, 2) . $parts[3]),
            ];
        } else {
            return '';
        }

        return checkdate($month, $day, $year)
            ? sprintf('%04d-%02d-%02d', $year, $month, $day)
            : '';
    }

    private function dateTimestamp(string $date): int
    {
        return (int) strtotime($date);
    }
}
