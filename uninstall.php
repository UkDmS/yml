<?php

if( ! defined('WP_UNINSTALL_PLUGIN') )

	exit;



delete_option('yml_settings');

delete_option('yml_settings_file');

delete_option('yml_settings_category');