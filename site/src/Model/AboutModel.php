<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\Registry\Registry;

final class AboutModel extends SportsManagementModel
{
    public function getAbout(): object
    {
        $version = '';

        try {
            $extension = ExtensionHelper::getExtensionRecord('com_sportsmanagement', 'component');
            if ($extension && !empty($extension->manifest_cache)) {
                $manifest = new Registry();
                $manifest->loadString((string) $extension->manifest_cache, 'JSON');
                $version = (string) $manifest->get('version', '');
            }
        } catch (\Throwable) {
            $version = '';
        }

        return (object) [
            'translations' => '<a href="https://www.transifex.com/jsm/sportsmanagement/">https://www.transifex.com/jsm/sportsmanagement/</a>',
            'repository' => '<a href="https://github.com/diddipoeler/sportsmanagement">https://github.com/diddipoeler/sportsmanagement</a>',
            'version' => $version,
            'author' => '',
            'page' => 'https://sportsmanagement.fussballineuropa.de/',
            'email' => 'diddipoeler@gmx.de',
            'forum' => 'https://www.fussballineuropa.de/index.php/forum',
            'bugs' => 'https://github.com/diddipoeler/sportsmanagement/issues',
            'wiki' => 'http://smwiki.diddipoeler.de/',
            'date' => '2014-01-01',
            'developer' => 'DonClumsy (Tim Keller), SvDoldie (Hauke Prochnow), Stony (Siegfried Galun) ',
            'designer' => 'DonClumsy (Tim Keller), ',
            'icons' => '<a href="http://www.hollandsevelden.nl/iconset/" target="_blank">Jersey Icons</a> (Hollandsevelden.nl), <a href="http://www.famfamfam.com/lab/icons/silk/" target="_blank">Silk / Flags Icons</a> (Mark James), Panel images (Kasi)',
            'flash' => '',
            'graphic_library' => '',
            'phpthumb' => '',
            'github' => 'https://github.com/diddipoeler/sportsmanagement',
            'diddipoelerpage' => 'https://www.fussballineuropa.de',
            'diddipoeleremail' => 'diddipoeler@gmx.de',
            'diddipoelerforum' => 'https://www.fussballineuropa.de/index.php/forum/sports-management',
        ];
    }
}
