<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\JlextcountryTable;
use Joomla\Archive\Archive;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Http\HttpFactory;

/** Native Joomla 5/6 administrator form model for countries. */
final class JlextcountryModel extends SportsManagementAdminModel
{
    public function getTable($type = 'jlextcountry', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'jlextcountry') === 0) {
            return new JlextcountryTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    protected function prepareSportsManagementData(array $data): array
    {
        if (array_key_exists('countrymap_mapdata', $data) && trim((string) $data['countrymap_mapdata']) === '') {
            $data['countrymap_mapdata'] = null;
        }

        if (array_key_exists('countrymap_mapinfo', $data) && trim((string) $data['countrymap_mapinfo']) === '') {
            $data['countrymap_mapinfo'] = null;
        }

        return $data;
    }

    /** Import postal-code data for the selected countries. */
    public function importplz(array $pks): bool
    {
        $app = $this->administratorApplication();
        $pks = array_values(array_unique(array_filter(array_map('intval', $pks))));

        if (!$pks) {
            return true;
        }

        $server = trim((string) ComponentHelper::getParams('com_sportsmanagement')->get('cfg_plz_server', ''));

        if ($server === '') {
            $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_COPY_PLZ_ERROR'), 'error');

            return false;
        }

        $baseDir = JPATH_SITE . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;

        if (!Folder::exists($baseDir) && !Folder::create($baseDir)) {
            $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_COPY_PLZ_ERROR'), 'error');

            return false;
        }

        $http = (new HttpFactory())->getHttp();
        $archive = new Archive();
        $db = $this->getDatabase();
        $success = true;

        foreach ($pks as $pk) {
            $table = $this->getTable();

            if (!$table->load($pk)) {
                $success = false;
                continue;
            }

            $alpha2 = strtoupper(trim((string) $table->alpha2));

            if (!preg_match('/^[A-Z]{2}$/', $alpha2)) {
                $success = false;
                $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_COPY_PLZ_ERROR'), 'error');
                continue;
            }

            $zipName = $alpha2 . '.zip';
            $zipPath = $baseDir . $zipName;
            $textPath = $baseDir . $alpha2 . '.txt';
            $url = rtrim($server, '/') . '/' . $zipName;

            try {
                $response = $http->get($url);
                $status = $response->getStatusCode();
                $body = (string) $response->getBody();

                if ($status < 200 || $status >= 300 || $body === '') {
                    throw new \RuntimeException('Postal-code download failed with HTTP status ' . $status);
                }

                if (!File::write($zipPath, $body)) {
                    throw new \RuntimeException('Unable to write postal-code archive ' . $zipName);
                }

                $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_COPY_PLZ_SUCCESS'), 'message');

                if (!$archive->extract($zipPath, $baseDir) || !File::exists($textPath)) {
                    throw new \RuntimeException('Unable to extract postal-code archive ' . $zipName);
                }

                $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_COPY_PLZ_ZIP_SUCCESS'), 'message');

                $handle = fopen($textPath, 'rb');

                if ($handle === false) {
                    throw new \RuntimeException('Unable to read postal-code data ' . basename($textPath));
                }

                $db->transactionStart();

                try {
                    while (($row = fgetcsv($handle, null, "\t")) !== false) {
                        if (!$row || count($row) < 3) {
                            continue;
                        }

                        $row = array_pad($row, 12, '');
                        $profile = (object) [
                            'country_code' => (string) $row[0],
                            'postal_code' => (string) $row[1],
                            'place_name' => (string) $row[2],
                            'admin_name1' => (string) $row[3],
                            'admin_code1' => (string) $row[4],
                            'admin_name2' => (string) $row[5],
                            'latitude' => (string) $row[9],
                            'longitude' => (string) $row[10],
                            'accuracy' => (string) $row[11],
                        ];

                        $db->insertObject('#__sportsmanagement_countries_plz', $profile);
                    }

                    $db->transactionCommit();
                } catch (\Throwable $e) {
                    $db->transactionRollback();
                    throw $e;
                } finally {
                    fclose($handle);
                }
            } catch (\Throwable $e) {
                $success = false;
                $app->enqueueMessage($e->getMessage(), 'error');
                $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_COPY_PLZ_ZIP_ERROR'), 'error');
            } finally {
                if (File::exists($zipPath)) {
                    File::delete($zipPath);
                }

                if (File::exists($textPath)) {
                    File::delete($textPath);
                }
            }
        }

        return $success;
    }
}
