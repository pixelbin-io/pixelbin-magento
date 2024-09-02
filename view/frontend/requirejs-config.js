var config = {
    map: {
        '*': {
            loadPlayer: 'Pixelbinio_Pixelbin/js/load-player',
            'Magento_ProductVideo/js/fotorama-add-video-events': 'Pixelbinio_Pixelbin/js/fotorama-add-video-events',
            pixelbinLazyload: 'Pixelbinio_Pixelbin/js/pixelbin-lazyload'
        }
    },
    paths: {
        'jquery.lazyload': "Pixelbinio_Pixelbin/js/jquery.lazyload.min"
    },
    shim: {
        'jquery.lazyload': {
            deps: ['jquery']
        },
    }
};
