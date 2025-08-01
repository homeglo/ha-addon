<?php
namespace app\widgets;

use hoaaah\sbadmin2\widgets\Menu as BaseMenu;
use yii\helpers\Url;

/**
 * Custom Menu widget that keeps accordions always expanded and fixes URL generation
 */
class Menu extends BaseMenu
{
    /**
     * Override to fix URL generation for ingress mode
     */
    protected function renderItem($item){
        if($this->setVisibility($item) === false) return '';

        if(!isset($item['type'])) $item['type'] = 'menu';

        if($item['type'] === 'divider') return $this->dividerTemplate;

        if($item['type'] === 'sidebar') return strtr($this->sidebarHeadingTemplate, ['{label}' => $item['label']]);

        if($item['type'] === 'menu')
        {
            // generate link - use relative URL
            $url = Url::to($item['url']);
            $label = $item['label'];
            $icon = $item['icon'] ?? $this->iconDefault;
            $linkOptions = '';
            if(isset($item['linkOptions']))
            {
                foreach ($item['linkOptions'] as $key => $value) {
                    $linkOptions .= "{$key}=\"{$value}\"";
                }
            }
            $link = strtr($this->linkTemplate, ['{url}' => $url, '{label}' => $label, '{icon}' => $icon, '{linkOptions}' => $linkOptions]);

            // generate nav-item
            $liClass = $this->liClass;
            if($this->isActive($item['url'])) $liClass .= " {$this->activeClass}";

            return strtr($this->menuTemplate, ['{liClass}' => $liClass, '{link}' => $link]);
        }
    }
    
    /**
     * Override to fix URL generation for submenu items
     */
    protected function renderSubItem($item){
        $subMenuClass = $this->subMenuLinkClass;
        // Use relative URL
        $url = Url::to($item['url']);
        $icon = $item['icon'] ?? $this->iconDefault;
        $label = $item['label'];
        $linkOptions = '';
        if(isset($item['linkOptions']))
        {
            foreach ($item['linkOptions'] as $key => $value) {
                $linkOptions .= "{$key}=\"{$value}\"";
            }
        }
        if($this->isActive($item['url'])) $subMenuClass .= " {$this->activeClass}";

        if(!$this->setVisibility($item)) return '';

        return strtr($this->subMenuLinkTemplate, ['{subMenuClass}' => $subMenuClass, '{url}' => $url, '{icon}' => $icon, '{label}' => $label, '{linkOptions}' => $linkOptions]);
    }
    
    /**
     * Override to always show accordions as expanded
     */
    protected function renderItems($items, $key){
        if($this->setVisibility($items) === false) return '';

        $label = $items['label'];
        $ulId = $this->ulId;
        $subMenuTitle = $items['subMenuTitle'] ?? '';
        $header = $subMenuTitle;
        if(isset($items['subMenuTitle'])) $header = strtr($this->subMenuHeaderTemplate, ['{subMenuTitle}' => $subMenuTitle]);
        $icon = $items['icon'] ?? $this->iconDefault;

        $subMenuClass = $this->liClass;
        $active = false;
        $link = '';
        
        // Always show accordions as expanded
        $collapseShow = 'show';
        $collapseArrow = '';

        foreach ($items['items'] as $item) {
            $isActiveThisItem = $this->isActive($item['url']);
            if($isActiveThisItem) $active = true;
            $link .= $this->renderSubItem($item);
        }

        if($active === true)
        {
            $subMenuClass .= " {$this->activeClass}";
        }

        return strtr($this->subMenuTemplate, ['{liClass}' => $subMenuClass, '{key}' => $key, '{label}' => $label, '{active-show}' => $collapseShow, '{collapsed-arrow}' => $collapseArrow,
            '{ulId}' => $ulId, '{header}' => $header, '{link}' => $link, '{icon}' => $icon]);
    }
}