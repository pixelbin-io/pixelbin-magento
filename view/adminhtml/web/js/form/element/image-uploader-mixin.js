/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(function () {
    'use strict';

    return function (imageUploader) {
        return imageUploader.extend({
            initialize: function () {
                this._super();

                if (typeof this.allowedExtensions === 'string') {
                    this.allowedExtensions += ' svg';
                    this.allowedExtensions += ' webp';
                    this.allowedExtensions += ' tiff';
                    this.allowedExtensions += ' tif';
                    this.allowedExtensions += ' avif';
                    this.allowedExtensions += ' raw';
                }
            }
        });
    };
});
