<?php

defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

class Xoonips_Backend extends XCube_ActionFilter
{
    public function postFilter()
    {
        $this->mController->mRoot->mDelegateManager->add('Legacy_BackendAction.GetRSSItems', array(&$this, 'getRSSItems'));
    }

    public function getRSSItems(&$items)
    {
        // check module_read permission
    $moduleHandler = xoops_getHandler('module');
        $module = $moduleHandler->getByDirname('xoonips');
        $gpermHandler = xoops_getHandler('groupperm');
        $can_read = $gpermHandler->checkRight('module_read', $module->getVar('mid'), XOOPS_GROUP_ANONYMOUS);
        if (!$can_read) {
            return;
        }

    // get all published items
    $limit = 10;
        $category = $module->getVar('name');
        $ibHandler = &xoonips_getormhandler('xoonips', 'item_basic');
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('event_type_id', ETID_CERTIFY_ITEM));
        $criteria->setGroupBy('ev.item_id');
        $criteria->setSort('ev.timestamp');
        $criteria->setOrder('DESC');
        $criteria->setLimit($limit);
        $fields = '*,MAX(ev.timestamp) AS pubdate';
        $join = new XooNIpsJoinCriteria('xoonips_event_log', 'item_id', 'item_id', 'INNER', 'ev');
        $criteria->add(new Criteria('iil.certify_state', CERTIFIED));
        $join->cascade(new XooNIpsJoinCriteria('xoonips_index_item_link', 'item_id', 'item_id', 'INNER', 'iil'));
        $criteria->add(new Criteria('idx.open_level', OL_PUBLIC));
        $join->cascade(new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'INNER', 'idx'), 'iil', true);
        $res = &$ibHandler->open($criteria, $fields, false, $join);
        while ($obj = &$ibHandler->getNext($res)) {
            $item_id = (int) $obj->get('item_id');
            $doi = $obj->get('doi');
            $title = $this->_getItemTitle($item_id, 's');
            $url = $this->_getItemUrl($item_id, $doi, 's');
            $description = $obj->get('description');
            $items[] = array(
        'pubdate' => $obj->getExtraVar('pubdate'),
        'title' => $title,
        'link' => $url,
        'guid' => $url,
        'description' => $description,
        'category' => $category,
      );
        }
        $ibHandler->close($res);
    }

    public function _getItemTitle($item_id, $fmt)
    {
        $itHandler = &xoonips_getormhandler('xoonips', 'title');
        $title = '';
        $tobjs = $itHandler->getTitles($item_id);
        foreach ($tobjs as $tobj) {
            $title .= $tobj->get('title', $fmt);
        }

        return $title;
    }

    public function _getItemUrl($item_id, $doi, $fmt)
    {
        $url = XOOPS_URL.'/modules/xoonips/detail.php?';
        $url .= ($doi != '' && XNP_CONFIG_DOI_FIELD_PARAM_NAME != '') ? XNP_CONFIG_DOI_FIELD_PARAM_NAME.'='.urlencode($doi) : 'item_id='.$item_id;
        if (isset($GLOBALS['cubeUtilMlang'])) {
            if (!empty($GLOBALS['cubeUtilMlang']->mLanguage)) {
                $url .= '&amp;'.CUBE_UTILS_ML_PARAM_NAME.'='.$GLOBALS['cubeUtilMlang']->mLanguage;
            }
        }

        return $url;
    }
}
