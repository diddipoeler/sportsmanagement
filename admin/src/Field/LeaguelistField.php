<?php
/**
 * Joomla 5/6 native league list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;

final class LeaguelistField extends SportsManagementListField
{
    protected $type = 'leaguelist';

    protected function getInput(): string
    {
        $view = $this->administratorApplication()->getInput()->getCmd('view', '');

        if ($view === 'projects' && trim((string) ($this->element['onchange'] ?? '')) === '') {
            $this->element['onchange'] = 'this.form.submit();';
        }

        return parent::getInput();
    }

    protected function getOptions(): array
    {
        $app = $this->administratorApplication();
        $input = $app->getInput();
        $view = $input->getCmd('view', '');
        $projectId = $input->getInt('id', 0);
        $country = '';
        $db = $this->getSportsManagementDatabase();

        if ($view === 'project' && $projectId > 0) {
            $query = $db->createQuery()
                ->select($db->quoteName('l.country'))
                ->from($db->quoteName('#__sportsmanagement_league', 'l'))
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_project', 'p')
                    . ' ON ' . $db->quoteName('p.league_id') . ' = ' . $db->quoteName('l.id')
                )
                ->where($db->quoteName('p.id') . ' = ' . (int) $projectId);
            $db->setQuery($query);
            $country = (string) ($db->loadResult() ?: '');
        } elseif ($view === 'projects') {
            $post = $input->post->getArray();
            $country = trim((string) ($post['filter']['search_nation'] ?? ''));
        }

        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_league'));

        if ($country !== '') {
            $query->where($db->quoteName('country') . ' = ' . $db->quote($country));
        }

        $query->order($db->quoteName('name'));
        $db->setQuery($query);

        $options = [];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => (string) $item->text . ' (' . (int) $item->value . ')',
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }

    private function administratorApplication(): AdministratorApplication
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app instanceof AdministratorApplication) {
            throw new \RuntimeException('SportsManagement administrator application is unavailable.');
        }

        return $app;
    }
}
