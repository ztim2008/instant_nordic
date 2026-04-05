<?php

class modelInstyler extends cmsModel{

    public function addSelector ($selector) {

        $selector['ordering'] = $this->getMaxSelectorOrdering() + 1;

        return $this->insert('instyler_styles', $selector);

    }

    public function updateSelector ($id, $selector) {
        unset($selector['id']);
        unset($selector['scope_type']);
        unset($selector['ordering']);
        return $this->update('instyler_styles', $id, $selector);
    }

    public function deleteSelector ($id) {

        $selector = $this->getSelector($id);

        $this->filterGt('ordering', $selector['ordering'])->decrement('instyler_styles', 'ordering');

        return $this->delete('instyler_styles', $id);

    }

    public function getSelectorsCount(){
        return $this->getCount('instyler_styles');
    }

    public function getSelectors(){
        $this->orderBy('ordering');
        return $this->get('instyler_styles', false, false);
    }

    public function getSelector($id){
        return $this->getItemById('instyler_styles', $id);
    }

    public function getSelectorByOrdering($ordering){
        return $this->getItemByField('instyler_styles', 'ordering', $ordering);
    }

    public function getMaxSelectorOrdering(){
        return intval($this->getMaxOrdering('instyler_styles'));
    }

    public function moveSelector($from, $to){

        $selector = $this->getSelectorByOrdering($from);

        if (!$selector) { return; }

        if ($from > $to){
            $this->filter("i.ordering < {$from} AND i.ordering >= {$to}")->increment('instyler_styles', 'ordering');
        }

        if ($from < $to) {
            $this->filter("i.ordering > {$from} AND i.ordering <= {$to}")->decrement('instyler_styles', 'ordering');
        }

        $this->update('instyler_styles', $selector['id'], array(
            'ordering' => $to
        ));

    }

    public function addImage ($image) {
        return $this->insert('instyler_images', $image);
    }

    public function updateImage ($id, $image) {
        return $this->update('instyler_images', $id, $image);
    }

    public function deleteImage ($id) {
        return $this->delete('instyler_images', $id);
    }

    public function getImagesCount(){
        return $this->getCount('instyler_images');
    }

    public function getImages(){
        return $this->get('instyler_images', false, false);
    }

    public function getImage($id){
        return $this->getItemById('instyler_images', $id);
    }

}
