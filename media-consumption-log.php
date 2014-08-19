<?php

/*
  Plugin Name: Media Consumption Log
  Plugin URI: https://github.com/hurik/wordpress-media-consumption-log
  Description: Shows table with tags of each category and the latest post in it.
  Version: 1.0.0
  Author: Andreas Giemza
  Author URI: http://www.andreasgiemza.de
  License: MIT
 */

/*
  The MIT License

  Copyright 2014 Andreas Giemza (andreas@giemza.net).

  Permission is hereby granted, free of charge, to any person obtaining a copy
  of this software and associated documentation files (the "Software"), to deal
  in the Software without restriction, including without limitation the rights
  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the Software is
  furnished to do so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in
  all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
  THE SOFTWARE.
 */

require_once dirname( __FILE__ ) . '/mcl-functions.php';
require_once dirname( __FILE__ ) . '/mcl-comma-in-tags.php';
require_once dirname( __FILE__ ) . '/mcl-admin-menu.php';
require_once dirname( __FILE__ ) . '/mcl-admin-settings.php';
require_once dirname( __FILE__ ) . '/mcl-admin-finished.php';
require_once dirname( __FILE__ ) . '/mcl-admin-quick-post.php';
require_once dirname( __FILE__ ) . '/mcl-status.php';
require_once dirname( __FILE__ ) . '/mcl-statistics.php';

?>