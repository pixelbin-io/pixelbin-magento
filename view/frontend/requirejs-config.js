var config = {
    map: {
        '*': {
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