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

use Joomla\Database\ParameterType;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;

final class LeaguelistField extends SportsManagementListField
{
    protected $type = 'leaguelist';

    /**
     * Render the league selection control in Joomla administrator forms.
     *
     * Enables form submission on project lists only when a valid custom
     * onchange handler is absent. Retains configured handlers and delegates
     * input rendering to Joomla's inherited list-field implementation.
     *
     * @return string League selector HTML from the parent field.
     */
    protected function getInput(): string
    {
        $view = $this->administratorApplication()->getInput()->getCmd('view', '');

        $onchange = $this->element['onchange'] ?? '';

        if ($view === 'projects' && (!is_string($onchange) || trim($onchange) === '')) {
            $this->element['onchange'] = 'this.form.submit();';
        }

        return parent::getInput();
    }

    /**
     * Build league options for a project or a country-filtered projects list.
     *
     * Reads an existing project's league country from the component database,
     * or uses a validated posted country selector on the list view. Supports
     * the old projects_search_nation request filter when no nested POST
     * filter was supplied. Builds quoted Joomla SQL conditions and combines
     * league names and IDs with inherited administrator select options.
     *
     * @return array<int,object> Joomla list options including eligible leagues.
     */
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
                ->where($db->quoteName('p.id') . ' = :projectId')
                ->bind(':projectId', $projectId, ParameterType::INTEGER);
            $db->setQuery($query);
            $rawCountry = $db->loadResult();
            $country = is_string($rawCountry) ? $rawCountry : '';
        } elseif ($view === 'projects') {
            $post = $input->post->getArray();
            $country = self::postedCountry($post['filter'] ?? null);

            // Older project filters submit a flat request key. Retain that
            // Joomla 3-era input contract, but inspect the raw value before
            // using it in a Joomla 5/6 database query.
            if ($country === '') {
                $legacyCountry = $input->get('projects_search_nation', null, 'RAW');
                $country = self::postedCountry(['search_nation' => $legacyCountry]);
            }
        }

        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_league'));

        if ($country !== '') {
            $query->where($db->quoteName('country') . ' = :country')
                ->bind(':country', $country, ParameterType::STRING);
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

    /**
     * Parse a posted country search filter without PHP 8 type coercion.
     *
     * Rejects non-array containers and nested country values as well as
     * oversized or control-byte strings. Valid country strings retain
     * the legacy trimming behaviour used by the administrator list.
     *
     * @param mixed $filter Posted Joomla filter settings.
     * @return string Country code or empty string for malformed input.
     */
    private static function postedCountry(mixed $filter): string
    {
        if (!is_array($filter)) {
            return '';
        }

        $country = $filter['search_nation'] ?? null;

        if (!is_string($country) || strlen($country) > 64
            || preg_match('/[\\x00-\\x1F\\x7F]/', $country) === 1) {
            return '';
        }

        return trim($country);
    }

    /**
     * Resolve the administrator application from Joomla dependency injection.
     *
     * Obtains the strongly typed administrator application rather than using
     * the removed legacy Joomla application factory and refuses wrong-client
     * services before reading request input.
     *
     * @return AdministratorApplication Active backend application service.
     * @throws \\RuntimeException When the service has an unexpected type.
     */
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
