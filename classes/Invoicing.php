<?php
class Invoicing
{
	private $summarized;

	/** @var Logger */
	private $logger;

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
			$client = empty($report['client']) ? 'Unknown' : $report['client'];

			if (!isset($this->summarized[$client])) {
				$this->summarized[$client] = [];
			}

			if (!isset($this->summarized[$client][$report['project']])) {
				$this->summarized[$client][$report['project']] = 0;
			}

			$this->summarized[$client][$report['project']] += $report['dur'];
		}

		$this->printSummary();
	}

	public function printSummary()
	{
		$mask = "\t%-30.30s %6.2f\n";
		$timeSum = 0;
		print "\n";
		
		foreach ($this->summarized as $client => $projects) {
			print "$client\n";

			foreach ($projects as $project => $time) {
				$time = ceil($time / 36000) / 100;
				$timeSum += $time;
				printf($mask, $project, $time);
			}
		}

		printf("\nTotal: %6.2f\n", $timeSum);
	}
	
}