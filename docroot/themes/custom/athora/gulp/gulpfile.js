const gulp = require('gulp');
const cssPreProcessor = require('gulp-less');
const browserSync = require('browser-sync').create();
const reload = browserSync.reload;
const autoprefixer = require('gulp-autoprefixer');
const config = require('./gulp.config');
const concat = require('gulp-concat');
const minify = require('gulp-minify');


gulp.task('compile-less', function () {
  return gulp.src(config.less.src)
    .pipe(cssPreProcessor())
    .pipe(autoprefixer(config.less.autoprefixer))
    .pipe(gulp.dest(config.less.dest))
    .pipe(browserSync.reload({
      stream: true
    }));
});

gulp.task('js', function () {
  return gulp.src(config.scripts.src)
    .pipe(concat('script.js'))
    .pipe(minify({
      ext:{
        min:'.min.js'
      }
    }))
    .pipe(gulp.dest(config.scripts.dest))
    .pipe(browserSync.reload({
      stream: true
    }));
});

gulp.task('js-search', function () {
  return gulp.src(config.scripts.srcSearch)
    .pipe(minify({
      ext:{
        min:'.min.js'
      }
    }))
    .pipe(gulp.dest(config.scripts.destSearch))
    .pipe(browserSync.reload({
      stream: true
    }));
});

gulp.task('serve', function () {
    // Serve files from the root of this project
    browserSync.init(null, {
      proxy: "http://www.athora-be.com.docker",
      proxyReq: [
        function(proxyReq) {
          proxyReq.setHeader('Access-Control-Allow-Origin', 'all');
        }
      ]
    });
  gulp.watch(config.less.src, gulp.series('compile-less'));
  gulp.watch(config.scripts.src, gulp.series('js'));
});


gulp.task('default', gulp.series('compile-less','js','js-search','serve'));
gulp.task('build', gulp.series('compile-less','js','js-search'));
