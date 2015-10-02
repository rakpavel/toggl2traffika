#!/usr/bin/php
<?php
require_once 'classes/Toggl.php';
require_once 'classes/Traffika.php';
require_once 'classes/Logger.php';

$config = require_once 'config.php';
$logger = new Logger();

date_default_timezone_set('Europe/Prague');

$toggl = new Toggl($config, $logger);

$reports = $toggl->getReportsRespectingDeadline(12);

$traffika = new Traffika($config, $logger);
$traffika->uploadReports($reports);
