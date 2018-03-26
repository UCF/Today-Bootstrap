var browserSync = require('browser-sync').create(),
    gulp = require('gulp'),
    autoprefixer = require('gulp-autoprefixer'),
    cleanCSS = require('gulp-clean-css'),
    include = require('gulp-include'),
    eslint = require('gulp-eslint'),
    isFixed = require('gulp-eslint-if-fixed'),
    babel = require('gulp-babel'),
    rename = require('gulp-rename'),
    sass = require('gulp-sass'),
    scsslint = require('gulp-scss-lint'),
    uglify = require('gulp-uglify'),
    runSequence = require('run-sequence'),
    merge = require('merge');


var configLocal = require('./gulp-config.json'),
    configDefault = {
      src: {
        scssPath: './src/scss',
        jsPath:   './src/js'
      },
      dist: {
        cssPath:  './static/css',
        jsPath:   './static/js',
        fontPath: './static/fonts',
        imgPath: './static/img'
      },
      packagesPath: './node_modules',
      sync: false,
      syncTarget: 'http://localhost/'
    },
    config = merge(configDefault, configLocal);


//
// CSS
//

// Base linting function
function lintSCSS(src) {
  return gulp.src(src)
    .pipe(scsslint({
      'maxBuffer': 1000 * 1024  // default: 300 * 1024
    }));
}

// Lint all theme scss files (including admin styles)
gulp.task('scss-lint-theme', function() {
  return lintSCSS(config.src.scssPath + '/*.scss');
});

// Lint all dev scss files
gulp.task('scss-lint-dev', function(event) {
  return lintSCSS(config.devPath + '/**/*.scss');
});

// Base SCSS compile function
function buildCSS(src, dest) {
  dest = dest || config.dist.cssPath;

  return gulp.src(src)
    .pipe(sass({
      includePaths: [config.src.scssPath, config.packagesPath]
    })
      .on('error', sass.logError))
    .pipe(cleanCSS())
    .pipe(autoprefixer({
      // Supported browsers added in package.json ("browserslist")
      cascade: false
    }))
    .pipe(rename({
      extname: '.min.css'
    }))
    .pipe(gulp.dest(dest))
    .pipe(browserSync.stream());
}

// Compile theme stylesheet (does not include admin styles)
gulp.task('scss-build-theme', function() {
  return buildCSS(config.src.scssPath + '/style.scss');
});

// Compile admin stylesheet
gulp.task('scss-build-admin', function() {
  return buildCSS(config.src.scssPath + '/admin.scss');
});

// All theme css-related tasks
gulp.task('css', ['scss-lint-theme', 'scss-build-theme', 'scss-build-admin']);


//
// JavaScript
//

// Run eslint on js files in src.jsPath. Do not perform linting
// on vendor js files.
gulp.task('es-lint', function() {
  return gulp.src([config.src.jsPath + '/*.js'])
    .pipe(eslint({ fix: true }))
    .pipe(eslint.format())
    .pipe(isFixed(config.src.jsPath));
});

// Uglify js script file through babel
gulp.task('js-build', function() {
  return gulp.src(config.src.jsPath + '/script.js')
    .pipe(include({
      includePaths: [config.packagesPath, config.src.jsPath]
    }))
      .on('error', console.log)
    .pipe(babel())
    .pipe(uglify())
    .pipe(rename('script.min.js'))
    .pipe(gulp.dest(config.dist.jsPath))
    .pipe(browserSync.stream());
});

// Uglify admin js file through babel
gulp.task('js-build-admin', function () {
  return gulp.src(config.src.jsPath + '/admin.js')
    .pipe(include({
      includePaths: [config.packagesPath, config.src.jsPath]
    }))
    .on('error', console.log)
    .pipe(babel())
    .pipe(uglify())
    .pipe(rename('admin.min.js'))
    .pipe(gulp.dest(config.dist.jsPath))
    .pipe(browserSync.stream());
});

// All js-related tasks
gulp.task('js', function() {
  runSequence('es-lint', 'js-build', 'js-build-admin');
});


//
// Rerun tasks when files change
//
gulp.task('watch', function() {
  if (config.sync) {
    browserSync.init({
        proxy: {
          target: config.syncTarget
        }
    });
  }

  gulp.watch(config.src.scssPath + '/**/*.scss', ['css']);
  gulp.watch(config.src.jsPath + '/**/*.js', ['js']);
  gulp.watch('./**/*.php').on('change', browserSync.reload);
});


//
// Default task
//
gulp.task('default', function() {
  runSequence('css', 'js');
});
