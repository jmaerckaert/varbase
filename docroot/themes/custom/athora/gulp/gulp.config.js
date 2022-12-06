var rootPath = ('../');

module.exports = {
    browserSync: {
        files: [
            rootPath + 'js/**/*.js',
            rootPath +'less/**/*.less'
        ],
        server: {
            baseDir: 'public'
        },
        startPath: 'index.html'
    },
    images: {
        src: rootPath + 'images/**/*.(png|jpg|jpeg|gif|svg)',
        dest: rootPath + 'images'
    },
    less: {
        src: [
            rootPath + 'less/**/*.less'
        ],
        dest: rootPath + 'css',
        imagePath: rootPath + 'images',
        includePaths: [

        ],
        minify: true,
        autoprefixer: {
            browsers: ['last 2 versions']
        }
    },
    scripts: {
        src: [
            rootPath + 'js/detect.min.js',
            rootPath + 'js/custom.js',
            rootPath + 'js/gstt.js'
        ],
        srcSearch: [
          '../../../../modules/custom/tbn_search/assets/js/tbn_search.js'
        ],
        destSearch: '../../../../modules/custom/tbn_search/assets/js',
        dest: rootPath + 'js',
        minify: true
    }
};
