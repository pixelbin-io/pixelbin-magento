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
                    this.allowedExtensions += ' avif';
                    this.allowedExtensions += ' heic';
                    this.allowedExtensions += ' heif';
                    this.allowedExtensions += ' raw';
                    this.allowedExtensions += ' cr2';
                    this.allowedExtensions += ' nef';
                    this.allowedExtensions += ' rw2';
                    this.allowedExtensions += ' dng';
                    this.allowedExtensions += ' orf';
                    this.allowedExtensions += ' pdf';
                    this.allowedExtensions += ' ai';
                    this.allowedExtensions += ' eps';
                }
            }
        });
    };
});
