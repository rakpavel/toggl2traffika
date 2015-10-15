<?php
class Invoicing
{
	private $logger;
	private $sumarized;

	public function __construct($logger)
	{
		$this->logger = $logger;
	}

	public function getPeriod($dateString)
	{
		list($month, $year) = explode('/', $dateString);
		$date = $year . '-' . $month . '-01';
		$start = new DateTime(date('Y-m-01', strtotime($date)));
		$end = new DateTime(date('Y-m-t', strtotime($date)));
		return [$start, $end];
	}

	public function getCurrentMonthPeriod()
	{
		$start = new DateTime(date('Y-m-01'));
		$end = new DateTime(date('Y-m-t'));
		return [$start, $end];
	}

	public function summarize($reports)
	{
		$this->summarized = [];

		foreach($reports as $report) {
			if (!isset($this->summarized[$report['client']])) {
				$this->summarized[$report['client']] = [];
			}

			if (!isset($this->summarized[$report['client']][$report['project']])) {
				$this->summarized[$report['client']][$report['project']] = 0;
			}

			$this->summarized[$report['client']][$report['project']] += $report['dur'];
		}

		$this->printSummary();
	}

	public function printSummary()
	{
		$mask = "\t%-30.30s %5.2f\n";
		print "\n";
		
		foreach ($this->summarized as $client => $projects) {
			$client = empty($client) ? 'Unknown' : $client;
			print "$client\n";

			foreach ($projects as $project => $time) {
				$time = ceil($time / 36000) / 100;
				printf($mask, $project, $time);
			}
		}
	}
	
}