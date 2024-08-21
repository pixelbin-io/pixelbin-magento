define([
    'jquery',
    'mageUtils',
    'uiRegistry',
    'productGallery',
    'Magento_Ui/js/modal/alert',
    'mage/backend/notification',
    'mage/translate',
    'Magento_MediaGalleryUi/js/image-uploader',
    'jquery/ui',
    'Magento_Ui/js/modal/modal',
    'mage/backend/validation',
    'pixelbinMediaLibraryAll',
    'es6Promise',


], function($, mageUtils, registry, productGallery, uiAlert, notification, $t, imageUploader) {
    'use strict';

    $.widget('mage.pixelbinMediaLibraryModal', {

        options: {
            cloud_name: "",
            remove_header: true,
            max_files: "1",
            insert_caption: "",
            inline_container: "",
            default_transformations: [[]],
            button_class: "",
            button_caption: ""
        },

        /**
         * Bind events
         * @private
         */
        _bind: function() {
            if ($(this.options.buttonSelector).length) {
                $(this.options.buttonSelector).on('click', this.openMediaLibrary.bind(this));
            } else {
                this.element.on('click', this.openMediaLibrary.bind(this));
            }
        },

        /**
         * @param {Array} messages
         */
        notifyError: function(messages) {
            var data = {
                content: messages.join('')
            };
            if (messages.length > 1) {
                data.modalClass = '_image-box';
            }
            uiAlert(data);
            return this;
        },

        /**
         * @private
         */
        _create: function() {
            this._super();
            this._bind();

            var widget = this;
            var uiRegistry = registry;


            window.pixelbin = window.pixelbin || [];
            console.log('all options');
            console.log(this.options)
            this.options.cldMLid = this.options.cldMLid || 0;

            if (typeof window.pixelbin[this.options.cldMLid] === "undefined") {
                window.ml = window.pixelbin.createMediaLibrary(
                    this.options, {
                        insertHandler: function(data) {
                            console.log("insert handler data");
                            console.log(data);
                            $('body').first().css('overflow', 'initial');
                            if (widget.isMediaBrowser()) {
                                return widget.pixelbinInsertHandler(data);
                            } else {
                                // new media gallery
                                data['newGalleryMode'] = 1;
                                widget.pixelbinInsertHandler(data);
                                if (uiRegistry.get('media_gallery_listing.media_gallery_listing_data_source')) {
                                    $(window).trigger('reload.MediaGallery');
                                }
                            }
                        }
                    }
                );
            } else {
                this.pixelbin_ml = window.pixelbin_ml[this.options.cldMLid];
            }

        },
        getMageMediaBrowserData: function () {
            return $('.media-gallery-modal').data('mageMediabrowser');
        },

        isMediaBrowser: function () {
            return typeof this.getMageMediaBrowserData() !== 'undefined';
        },

        selectImageInNewMediaGallery: function (record) {
            this.uploadHandler(record);
        },
        /**
         * Fired on trigger "openMediaLibrary"
         */
        openMediaLibrary: function() {
            window.ml.show(this.options.pixelbinShowOptions);
        },
        showLoader: function () {
            this.loader(true);
        },

        /**
         * Hides spinner loader
         */
        hideLoader: function () {
            this.loader(false);
        },
        /**
         * Escape Regex
         */
        escapeRegex: function(string) {
            return string.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
        },

        /**
         * Fired on trigger "pixelbinInsertHandler"
         */
        pixelbinInsertHandler: function(data) {
            var widget = this;
            var aggregatedErrorMessages = [];
            var $i = data.assets.length;

            data.assets.forEach(asset => {
                //console.log(asset);
                $i--;
                if (widget.options.imageUploaderUrl) {
                    asset.asset_url = asset.asset_image_url = asset.secure_url;
                    asset.free_transformation = "";
                    $.ajax({
                        url: widget.options.imageUploaderUrl,
                        data: {
                            asset: asset,
                            remote_image: asset.asset_image_url,
                            param_name: widget.options.imageParamName,
                            form_key: window.FORM_KEY
                        },
                        method: 'POST',
                        dataType: 'json',
                        async: false,
                        showLoader: true
                    }).done(
                        function(file) {
                            if (file.file && !file.error) {
                                var context = (asset.context && asset.context.custom) ? asset.context.custom : {};
                                if (asset.resource_type === "video") {
                                    file.video_provider = 'pixelbin';
                                    file.media_type = "external-video";
                                    file.video_url = asset.asset_url;
                                    file.video_title = context.caption || context.alt || asset.public_id || "";
                                    file.video_description = (context.description || context.alt || context.caption || "").replace(/(&nbsp;|<([^>]+)>)/ig, '');
                                    if (file.using_placeholder_fallback) {
                                        notification().add({
                                            error: false,
                                            message: $t("Couldn't automatically generate pixelbin video thumbnail, using fallback placeholder instead. You can always replace that manually later"),
                                            insertMethod: function(constructedMessage) {
                                                aggregatedErrorMessages.push(constructedMessage);
                                            }
                                        });
                                    }
                                } else {
                                    file.media_type = "image";
                                    file.label = asset.label = context.alt || context.caption || asset.public_id || "";
                                    if (widget.options.addTmpExtension && !/\.tmp$/.test(file.file)) {
                                        file.file = file.file + '.tmp';
                                    }
                                }
                                file.free_transformation = asset.free_transformation;
                                file.asset_derived_image_url = asset.asset_derived_image_url;
                                file.image_url = asset.asset_image_url;
                                file.pixelbin_asset = asset;

                                if (widget.options.triggerSelector && widget.options.triggerEvent) {
                                    $(widget.options.triggerSelector).last().trigger(widget.options.triggerEvent, file);
                                    if (asset.resource_type === "video") {
                                        $(widget.options.triggerSelector).last().find('img[src="' + file.url + '"]').addClass('video-item');
                                    }
                                }
                                if (widget.options.callbackHandler && widget.options.callbackHandlerMethod && typeof widget.options.callbackHandler[widget.options.callbackHandlerMethod] === 'function') {
                                    widget.options.callbackHandler[widget.options.callbackHandlerMethod](file);
                                }
                            } else {
                                console.error(file);
                                notification().add({
                                    error: true,
                                    message: $t('An error occured during ' + asset.resource_type + ' insert (' + asset.public_id + ')!') + '%s%sError: ' + file.error.replace(/File:.*$/, ''),
                                    insertMethod: function(constructedMessage) {
                                        aggregatedErrorMessages.push(constructedMessage.replace('%s%s', '<br>'));
                                    }
                                });
                            }
                            if (!$i && aggregatedErrorMessages.length) {
                                widget.notifyError(aggregatedErrorMessages);
                            }
                        }
                    ).fail(
                        function(response) {
                            console.error(response);
                            notification().add({
                                error: true,
                                message: $t('An error occured during ' + asset.resource_type + ' insert (' + asset.public_id + ')!')
                            });
                            if (!$i && aggregatedErrorMessages.length) {
                                widget.notifyError(aggregatedErrorMessages);
                            }
                        }
                    );
                }
            });
        }
    });

    return $.mage.pixelbinMediaLibraryModal;
});
