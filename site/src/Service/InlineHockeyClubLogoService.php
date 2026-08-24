<?php
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

/** Synchronise missing Inline Hockey club logos without overwriting custom logos. */
final class InlineHockeyClubLogoService
{
    private const LOGO_DIRECTORY = 'images/com_sportsmanagement/database/clubs/large';
    private const IMAGE_EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif', 'webp'];

    public function __construct(
        private readonly DatabaseInterface $db,
        private readonly InlineHockeyApiClient $api,
        private readonly InlineHockeyProjectService $projects
    ) {
    }

    public function syncProjectLogos(
        int $projectId,
        string $matchLink = '',
        string $username = '',
        string $password = ''
    ): int {
        if ($projectId <= 0) {
            return 0;
        }

        $matchLink = trim($matchLink);

        if ($matchLink === '') {
            $matchLink = $this->projects->getMatchLink($projectId);
        }

        if ($matchLink === '') {
            return 0;
        }

        $firstPage = $this->api->fetchJson($matchLink, $username, $password);
        $pages = max(1, (int) ($firstPage->pages ?? 1));
        $seen = [];
        $synced = 0;

        for ($page = 1; $page <= $pages; $page++) {
            $payload = $page === 1
                ? $firstPage
                : $this->api->fetchJson($this->api->pageUrl($matchLink, $page), $username, $password);
            $schedule = $payload->_embedded->schedule ?? [];

            if (!is_iterable($schedule)) {
                continue;
            }

            foreach ($schedule as $externalMatch) {
                if (!is_object($externalMatch)) {
                    continue;
                }

                foreach ([$externalMatch->home_team ?? null, $externalMatch->away_team ?? null] as $side) {
                    $club = is_object($side) && is_object($side->club ?? null) ? $side->club : null;
                    $clubId = (int) ($club->id ?? 0);

                    if ($clubId <= 0 || isset($seen[$clubId])) {
                        continue;
                    }

                    $seen[$clubId] = true;
                    $links = is_object($club->_links ?? null) ? $club->_links : null;
                    $logo = $links && is_object($links->logo ?? null) ? $links->logo : null;
                    $logoHref = trim((string) ($logo->href ?? ''));

                    if ($logoHref === '') {
                        continue;
                    }

                    try {
                        if ($this->syncClubLogo($clubId, $logoHref, $username, $password)) {
                            $synced++;
                        }
                    } catch (\Throwable $exception) {
                        Log::add($exception->getMessage(), Log::WARNING, 'jsmerror');
                    }
                }
            }
        }

        return $synced;
    }

    private function syncClubLogo(
        int $clubId,
        string $logoHref,
        string $username,
        string $password
    ): bool {
        $currentLogo = $this->currentLogo($clubId);

        if ($currentLogo !== '' && !str_contains($currentLogo, '/placeholders/')) {
            return false;
        }

        $path = (string) (parse_url($logoHref, PHP_URL_PATH) ?: '');
        $fileName = File::makeSafe(basename(rawurldecode($path)));
        $extension = strtolower((string) File::getExt($fileName));

        if ($fileName === '' || !in_array($extension, self::IMAGE_EXTENSIONS, true)) {
            return false;
        }

        $directory = JPATH_SITE . '/' . self::LOGO_DIRECTORY;

        if (!is_dir($directory) && !Folder::create($directory)) {
            throw new \RuntimeException('Inline-Hockey club logo directory could not be created.');
        }

        $relativePath = self::LOGO_DIRECTORY . '/' . $clubId . '_' . $fileName;
        $destination = JPATH_SITE . '/' . $relativePath;

        if (!File::exists($destination) || (int) @filesize($destination) <= 0) {
            $image = $this->api->fetchIshdImage($logoHref, $username, $password);

            if (!File::write($destination, $image)) {
                throw new \RuntimeException('Inline-Hockey club logo could not be written: ' . $fileName);
            }
        }

        $club = (object) [
            'id' => $clubId,
            'logo_big' => $relativePath,
        ];
        $this->db->updateObject('#__sportsmanagement_club', $club, 'id');

        return true;
    }

    private function currentLogo(int $clubId): string
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('logo_big'))
            ->from($this->db->quoteName('#__sportsmanagement_club'))
            ->where($this->db->quoteName('id') . ' = :clubId')
            ->bind(':clubId', $clubId, ParameterType::INTEGER);
        $this->db->setQuery($query, 0, 1);

        return trim((string) $this->db->loadResult());
    }
}
