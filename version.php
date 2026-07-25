<?php
defined('MOODLE_INTERNAL') || die();

$plugin->component = 'block_monlaututoria';
$plugin->version   = 2026090600;
$plugin->requires  = 2025100600; // Moodle 5.1.0 minimum.
$plugin->maturity  = MATURITY_ALPHA;
$plugin->release   = '0.10.1';
$plugin->dependencies = [
    'local_monlaututoria' => 2026090500,
];

