var gulp = require('gulp'),
sass = require('gulp-sass'),
uglify = require('gulp-uglify'),
sourcemaps = require('gulp-sourcemaps'),
modernizr = require('gulp-modernizr'),
browserSync = require('browser-sync').create(),
plumber = require('gulp-plumber'),
rename = require('gulp-rename'),
notify = require('gulp-notify');

// Livereload Task
// Active after sass
gulp.task('browser-sync', function(){
  var files = [
    './assets/css/pp.css',
  ];
  
  // Initialize Browsersync with a PHP server
  browserSync.init(files, {
    proxy: "http://dev.funkmo/g4g"
  });
});

// Scripts Task
// Uglifies
gulp.task('scripts', function() {
  gulp.src('assets/js/apps.js')
  .pipe(uglify())
  .pipe(plumber())
  .pipe(rename({extname:'.min.js'}))
  .pipe(gulp.dest('assets/js'))
  .pipe(browserSync.stream());
});

// Styles Task
// Uglifies
gulp.task('sass', function() {
  
  gulp.src(['assets/sass/pp.scss'])
  .pipe(plumber({errorHandler: function(err){
    notify.onError({
      title: "Gulp Error in" + err.plugin,
      message: err.toString()
    })(err)
  }}))
  .pipe(sourcemaps.init())
  .pipe(sass({outputStyle: 'nested'}))
  .pipe(sourcemaps.write('style-maps'))
  .pipe(gulp.dest('./assets/css'))
  .pipe(browserSync.stream());

  gulp.src('assets/sass/fonts.scss')
  .pipe(plumber({errorHandler: function(err){
    notify.onError({
      title: "Gulp Error in" + err.plugin,
      message: err.toString()
    })(err)
  }}))
  .pipe(sourcemaps.init())
  .pipe(sass({outputStyle: 'compressed'}))
  .pipe(gulp.dest('./assets/css/'))
  .pipe(browserSync.stream());
  
});

// Task Task
// Watches JS
gulp.task('watch', function() {
  gulp.watch('assets/js/*.js', ['scripts']);
  gulp.watch('assets/**/**.scss', ['sass']);
});

// Default Task
// Gulp
gulp.task('default', ['scripts', 'browser-sync', 'sass', 'watch']);
