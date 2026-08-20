<?php
/** SportsManagement administrator image list view. */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

class sportsmanagementViewimagelist extends sportsmanagementView
{
    public function init()
    {
        $this->app->getLanguage()->load('com_media', JPATH_ADMINISTRATOR);

        if (in_array($this->getLayout(), ['default_3', 'default_4'], true)) {
            $this->setLayout('default');
        }

        $input = $this->app->getInput();
        $data  = $input->getArray();

        $this->filter_search = trim((string) ($data['filter_search'] ?? ''));
        $this->club_id       = max(0, (int) ($data['club_id'] ?? 0));
        $this->teamplayer_id = max(0, (int) ($data['teamplayer_id'] ?? 0));
        $this->player_id     = max(0, (int) ($data['player_id'] ?? 0));
        $this->folder        = trim((string) ($data['folder'] ?? ''), '/\\');
        $this->type          = (string) ($data['type'] ?? '');
        $this->fieldid       = (string) ($data['fieldid'] ?? '');
        $this->fieldname     = (string) ($data['fieldname'] ?? '');
        $this->imagelist     = !empty($data['imagelist']) ? 1 : 0;
        $this->pid           = max(0, (int) ($data['pid'] ?? 0));
        $this->mid           = max(0, (int) ($data['mid'] ?? 0));
        $this->match_id      = $this->mid;

        $path = $this->folder;

        if ($this->folder === 'projectimages' && $this->pid > 0) {
            $path .= '/' . $this->pid;
        } elseif ($this->folder === 'matchreport' && $this->mid > 0) {
            $path .= '/' . $this->mid;
        }

        $this->images     = $this->model->getFiles($path, '', $data);
        $this->state      = $this->model->getState();
        $this->pagination = $this->model->getPagination();
        $this->limit      = $this->state->get('list.limit');

        $this->document->addStyleSheet(
            Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/media-browser.css'
        );
        $this->addSelectionScript();
    }

    public function setImage($index = 0)
    {
        $this->_tmp_img = $this->images[$index] ?? new \stdClass();
    }

    private function addSelectionScript(): void
    {
        $token        = Session::getFormToken();
        $baseUrl      = Uri::root() . 'administrator/index.php?option=com_sportsmanagement&tmpl=component';
        $type         = $this->type;
        $fieldId      = $this->fieldid;
        $fieldName    = $this->fieldname;
        $folder       = $this->folder;
        $playerId     = $this->player_id;
        $clubId       = $this->club_id;
        $teamPlayerId = $this->teamplayer_id;

        $script = '(() => {'
            . 'const baseUrl=' . json_encode($baseUrl, JSON_UNESCAPED_SLASHES) . ';'
            . 'const token=' . json_encode($token) . ';'
            . 'const type=' . json_encode($type) . ';'
            . 'const fieldId=' . json_encode($fieldId) . ';'
            . 'const fieldName=' . json_encode($fieldName) . ';'
            . 'const folder=' . json_encode($folder) . ';'
            . 'const playerId=' . (int) $playerId . ';'
            . 'const clubId=' . (int) $clubId . ';'
            . 'const teamPlayerId=' . (int) $teamPlayerId . ';'
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

        $this->document->addScriptDeclaration($script);
    }
}
