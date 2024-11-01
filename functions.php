<?php

//define constants
$sname = "theaCounter";
define("THEACOUNTER",$sname);
define("THEACOUNTERFNAME","wp-post-views-counter");

$site = get_bloginfo("url");
$parse  = parse_url($site);

$domain = $parse['host'];

define("THEADOMAIN",$domain);

define("THEAPATH",plugin_dir_path( __FILE__ ));

define("THEAURI",plugin_dir_url( __FILE__ ));
//
require(plugin_dir_path( __FILE__ )."/lib/counter.class.php");

$thea_counterwp = new TheaCounter();

