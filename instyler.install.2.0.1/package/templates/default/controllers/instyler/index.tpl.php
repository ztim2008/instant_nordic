<?php $config = cmsConfig::getInstance(); ?>
<!doctype html>
<html>
    <head>
        <title><?php echo LANG_INSTYLER_CONTROLLER; ?> - <?php echo $config->sitename; ?></title>
		<?php $this->addMainCSS("templates/default/controllers/instyler/styles.css"); ?>
        <?php $this->addMainCSS("templates/default/controllers/instyler/libs/font/css/font-awesome.css"); ?>
        <?php $this->addMainCSS("templates/default/controllers/instyler/libs/jquery/jquery-ui.css"); ?>
        <?php $this->addMainCSS("templates/default/controllers/instyler/libs/code/codemirror.css"); ?>
        <?php $this->addMainJS("templates/default/controllers/instyler/libs/jquery/jquery.js"); ?>
        <?php $this->addMainJS("templates/default/controllers/instyler/libs/jquery/jquery-ui.js"); ?>
        <?php $this->addMainJS("templates/default/controllers/instyler/libs/color/colors.js"); ?>
        <?php $this->addMainJS("templates/default/controllers/instyler/libs/color/picker.js"); ?>
        <?php $this->addMainJS("templates/default/controllers/instyler/libs/jquery.fileupload.js"); ?>
        <?php $this->addMainJS("templates/default/controllers/instyler/libs/pages/pages.js"); ?>
        <?php $this->addMainJS("templates/default/controllers/instyler/libs/code/codemirror.js"); ?>
        <?php $this->addMainJS("templates/default/controllers/instyler/libs/code/css.js"); ?>
        <?php $this->addMainJS("templates/default/controllers/instyler/js/fields.js"); ?>
        <?php $this->addMainJS("templates/default/controllers/instyler/js/handlers.js"); ?>
        <?php $this->addMainJS("templates/default/controllers/instyler/js/list.js"); ?>
        <?php $this->addMainJS("templates/default/controllers/instyler/js/premade.js"); ?>
        <?php $this->addMainJS("templates/default/controllers/instyler/js/picker.js"); ?>
        <?php $this->addMainJS("templates/default/controllers/instyler/js/editor.js"); ?>
        <?php $this->addMainJS("templates/default/controllers/instyler/js/instyler.js"); ?>
        <?php $this->addMainJS("templates/default/controllers/instyler/js/settings.js"); ?>
        <?php $this->head(); ?>
    </head>
    <body>

		<div id="page-wrap">
			<iframe name="page" id="page-frame" src="" frameborder="0"></iframe>
            <div id="page-loading" class="loading"><i class="fa fa-gear fa-spin"></i></div>
		</div>

        <div id="instyler-panel">
            <header>
                <h3>
                    <?php echo LANG_INSTYLER_CONTROLLER; ?>
                    <span>Pro</span>
                </h3>
                <div class="toolbuttons">
                    <a href="#" class="settings-button" title="<?php echo LANG_OPTIONS; ?>"><i class="fa fa-gear"></i></a>
                    <a href="#" class="close-button"><i class="fa fa-times"></i></a>
                </div>
            </header>
            <div id="body">

                <div class="pane" id="pane-settings">

					<div class="bar">
                        <label><?php echo LANG_INSTYLER_SETTINGS; ?></label>
                    </div>

                    <ul class="fields">

						<li class="field fixed">
							<label><?php echo LANG_INSTYLER_EXPORT_ACTIVE; ?></label>
							<div class="value">
								<a href="#" class="modal-link" id="selector-export"><?php echo LANG_INSTYLER_EXPORT_SHOW_CSS; ?></a>
							</div>
						</li>

						<li class="field fixed">
							<label><?php echo LANG_INSTYLER_EXPORT_DUMP; ?></label>
							<div class="value">
								<i class="fa fa-download"></i> <a href="<?php echo href_to('instyler', 'export'); ?>" target="_blank" id="selectors-dump"><?php echo LANG_DOWNLOAD; ?></a>
							</div>
						</li>

						<li class="field fixed">
							<label><?php echo LANG_INSTYLER_EXPORT_IMAGES; ?></label>
							<div class="value">
								<i class="fa fa-download"></i> <a href="<?php echo href_to('instyler', 'export_images'); ?>" target="_blank" id="images-dump"><?php echo LANG_DOWNLOAD; ?></a>
							</div>
						</li>

						<li class="field fixed">
							<label><?php echo LANG_INSTYLER_IMPORT_DUMP; ?></label>
							<div class="value">
								<input type="file" class="input-file" id="import-upload" name="file" data-url="<?php echo href_to('instyler', 'import'); ?>">
							</div>
						</li>

					</ul>

                    <button class="btn btn-wide btn-gray" id="btn-settings-close"><i class="fa fa-times"></i> <?php echo LANG_CLOSE; ?></button>
                </div>


                <div class="pane" id="pane-home">


                    <div id="scopes" class="tabbed-view small">
                        <ul class="tabs">
                            <li class="active" data-target="0"><?php echo LANG_INSTYLER_SELECTOR_SCOPE_TYPE_ACTIVE; ?></li>
                            <li data-target="1"><?php echo LANG_INSTYLER_SELECTOR_SCOPE_TYPE_LOCAL; ?></li>
                            <li data-target="2"><?php echo LANG_INSTYLER_SELECTOR_SCOPE_TYPE_GLOBAL; ?></li>
                            <li data-target="100"><?php echo LANG_ALL; ?></li>
                        </ul>
                    </div>

                    <div id="devices" class="tabbed-view">
                        <ul class="tabs">
                            <?php foreach($devices as $type=>$device){ ?>
                                <li class="device-<?php echo $type; ?><?php if ($type=='desktop'){ ?> active<?php } ?>" data-type="<?php echo $type; ?>" data-target="<?php echo $device['width']; ?>" data-icon="<?php echo $device['icon']; ?>" title="<?php echo $device['title']; ?>">
                                    <i class="fa fa-<?php echo $device['icon']; ?>"></i>
                                    <span class="dot"><i class="fa fa-caret-up"></i></span>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>


                    <div id="title-filter">
                        <input type="text" class="input" id="input-title-filter">
                        <span class="cancel">
                            <i class="fa fa-times"></i>
                        </span>
                        <span class="icon">
                            <i class="fa fa-search"></i>
                        </span>
                    </div>

                    <div class="no-selectors"><?php echo LANG_INSTYLER_NO_SELECTORS; ?></div>

                    <ul class="selectors-list"></ul>

                    <button class="btn btn-wide btn-green" id="btn-add-selector"><i class="fa fa-plus"></i> <?php echo LANG_INSTYLER_ADD_SELECTOR; ?></button>
                </div>

                <div class="pane" id="pane-choose">
					<div class="picker-hint"><?php echo LANG_INSTYLER_PICKER_HINT; ?></div>
                    <ul id="picker-options-list">
                        <li>
							<i class="number">1</i> <span><?php echo LANG_INSTYLER_CLICK_PICK; ?></span>
						</li>
						<li class="or pick-premade"><?php echo LANG_INSTYLER_PICK_OR; ?></li>
                        <li class="pick-premade">
							<i class="number">2</i> <span>
								<a href="#" id="btn-pick-premade" class="modal-link"><?php echo LANG_INSTYLER_THEME_PICK; ?></a></span>
						</li>
						<li class="or"><?php echo LANG_INSTYLER_PICK_OR; ?></li>
                        <li class="pick-custom">
							<i class="number">3</i> <span><?php echo LANG_INSTYLER_CUSTOM_PICK; ?></span>
							<textarea class="input" id="input-path"></textarea>
						</li>
                    </ul>
                    <div class="btn-group btn-group-2">
                        <button class="btn" id="btn-picker-manual"><i class="fa fa-check"></i> <?php echo LANG_CONTINUE; ?></button>
                        <button class="btn btn-gray btn-picker-cancel"><i class="fa fa-times"></i> <?php echo LANG_CANCEL; ?></button>
                    </div>
                </div>

                <div class="pane" id="pane-premade">
					<div class="picker-hint"><?php echo LANG_INSTYLER_PICKER_HINT; ?></div>
                    <ul id="theme-elements-list"></ul>
                    <div class="btn-group btn-group-2">
                        <button class="btn" id="btn-premade-select"><i class="fa fa-check"></i> <?php echo LANG_CONTINUE; ?></button>
                        <button class="btn btn-gray" id="btn-premade-cancel"><i class="fa fa-times"></i> <?php echo LANG_CANCEL; ?></button>
                    </div>
                </div>

                <div class="pane" id="pane-selectors">
                    <div class="bar">
                        <label><?php echo LANG_INSTYLER_SELECTOR_TITLE; ?></label>
                        <input type="text" class="input" id="input-picker-title">
                        <div id="picker-title">
                            <i class="fa fa-hashtag"></i> <span></span>
                        </div>
                    </div>
                    <div class="btn-group btn-group-5">
                        <button class="btn btn-gray" id="btn-dom-up" title="<?php echo LANG_INSTYLER_SELECTION_UP; ?>"><i class="fa fa-search-plus"></i></button>
                        <button class="btn btn-gray" id="btn-dom-down" title="<?php echo LANG_INSTYLER_SELECTION_DOWN; ?>"><i class="fa fa-search-minus"></i></button>
                        <button class="btn btn-gray" id="btn-dom-similar" title="<?php echo LANG_INSTYLER_SELECTION_SIMILAR; ?>"><i class="fa fa-plus-circle"></i></button>
                        <button class="btn btn-gray" id="btn-dom-parent" title="<?php echo LANG_INSTYLER_SELECTION_PARENT; ?>"><i class="fa fa-object-group"></i></button>
                        <button class="btn btn-gray" id="btn-dom-strict" title="<?php echo LANG_INSTYLER_SELECTION_STRICT; ?>"><i class="fa fa-chevron-right"></i></button>
                    </div>
                    <ul></ul>
                    <div class="btn-group btn-group-2">
                        <button class="btn" id="btn-picker-select"><i class="fa fa-check"></i> <?php echo LANG_SAVE; ?></button>
                        <button class="btn btn-gray btn-picker-cancel"><i class="fa fa-times"></i> <?php echo LANG_CANCEL; ?></button>
                    </div>
                </div>


                <div class="pane" id="pane-edit-css">
                    <div class="bar"><?php echo LANG_INSTYLER_EDIT_CSS; ?></div>
                    <div class="states tabbed-view small">
                        <ul class="tabs">
                            <li class="active" data-target="base"><?php echo LANG_INSTYLER_CSS_STATE_BASE; ?></li>
                            <li data-target="hover"><?php echo LANG_INSTYLER_CSS_STATE_HOVER; ?></li>
                            <li data-target="active"><?php echo LANG_INSTYLER_CSS_STATE_ACTIVE; ?></li>
                        </ul>
                    </div>
                    <div class="wrap">
                        <div class="css-code"><span id="css-path-start"></span> {</div>
                        <div id="css-editor-wrap">
                            <textarea class="input" id="css-editor"></textarea>
                        </div>
                        <div class="css-code">}</div>
                    </div>
                    <div class="btn-group btn-group-2">
                        <button class="btn" id="btn-edit-css-save"><i class="fa fa-check"></i> <?php echo LANG_SAVE; ?></button>
                        <button class="btn btn-gray" id="btn-edit-css-cancel"><i class="fa fa-times"></i> <?php echo LANG_CANCEL; ?></button>
                    </div>
                </div>


                <div class="pane" id="pane-editor">
                    <div class="bar">
                        <div id="selector-title">
                            <i class="fa fa-hashtag"></i>
                            <input type="text" class="input input-inline" id="input-editor-title" value="">
                        </div>
                        <div class="tools">
                            <button class="btn-sm" id="btn-show-selector" title="<?php echo LANG_INSTYLER_SELECTOR_SHOW; ?>"><i class="fa fa-eye"></i></button>
                        </div>
                    </div>

                    <div class="states tabbed-view small">
                        <ul class="tabs">
                            <li class="active" data-target="base"><?php echo LANG_INSTYLER_CSS_STATE_BASE; ?></li>
                            <li data-target="hover"><?php echo LANG_INSTYLER_CSS_STATE_HOVER; ?></li>
                            <li data-target="active"><?php echo LANG_INSTYLER_CSS_STATE_ACTIVE; ?></li>
                        </ul>
                    </div>

                    <div id="properties" class="tabbed-view">
                        <ul class="tabs">
                            <li data-target="settings" class="active" title="<?php echo LANG_INSTYLER_SELECTOR_SETTINGS; ?>"><i class="fa fa-gear"></i></li>
                            <li data-target="text" title="<?php echo LANG_INSTYLER_CSS_GROUP_TEXT; ?>"><i class="fa fa-font"></i></li>
                            <li data-target="bg" title="<?php echo LANG_INSTYLER_CSS_GROUP_BACKGROUND; ?>"><i class="fa fa-picture-o"></i></li>
                            <li data-target="size" title="<?php echo LANG_INSTYLER_CSS_GROUP_SIZE; ?>"><i class="fa fa-object-ungroup"></i></li>
                            <li data-target="padding" title="<?php echo LANG_INSTYLER_CSS_GROUP_PADDINGS; ?>"><i class="fa fa-arrows"></i></li>
                            <li data-target="border" title="<?php echo LANG_INSTYLER_CSS_GROUP_BORDER; ?>"><i class="fa fa-bars"></i></li>
                            <li data-target="display" title="<?php echo LANG_INSTYLER_CSS_GROUP_DISPLAY; ?>"><i class="fa fa-paint-brush"></i></li>
                            <li class="tab-used" data-target="used" title="<?php echo LANG_INSTYLER_CSS_SHOW_USED; ?>"><i class="fa fa-pencil"></i></li>
                        </ul>
                        <ul class="fields">

                            <li class="tab-settings tab field fixed">
                                <label><?php echo LANG_INSTYLER_SELECTOR; ?></label>
                                <div class="value">
                                    <a href="#" class="modal-link" id="selector-path"></a>
                                </div>
                            </li>


                            <li class="tab-settings tab field fixed">
                                <label><?php echo LANG_INSTYLER_SELECTOR_SCOPE; ?></label>
                                <div class="value">
                                    <a href="#" class="modal-link" id="selector-scope"><?php echo LANG_INSTYLER_SELECTOR_SCOPE_GLOBAL; ?></a>
                                </div>
                            </li>


                            <li class="tab-settings tab field fixed">
                                <label><?php echo LANG_INSTYLER_SELECTOR_PRIORITY; ?></label>
                                <div class="value">
                                    <label class="input-checkbox">
                                        <input type="checkbox" id="selector-important" value="1"> <?php echo LANG_INSTYLER_SELECTOR_PRIORITY_IMPORTANT; ?>
                                    </label>
                                </div>
                            </li>


                            <li class="tab-settings tab field fixed" id="custom-css-field">
                                <label><?php echo LANG_INSTYLER_SELECTOR_CUSTOM_CSS; ?></label>
                                <div class="value">
                                    <a href="#" class="modal-link" id="selector-edit-css"><?php echo LANG_INSTYLER_SELECTOR_CUSTOM_CSS_EDIT; ?></a>
                                </div>
                            </li>

                            <li class="tab-settings tab field fixed">
                                <label><?php echo LANG_INSTYLER_EXPORT; ?></label>
                                <div class="value">
                                    <a href="#" class="modal-link" id="selector-export"><?php echo LANG_INSTYLER_EXPORT_SHOW_CSS; ?></a>
                                </div>
                            </li>


                        </ul>
                    </div>

                    <div class="btn-group btn-group-2">
                        <button class="btn" id="btn-editor-save"><i class="fa fa-check"></i> <?php echo LANG_SAVE; ?></button>
                        <button class="btn btn-gray" id="btn-editor-cancel"><i class="fa fa-times"></i> <?php echo LANG_CANCEL; ?></button>
                    </div>

                </div>

                <div id="loading"><i class="fa fa-gear fa-spin"></i></div>

            </div>
        </div>

        <div id="window-images" class="modal-window" title="<?php echo LANG_INSTYLER_IMAGE_SELECT; ?>">
            <div class="toolbar">
                <div id="pagination"></div>
            </div>
            <div class="images-view">
                <ul class="images-list"></ul>
                <div class="no-images"><?php echo LANG_INSTYLER_IMAGES_NONE; ?></div>
                <div id="image-loading" class="loading"><i class="fa fa-gear fa-spin"></i></div>
            </div>
            <input id="fileupload" type="file" name="file" data-url="<?php echo href_to('instyler', 'upload'); ?>">
        </div>


        <div id="window-scope-select" class="modal-window" title="<?php echo LANG_INSTYLER_SELECTOR_SCOPE; ?>">

            <div class="option">
                <label class="input-checkbox">
                    <input type="radio" name="scope" value="1"> <?php echo LANG_INSTYLER_SELECTOR_SCOPE_GLOBAL; ?>
                </label>
            </div>
            <div class="option">
                <label class="input-checkbox">
                    <input type="radio" name="scope" value="2"> <?php echo LANG_INSTYLER_SELECTOR_SCOPE_HOME; ?>
                </label>
            </div>
            <div class="option">
                <label class="input-checkbox">
                    <input type="radio" name="scope" value="3"> <?php echo LANG_INSTYLER_SELECTOR_SCOPE_CUSTOM; ?>:
                </label>
                <div id="masks">
                    <div class="mask">
                        <div class="title"><?php echo LANG_INSTYLER_SELECTOR_SCOPE_MASK_POS; ?></div>
                        <textarea id="mask-pos" class="input"></textarea>
						<div class="hint-set">
							<a class="modal-link add-current" data-add="current"><?php echo LANG_INSTYLER_SELECTOR_SCOPE_MASK_SET_CURRENT; ?></a>
							<a class="modal-link add-nested" data-add="nested"><?php echo LANG_INSTYLER_SELECTOR_SCOPE_MASK_SET_CURRENT_ALL; ?></a>
							<a class="modal-link add-all" data-add="all"><?php echo LANG_ALL; ?></a>
						</div>
                    </div>
                    <div class="mask">
                        <div class="title"><?php echo LANG_INSTYLER_SELECTOR_SCOPE_MASK_NEG; ?></div>
                        <textarea id="mask-neg" class="input"></textarea>
						<div class="hint-set">
							<a class="modal-link add-current" data-add="current"><?php echo LANG_INSTYLER_SELECTOR_SCOPE_MASK_SET_CURRENT; ?></a>
							<a class="modal-link add-nested" data-add="nested"><?php echo LANG_INSTYLER_SELECTOR_SCOPE_MASK_SET_CURRENT_ALL; ?></a>
							<a class="modal-link add-all" data-add="all"><?php echo LANG_ALL; ?></a>
						</div>
                    </div>
                    <div class="hint"><?php echo LANG_INSTYLER_SELECTOR_SCOPE_MASK_HINT; ?></div>
                </div>
            </div>

        </div>

        <div id="window-export-css" class="modal-window" title="<?php echo LANG_INSTYLER_EXPORT; ?>">
            <pre class="code"></pre>
        </div>


        <script>
            var lang = {
                save: '<?php echo LANG_SAVE; ?>',
                cancel: '<?php echo LANG_CANCEL; ?>',
                close: '<?php echo LANG_CLOSE; ?>',
                select: '<?php echo LANG_SELECT; ?>',
                upload: '<?php echo LANG_INSTYLER_IMAGE_UPLOAD; ?>',
                delete_selector: '<?php echo LANG_INSTYLER_DELETE_SELECTOR; ?>',
                delete_image: '<?php echo LANG_INSTYLER_IMAGE_DELETE_CONFIRM; ?>',
                copy_label: '<?php echo LANG_INSTYLER_SELECTOR_COPY_LABEL; ?>',
                delete: '<?php echo LANG_DELETE; ?>',
                toggle: '<?php echo LANG_INSTYLER_SELECTOR_TOGGLE; ?>',
                clone: '<?php echo LANG_INSTYLER_SELECTOR_COPY; ?>',

                scopes: {
                    1: '<?php echo LANG_INSTYLER_SELECTOR_SCOPE_GLOBAL; ?>',
                    2: '<?php echo LANG_INSTYLER_SELECTOR_SCOPE_HOME; ?>',
                    3: '<?php echo LANG_INSTYLER_SELECTOR_SCOPE_CUSTOM; ?>',
                },

                styleNames: {
                    'color': '<?php echo LANG_INSTYLER_CSS_TEXT_COLOR; ?>',
                    'font-size': '<?php echo LANG_INSTYLER_CSS_FONT_SIZE; ?>',
                    'font-weight': '<?php echo LANG_INSTYLER_CSS_FONT_WEIGHT; ?>',
                    'font-style': '<?php echo LANG_INSTYLER_CSS_FONT_STYLE; ?>',
                    'text-decoration': '<?php echo LANG_INSTYLER_CSS_TEXT_DECORATION; ?>',
                    'text-transform': '<?php echo LANG_INSTYLER_CSS_TEXT_TRANSFORM; ?>',
                    'text-align': '<?php echo LANG_INSTYLER_CSS_TEXT_ALIGN; ?>',
                    'line-height': '<?php echo LANG_INSTYLER_CSS_LINE_HEIGHT; ?>',
                    'text-indent': '<?php echo LANG_INSTYLER_CSS_TEXT_INDENT; ?>',
                    'word-spacing': '<?php echo LANG_INSTYLER_CSS_WORD_SPACING; ?>',
                    'letter-spacing': '<?php echo LANG_INSTYLER_CSS_LETTER_SPACING; ?>',
                    'text-shadow': '<?php echo LANG_INSTYLER_CSS_TEXT_SHADOW; ?>',
                    'background-color': '<?php echo LANG_INSTYLER_CSS_BG_COLOR; ?>',
                    'background-image': '<?php echo LANG_INSTYLER_CSS_BG_IMAGE; ?>',
                    'background-repeat': '<?php echo LANG_INSTYLER_CSS_BG_REPEAT; ?>',
                    'background-position-x': '<?php echo LANG_INSTYLER_CSS_BG_POSITION_X; ?>',
                    'background-position-y': '<?php echo LANG_INSTYLER_CSS_BG_POSITION_Y; ?>',
                    'background-attachment': '<?php echo LANG_INSTYLER_CSS_BG_ATTACHMENT; ?>',
                    'background-size': '<?php echo LANG_INSTYLER_CSS_BG_SIZE; ?>',
                    'width': '<?php echo LANG_INSTYLER_CSS_WIDTH; ?>',
                    'min-width': '<?php echo LANG_INSTYLER_CSS_MIN_WIDTH; ?>',
                    'max-width': '<?php echo LANG_INSTYLER_CSS_MAX_WIDTH; ?>',
                    'height': '<?php echo LANG_INSTYLER_CSS_HEIGHT; ?>',
                    'min-height': '<?php echo LANG_INSTYLER_CSS_MIN_HEIGHT; ?>',
                    'max-height': '<?php echo LANG_INSTYLER_CSS_MAX_HEIGHT; ?>',
                    'position': '<?php echo LANG_INSTYLER_CSS_POSITION; ?>',
                    'left': '<?php echo LANG_INSTYLER_CSS_LEFT; ?>',
                    'right': '<?php echo LANG_INSTYLER_CSS_RIGHT; ?>',
                    'top': '<?php echo LANG_INSTYLER_CSS_TOP; ?>',
                    'bottom': '<?php echo LANG_INSTYLER_CSS_BOTTOM; ?>',
                    'z-index': '<?php echo LANG_INSTYLER_CSS_Z_INDEX; ?>',
                    'padding': '<?php echo LANG_INSTYLER_CSS_PADDING; ?>',
                    'padding-top': '<?php echo LANG_INSTYLER_CSS_PADDING_TOP; ?>',
                    'padding-right': '<?php echo LANG_INSTYLER_CSS_PADDING_RIGHT; ?>',
                    'padding-bottom': '<?php echo LANG_INSTYLER_CSS_PADDING_BOTTOM; ?>',
                    'padding-left': '<?php echo LANG_INSTYLER_CSS_PADDING_LEFT; ?>',
                    'margin': '<?php echo LANG_INSTYLER_CSS_MARGIN; ?>',
                    'margin-top': '<?php echo LANG_INSTYLER_CSS_MARGIN_TOP; ?>',
                    'margin-right': '<?php echo LANG_INSTYLER_CSS_MARGIN_RIGHT; ?>',
                    'margin-bottom': '<?php echo LANG_INSTYLER_CSS_MARGIN_BOTTOM; ?>',
                    'margin-left': '<?php echo LANG_INSTYLER_CSS_MARGIN_LEFT; ?>',
                    'border-width': '<?php echo LANG_INSTYLER_CSS_BORDER_WIDTH; ?>',
                    'border-top-width': '<?php echo LANG_INSTYLER_CSS_BORDER_TOP_WIDTH; ?>',
                    'border-bottom-width': '<?php echo LANG_INSTYLER_CSS_BORDER_BOTTOM_WIDTH; ?>',
                    'border-left-width': '<?php echo LANG_INSTYLER_CSS_BORDER_LEFT_WIDTH; ?>',
                    'border-right-width': '<?php echo LANG_INSTYLER_CSS_BORDER_RIGHT_WIDTH; ?>',
                    'border-color': '<?php echo LANG_INSTYLER_CSS_BORDER_COLOR; ?>',
                    'border-style': '<?php echo LANG_INSTYLER_CSS_BORDER_STYLE; ?>',
                    'border-radius': '<?php echo LANG_INSTYLER_CSS_BORDER_RADIUS; ?>',
                    'border-top-left-radius': '<?php echo LANG_INSTYLER_CSS_BORDER_TL_RADIUS; ?>',
                    'border-top-right-radius': '<?php echo LANG_INSTYLER_CSS_BORDER_TR_RADIUS; ?>',
                    'border-bottom-left-radius': '<?php echo LANG_INSTYLER_CSS_BORDER_BL_RADIUS; ?>',
                    'border-bottom-right-radius': '<?php echo LANG_INSTYLER_CSS_BORDER_BR_RADIUS; ?>',
                    'box-shadow': '<?php echo LANG_INSTYLER_CSS_BOX_SHADOW; ?>',
                    'display': '<?php echo LANG_INSTYLER_CSS_DISPLAY; ?>',
                    'visibility': '<?php echo LANG_INSTYLER_CSS_VISIBILITY; ?>',
                    'opacity': '<?php echo LANG_INSTYLER_CSS_OPACITY; ?>',
                },
                styleOptions: {
                    'none': '<?php echo LANG_NO; ?>',
                    'repeat': '<?php echo LANG_INSTYLER_CSS_BG_REPEAT_XY; ?>',
                    'repeat-x': '<?php echo LANG_INSTYLER_CSS_BG_REPEAT_X; ?>',
                    'repeat-y': '<?php echo LANG_INSTYLER_CSS_BG_REPEAT_Y; ?>',
                    'no-repeat': '<?php echo LANG_INSTYLER_CSS_BG_REPEAT_NO; ?>',
                    'scroll': '<?php echo LANG_INSTYLER_CSS_BG_ATTACH_SCROLL; ?>',
                    'fixed': '<?php echo LANG_INSTYLER_CSS_BG_ATTACH_FIXED; ?>',
                    'outset': '<?php echo LANG_INSTYLER_CSS_BOX_SHADOW_OUTSET; ?>',
                    'inset': '<?php echo LANG_INSTYLER_CSS_BOX_SHADOW_INSET; ?>',
                }
            };
            var instyler = new Instyler({
                initialURL: '<?php echo $url; ?>',
                url: '<?php echo href_to('instyler'); ?>',
                host: '<?php echo cmsConfig::get('host'); ?>',
				isPremadeList: <?php echo $is_premade_list ? 'true' : 'false'; ?>,
                urls: {
                    load_selectors: '<?php echo href_to('instyler', 'load'); ?>',
                    add_selector: '<?php echo href_to('instyler', 'add'); ?>',
                    save_selector: '<?php echo href_to('instyler', 'save'); ?>',
                    delete_selector: '<?php echo href_to('instyler', 'delete'); ?>',
                    get_scope_type: '<?php echo href_to('instyler', 'scope'); ?>',
                    toggle_selector: '<?php echo href_to('instyler', 'toggle'); ?>',
                    images: '<?php echo href_to('instyler', 'images'); ?>',
                    delete_image: '<?php echo href_to('instyler', 'delete_image'); ?>',
                    move: '<?php echo href_to('instyler', 'move'); ?>',
					premade_list: '<?php echo href_to('instyler', 'premade'); ?>'
                }
            });
        </script>

    </body>
</html>
