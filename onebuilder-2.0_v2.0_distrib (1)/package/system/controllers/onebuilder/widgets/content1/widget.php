<?php

class widgetOnebuilderContent1 extends cmsWidget { 
    
	public $is_cacheable = false;
    public function run() {
		
		$this->setWrapper('wrapper_plain');
		
        $id                = $this->getOption('id');
        $ptop              = $this->getOption('ptop');
        $pbottom           = $this->getOption('pbottom');
        $bgtype            = $this->getOption('bgtype');
        $bgcolor           = $this->getOption('bgcolor');
        $bgimage           = $this->getOption('bgimage');
        $bgfixed           = $this->getOption('bgfixed');

        $cat_id           = $this->getOption('category_id');
        $ctype_id         = $this->getOption('ctype_id');
        $dataset_id       = $this->getOption('dataset');
        $image_field      = $this->getOption('image_field');
        $image_preset     = $this->getOption('image_preset');
        $teaser_fields    = $this->getOption('teaser_field');
        $limit            = $this->getOption('limit', 10);
        $view_item        = $this->getOption('view_item');

        $title            = $this->getOption('title');
        $title_preset     = $this->getOption('title_preset');
        $desc             = $this->getOption('desc');
        $dot_color        = $this->getOption('dot_color');
        $dot_pos          = $this->getOption('dot_pos');

        $model = cmsCore::getModel('content');

        $ctype = $model->getContentType($ctype_id);
        if (!$ctype) { return false; }

		if ($cat_id){
			$category = $model->getCategory($ctype['name'], $cat_id);
		} else {
			$category = false;
		}

        if ($dataset_id){

            $dataset = $model->getContentDataset($dataset_id);

            if ($dataset){
                $model->applyDatasetFilters($dataset);
            } else {
                $dataset_id = false;
            }

        }

		if ($category){
			$model->filterCategory($ctype['name'], $category, true);
		}

        // применяем приватность
        // флаг показа только названий
        $hide_except_title = $model->applyPrivacyFilter($ctype, cmsUser::isAllowed($ctype['name'], 'view_all'));

        // Скрываем записи из скрытых родителей (приватных групп и т.п.)
        $model->enableHiddenParentsFilter();

        // выключаем формирование рейтинга в хуках
        $ctype['is_rating'] = 0;

		list($ctype, $model) = cmsEventsManager::hook('content_list_filter', array($ctype, $model));
		list($ctype, $model) = cmsEventsManager::hook("content_{$ctype['name']}_list_filter", array($ctype, $model));

        $items = $model->limit($limit)->getContentItems($ctype['name']);
        if (!$items) { return false; }

        list($ctype, $items) = cmsEventsManager::hook("content_before_list", array($ctype, $items));
        list($ctype, $items) = cmsEventsManager::hook("content_{$ctype['name']}_before_list", array($ctype, $items));

        return array(

            'id'                => $this->id,
            'ptop'              => $ptop,
            'pbottom'           => $pbottom,
            'bgtype'            => $bgtype,
            'bgcolor'           => $bgcolor,
            'bgimage'           => $bgimage,
            'bgfixed'           => $bgfixed,
		
            'widget_id'         => $this->bind_id,
            'op'                => $this->options,
            'ctype'             => $ctype,
            'image_field'       => $image_field,
            'image_preset'      => $image_preset,
            'teaser_field'      => $teaser_fields,
            'items'             => $items,
            'view_item'         => $view_item,

            'title'             => $title,
            'title_preset'      => $title_preset,
            'desc'              => $desc,
            'dot_color'         => $dot_color,
            'dot_pos'           => $dot_pos
            
			
        );

    }

}
