var config = {
    map: {
        '*': {
            pixelbinFreeTransform: 'Pixelbinio_Pixelbin/js/pixelbin-free',
            newVideoDialog: 'Pixelbinio_Pixelbin/js/new-video-dialog',
            'Magento_ProductVideo/js/get-video-information': 'Pixelbinio_Pixelbin/js/get-video-information',
            pixelbinMediaLibraryModal: 'Pixelbinio_Pixelbin/js/pixelbin-media-library-modal',
            pixelbinSpinsetModal: 'Pixelbinio_Pixelbin/js/pixelbin-spinset-modal',
            cldspinsetDialog: 'Pixelbinio_Pixelbin/js/pixelbin-spinset-dialog',
            productGallery: 'Pixelbinio_Pixelbin/js/product-gallery',
            pixelbinLazyload: 'Pixelbinio_Pixelbin/js/pixelbin-lazyload',
            updateCmsImages: 'Pixelbinio_Pixelbin/js/cms/preview-update',
        }
    },
    paths: {
        'jquery.lazyload': "Pixelbinio_Pixelbin/js/jquery.lazyload.min",
        pixelbinMediaLibraryAll: "Pixelbinio_Pixelbin/js/all",
        es6Promise: "//cdnjs.cloudflare.com/ajax/libs/es6-promise/4.1.1/es6-promise.auto.min",
        'uiComponent': 'Magento_Ui/js/core/app',
    },
    shim: {
        'jquery.lazyload': {
            deps: ['jquery']
        },
        'uiComponent': {
            deps: ['jquery']
        },
        'Pixelbinio_Pixelbin/js/cms/preview-update': {
            deps: ['jquery']
        }
    },
    config: {
        mixins: {
            'Magento_Ui/js/lib/validation/validator': {
                'Pixelbinio_Pixelbin/js/form/element/validator-rules-mixin': true
            },
        }
    }
};
