# UCF Today Bootstrap WordPress Theme

Theme to replace the original Today WordPress theme, which relies on the Thematic framework. Written from the UCF Generic Bootstrap theme.



## Installation Requirements:
* Settings > Permalinks: set Category base to 'section'; set Tag base to 'topic'
# Home page: replace span divs with span4, span5, and span3 respectively


## Deployment

This theme relies on Twitter's Bootstrap framework. UCF's fork of the Bootstrap project (http://github.com/UCF/bootstrap/) is added as submodule in static/bootstrap. Bootstrap must be initialized as a submodule with every new clone of this theme repository.

#### Initializing Bootstrap with a new clone:
1. Pull/Clone the theme repo
2. From the theme's root directory, run `git submodule update --init static/bootstrap`
3. From the static/bootstrap directory, run `git checkout today`.  Make sure a branch has been checked out for submodules as they will default to 'no branch' when cloned.

#### Alternative method using Git v1.6.5+:
1. Run `git clone` using the `--recursive` parameter to clone the repo with all of its submodules; e.g. `git clone --recursive https://github.com/UCF/Today-Bootstrap.git`
2. From the static/bootstrap directory, run `git checkout today`.  Make sure a branch has been checked out for submodules as they will default to 'no branch' when cloned.


## Development

This theme relies on Twitter's Bootstrap framework. Bootstrap is a CSS framework that uses LESS to programatically develop stylesheets.
UCF's fork of the Bootstrap project (http://github.com/UCF/bootstrap/) is added as submodule in static/bootstrap.

### Setup
** Note: This theme uses a version of Bootstrap whose package requirements result in Bootstrap's CSS files compiling to empty files. Follow the steps below completely to install the packages so that the `make` command works correctly. (https://github.com/twitter/bootstrap/issues/8088) **

0. If they're not already installed on your machine, install node and npm for node-related package management.
1. If this is a brand new clone, run `git submodule update --init static/bootstrap` from the theme's root directory.
2. Navigate to static/bootstrap, then run `npm install` to install necessary dependencies for building Bootstrap's .less files. These packages are excluded in the submodule .gitignore.
3. Navigate to the submodule's node_modules/recess folder, and open **package.json**. Under 'dependencies', update 'less' from '>= 1.3.0' to '1.3.3' and save. Delete node_modules/ from within the recess directory.
4. From the recess directory, run `npm install`.
5. Navigate back to the root bootstrap directory and remove the compiled bootstrap directory, if it exists.

### Compiling
Once the setup instructions above have been completed, you can compile modified .less files from the root bootstrap directory with `make bootstrap`. Compiled files will save to a new directory 'bootstrap' within the root directory (static/bootstrap/bootstrap/).

### Importing Data
Today tends to export HUGE XML files which don't import well-- use a WXR splitter (http://github.com/suhastech/Wordpress-WXR-Splitter/) to generate smaller chunks of data for import.



## Other Notes

n/a


## Custom Post Types

n/a


## Shortcodes

n/a