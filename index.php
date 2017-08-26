<?php

/*
  Plugin Name: FTP Wordpress
  Plugin URI: https://github.com/filipemorelli/FTP-Wordpress
  Description: Plugin for manager files like FTP
  Version: 1.0.0
  Author: Filipe Morelli
  Author URI: http://morellibrasil.com.br
  License: GPLv2
 */

namespace FTPWordpress;

require __DIR__ . '/vendor/autoload.php';
require 'FTPWordpress.php';


$ftpWordpress = new FTPWordpress();
$ftpWordpress->start();
