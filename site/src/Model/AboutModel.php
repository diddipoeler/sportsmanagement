<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

final class AboutModel extends SportsManagementModel
{
    public function getAbout(): object
    {
        return (object) [
            'translations' => 'https://www.transifex.com/jsm/sportsmanagement/',
            'repository' => 'https://github.com/diddipoeler/sportsmanagement',
            'version' => '',
            'author' => '',
            'page' => 'https://sportsmanagement.fussballineuropa.de/',
            'email' => 'diddipoeler@gmx.de',
            'forum' => 'https://www.fussballineuropa.de/index.php/forum',
            'bugs' => 'https://github.com/diddipoeler/sportsmanagement/issues',
            'wiki' => 'http://smwiki.diddipoeler.de/',
            'date' => '2014-01-01',
            'developer' => 'DonClumsy (Tim Keller), SvDoldie (Hauke Prochnow), Stony (Siegfried Galun)',
            'designer' => 'DonClumsy (Tim Keller)',
            'icons' => 'Jersey Icons (Hollandsevelden.nl), Silk / Flags Icons (Mark James), Panel images (Kasi)',
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
