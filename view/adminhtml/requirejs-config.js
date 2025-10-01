var config = {
    map: {
        '*': {
            newVideoDialog: 'Pixelbinio_Pixelbin/js/new-video-dialog',
            'Magento_ProductVideo/js/get-video-information': 'Pixelbinio_Pixelbin/js/get-video-information',
            pixelbinMediaLibraryModal: 'Pixelbinio_Pixelbin/js/pixelbin-media-library-modal',
            cldspinsetDialog: 'Pixelbinio_Pixelbin/js/pixelbin-spinset-dialog',
            productGallery: 'Pixelbinio_Pixelbin/js/product-gallery',
            updateCmsImages: 'Pixelbinio_Pixelbin/js/cms/preview-update',
            'Magento_Backend/js/media-uploader': 'Pixelbinio_Pixelbin/js/media-uploader',
            transformationValidation: 'Pixelbinio_Pixelbin/js/validate-transformation'
        }
    },
    paths: {
        //pixelbinMediaLibraryAll: "//cdn.jsdelivr.net/gh/pixelbin-io/media-library-widget/dist/bundle",
        pixelbinMediaLibraryAll: "//cdn.jsdelivr.net/gh/pixelbin-io/media-library-widget@main/dist/bundle",
        es6Promise: "//cdnjs.cloudflare.com/ajax/libs/es6-promise/4.1.1/es6-promise.auto.min",
        'uiComponent': 'Magento_Ui/js/core/app',
    },
    shim: {
        'jquery.lazyload': {
            deps: ['jquery']
        },
        'uiComponent': {
            deps: ['jquery']
        }
    },
    config: {
        mixins: {
            'Magento_Ui/js/form/element/image-uploader': {
                'Pixelbinio_Pixelbin/js/form/element/image-uploader-mixin': true
            },
        }
    }
};
