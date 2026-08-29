<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\FormModel;
use Throwable;

/**
 * Joomla 5/6 form model for the small SportsManagement sample record updater.
 */
final class UpdsportsmanagementModel extends FormModel
{
    private function siteApplication(): SiteApplication
    {
        return Factory::getContainer()->get(SiteApplication::class);
    }

    public function getForm($data = [], $loadData = true): Form|false
    {
        try {
            return $this->loadForm(
                'com_sportsmanagement.updsportsmanagement',
                'updsportsmanagement',
                [
                    'control' => 'jform',
                    'load_data' => (bool) $loadData,
                ]
            );
        } catch (Throwable $e) {
            $this->siteApplication()->enqueueMessage($e->getMessage(), 'error');
            return false;
        }
    }

    public function getItem(): ?object
    {
        $app = $this->siteApplication();
        $input = $app->getInput();
        $itemId = $input->getInt('Itemid', 0);

        if ($itemId > 0) {
            $menu = $app->getMenu();
            if ($menu) {
                $this->setState('menuparams', $menu->getParams($itemId));
            }
        }

        $id = (int) $this->getState('sportsmanagement.id', 0);
        if ($id <= 0) {
            $id = $input->getInt('id', 0);
        }

        if ($id <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('greeting'),
            ])
            ->from($db->quoteName('#__sportsmanagement'))
            ->where($db->quoteName('id') . ' = ' . $id);

        try {
            $db->setQuery($query, 0, 1);
            return $db->loadObject() ?: null;
        } catch (Throwable $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
            return null;
        }
    }

    public function updItem(array $data): bool
    {
        $id = (int) ($data['id'] ?? 0);
        $greeting = trim((string) ($data['greeting'] ?? ''));

        if ($id <= 0 || $greeting === '') {
            return false;
        }

        $record = (object) [
            'id' => $id,
            'greeting' => $greeting,
        ];

        try {
            return (bool) $this->getDatabase()->updateObject(
                '#__sportsmanagement',
                $record,
                'id'
            );
        } catch (Throwable $e) {
            $this->siteApplication()->enqueueMessage($e->getMessage(), 'error');
            return false;
        }
    }
}
