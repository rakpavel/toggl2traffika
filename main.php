#!/usr/bin/php
<?php
require_once 'classes/Logger.php';
require_once 'classes/Reporting.php';
require_once 'classes/Runner.php';
require_once 'classes/Timesheets.php';
require_once 'classes/Toggl.php';
require_once 'classes/Traffika.php';

date_default_timezone_set('Europe/Prague');

$runner = new Runner();
$runner->run($argv);
