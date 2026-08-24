<?php
namespace Diddipoeler\Plugin\System\SportsmanagementSiscron\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Application\BeforeRenderEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;
use Joomla\Filesystem\Folder;
use Joomla\Http\HttpFactory;

/**
 * Joomla 5/6 SIS Handball schedule refresh plugin.
 */
final class SportsmanagementSiscron extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    protected $autoloadLanguage = true;

    private int $sisType = 1;

    public static function getSubscribedEvents(): array
    {
        return [
            'onBeforeRender' => 'onBeforeRender',
        ];
    }

    public function onBeforeRender(BeforeRenderEvent $event): void
    {
        $app = $this->getApplication();
        $input = $app->getInput();

        if (!$app->isClient('site') || $input->getCmd('option') !== 'com_sportsmanagement') {
            return;
        }

        $projectId = $input->getInt('p', 0);

        if ($projectId <= 0) {
            return;
        }

        $params = ComponentHelper::getParams('com_sportsmanagement');
        $xmlBase = rtrim((string) $params->get('sis_xmllink', ''), '/');
        $clubNumber = (string) $params->get('sis_meinevereinsnummer', '');
        $clubPassword = (string) $params->get('sis_meinvereinspasswort', '');

        if ($xmlBase === '' || $clubNumber === '' || $clubPassword === '') {
            return;
        }

        $project = $this->getProject($projectId);

        if (!$project || (string) $project->name !== 'COM_SPORTSMANAGEMENT_ST_HANDBALL') {
            return;
        }

        $leagueNumber = (string) $project->staffel_id;

        if ($leagueNumber === '') {
            return;
        }

        $url = $this->getLink($clubNumber, $clubPassword, $leagueNumber, $this->sisType, $xmlBase);

        try {
            $this->getSpielplan($url, $leagueNumber, $this->sisType);
        } catch (\Throwable $exception) {
            $app->enqueueMessage($exception->getMessage(), 'warning');
        }
    }

    private function getProject(int $projectId): ?object
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p.staffel_id'),
                $db->quoteName('p.sports_type_id'),
                $db->quoteName('st.name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_sports_type', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('p.sports_type_id')
            )
            ->where($db->quoteName('p.id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);

        $db->setQuery($query);

        return $db->loadObject() ?: null;
    }

    private function getLink(
        string $clubNumber,
        string $clubPassword,
        string $leagueNumber,
        int $sisType,
        string $xmlBase
    ): string {
        return sprintf(
            '%s/xmlexport/xml_dyn.aspx?user=%s&pass=%s&art=%d&auf=%s',
            $xmlBase,
            rawurlencode($clubNumber),
            rawurlencode($clubPassword),
            $sisType,
            rawurlencode($leagueNumber)
        );
    }

    private function getSpielplan(string $url, string $leagueNumber, int $sisType): ?\SimpleXMLElement
    {
        $directory = JPATH_SITE . '/components/com_sportsmanagement/sisdata';

        if (!is_dir($directory) && !Folder::create($directory)) {
            throw new \RuntimeException('SIS data directory could not be created: ' . $directory);
        }

        $file = $directory . '/sp_sis_art_' . $sisType . '_ln_' . basename($leagueNumber) . '.xml';
        $needsRefresh = !is_file($file) || (time() - (int) filemtime($file)) > 1800;

        if ($needsRefresh) {
            $response = HttpFactory::getHttp()->get($url, [], 30);
            $status = $response->getStatusCode();
            $body = (string) $response->getBody();

            if ($status < 200 || $status >= 300 || trim($body) === '') {
                throw new \RuntimeException('SIS XML download failed with HTTP status ' . $status);
            }

            $xml = new \DOMDocument('1.0', 'UTF-8');
            $previous = libxml_use_internal_errors(true);
            $loaded = $xml->loadXML($body, LIBXML_NONET);
            libxml_clear_errors();
            libxml_use_internal_errors($previous);

            if (!$loaded) {
                throw new \RuntimeException('SIS XML response is invalid.');
            }

            if ($xml->save($file) === false) {
                throw new \RuntimeException('SIS XML file could not be written: ' . $file);
            }
        }

        $result = simplexml_load_file($file, \SimpleXMLElement::class, LIBXML_NONET);

        if (!$result instanceof \SimpleXMLElement) {
            return null;
        }

        foreach ($result->Spiel as $match) {
            $number = substr((string) $match->Liga, -3);
            $date = substr((string) $match->SpielVon, 0, 10);
            $match->Date = $date;
            $match->Nummer = $number;
            $match->Datum = date('d.m.Y', strtotime($date));
            $match->vonUhrzeit = substr((string) $match->SpielVon, 11, 8);
            $match->bisUhrzeit = substr((string) $match->SpielBis, 11, 8);
        }

        return $result;
    }
}
