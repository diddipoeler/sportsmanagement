<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Imagelist;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Diddipoeler\Component\SportsManagement\Administrator\Model\ImagelistModel;
use Joomla\CMS\Session\Session;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Uri\Uri;

/** Native Joomla 5/6 administrator image browser view. */
final class HtmlView extends BaseHtmlView
{
    public array $images = [];
    public $state;
    public $pagination;
    public $uri;
    public string $filter_search = '';
    public int $club_id = 0;
    public int $teamplayer_id = 0;
    public int $player_id = 0;
    public string $folder = '';
    public string $type = '';
    public string $fieldid = '';
    public string $fieldname = '';
    public int $imagelist = 0;
    public int $pid = 0;
    public int $mid = 0;
    public int $match_id = 0;
    public int $limit = 0;
    public object $_tmp_img;

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $app->getLanguage()->load('com_media', JPATH_ADMINISTRATOR);

        if (in_array($this->getLayout(), ['default_3', 'default_4'], true)) {
            $this->setLayout('default');
        }

        $model = $this->getModel();

        if (!$model instanceof ImagelistModel) {
            throw new \RuntimeException('ImagelistModel is unavailable.', 500);
        }

        $data = $app->getInput()->getArray();
        $this->filter_search = trim((string) ($data['filter_search'] ?? ''));
        $this->club_id = max(0, (int) ($data['club_id'] ?? 0));
        $this->teamplayer_id = max(0, (int) ($data['teamplayer_id'] ?? 0));
        $this->player_id = max(0, (int) ($data['player_id'] ?? 0));
        $this->folder = trim((string) ($data['folder'] ?? ''), '/\\');
        $this->type = (string) ($data['type'] ?? '');
        $this->fieldid = (string) ($data['fieldid'] ?? '');
        $this->fieldname = (string) ($data['fieldname'] ?? '');
        $this->imagelist = !empty($data['imagelist']) ? 1 : 0;
        $this->pid = max(0, (int) ($data['pid'] ?? 0));
        $this->mid = max(0, (int) ($data['mid'] ?? 0));
        $this->match_id = $this->mid;

        $path = $this->folder;

        if ($this->folder === 'projectimages' && $this->pid > 0) {
            $path .= '/' . $this->pid;
        } elseif ($this->folder === 'matchreport' && $this->mid > 0) {
            $path .= '/' . $this->mid;
        }

        $this->images = $model->getFiles($path, '', $data);
        $this->state = $model->getState();
        $this->pagination = $model->getPagination();
        $this->limit = (int) $this->state->get('list.limit', 0);
        $this->uri = Uri::getInstance();

        $assets = $this->getDocument()->getWebAssetManager();
        $assets->registerAndUseStyle(
            'com_sportsmanagement.media-browser',
            'administrator/components/com_sportsmanagement/assets/css/media-browser.css',
            ['version' => 'auto']
        );
        $assets->addInlineScript($this->selectionScript());

        parent::display($tpl);
    }

    public function setImage($index = 0): void
    {
        $this->_tmp_img = $this->images[(int) $index] ?? new \stdClass();
    }

    private function selectionScript(): string
    {
        $token = Session::getFormToken();
        $baseUrl = Uri::root() . 'administrator/index.php?option=com_sportsmanagement&tmpl=component';

        return '(() => {'
            . 'const baseUrl=' . json_encode($baseUrl, JSON_UNESCAPED_SLASHES) . ';'
            . 'const token=' . json_encode($token) . ';'
            . 'const type=' . json_encode($this->type) . ';'
            . 'const fieldId=' . json_encode($this->fieldid) . ';'
            . 'const fieldName=' . json_encode($this->fieldname) . ';'
            . 'const playerId=' . $this->player_id . ';'
            . 'const clubId=' . $this->club_id . ';'
            . 'const teamPlayerId=' . $this->teamplayer_id . ';'
            . 'function closeModal(reload=false){'
                . 'if(window.parent&&window.parent.Joomla&&window.parent.Joomla.Modal){window.parent.Joomla.Modal.getCurrent().close();}'
                . 'if(reload&&window.parent){window.parent.location.reload();}'
            . '}'
            . 'async function saveSelection(task,idName,idValue,img){'
                . 'const body=new URLSearchParams();'
                . 'body.append(token,"1");body.append(idName,String(idValue));body.append("picture",img);'
                . 'const response=await fetch(baseUrl+"&task="+encodeURIComponent(task),{'
                    . 'method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"},body:body.toString()'
                . '});'
                . 'if(!response.ok){throw new Error("HTTP "+response.status);}'
                . 'await response.json();closeModal(true);'
            . '}'
            . 'window.exportToForm=async function(img){'
                . 'try{'
                    . 'if(playerId>0){await saveSelection("imagehandler.saveimageplayer","player_id",playerId,img);return;}'
                    . 'if(clubId>0){await saveSelection("imagehandler.saveimageclub","club_id",clubId,img);return;}'
                    . 'if(teamPlayerId>0){await saveSelection("imagehandler.saveimageteamplayer","teamplayer_id",teamPlayerId,img);return;}'
                    . 'const selector=window.parent&&window.parent["selectImage_"+type];'
                    . 'if(typeof selector==="function"){selector(img,img,fieldName,fieldId);closeModal(false);}'
                . '}catch(error){console.error("Image selection failed",error);}'
            . '};'
        . '})();';
    }
}
