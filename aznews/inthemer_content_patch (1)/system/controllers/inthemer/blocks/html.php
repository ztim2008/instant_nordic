<?php
class inthemerBlockHtml extends inthemerBlock {

	public function render($options, $childHTML = false){

        $tag = new inthemerTag('div', $this->getId(), $options);

		$tag->addClass('page-html');
		$tag->setHTML($options['html']);

		return $tag;

	}

}
