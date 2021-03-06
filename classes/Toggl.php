<?php
class Toggl
{
	const WORKSPACES_URL = 'https://www.toggl.com/api/v8/workspaces';
	const REPORTS_URL = 'https://toggl.com/reports/api/v2/details?workspace_id={workspaceId}&since={since}&until={until}&user_agent=api_test';

	private $token;
	private $workspaces;

	/** @var Logger */
	private $logger;

	public function __construct($config, $logger)
	{
		$this->logger = $logger;
		$this->token = $config['toggl_token'];
		$this->deadlineHour = $config['traffika_timesheet_deadline'];
		if (isset($config['toggl_workspace'])) {
			if (!is_array($config['toggl_workspace'])) {
				$config['toggl_workspace'] = [
					$config['toggl_workspace']
				];
			}
			$this->workspaces = $config['toggl_workspace'];
		}
	}

	public function getTodayReports()
	{
		list($from, $to) = self::getFromToRespectingDeadline($this->deadlineHour);
		return $this->getReports($from, $to);
	}

	public function getThisMonthReports()
	{
		$from = new DateTime('2018-03-01');
		$to = new DateTime('2018-03-31');
		return $this->getReports($from, $to);
	}

	public static function getFromToRespectingDeadline($deadlineHour)
	{
		$now = new DateTime();
		$deadline = new DateTime();
		$deadline->setTime($deadlineHour, 0, 0);

		if ($deadline > $now) {
			$from = new DateTime('-1 days');
			$to = new DateTime();
		} else {
			$from = new DateTime();
			$to = new DateTime('+1 days');
		}

		return [$from, $to];
	}

	public function getReports(DateTime $from, DateTime $to)
	{
		$this->logger->announceTask('Downloading reports from Toggl');
		$workspaceIds = $this->fetchWorkspace();
		$reports = [];
		foreach ($workspaceIds as $key => $value) {
			sleep(1);
			$reports = array_merge($reports, $this->fetchReports($value, $from, $to));
		}
		$this->logger->taskDone();
		return $reports;
	}

	private function fetchWorkspace()
	{
		$result = $this->requestApi(self::WORKSPACES_URL);
		$workspace = null;
		if ($this->workspaces) {
			$tmp = array_filter($result, function ($workspace) {
				return in_array($workspace['name'], $this->workspaces);
			});
			if (count($tmp) != count($this->workspaces)) {
				$this->logger->taskFail("One of configured workspaces is invalid.");
				exit();
			}

			$mapFunc = function ($item) {
				return $item['id'];
			};

			return array_map($mapFunc, $tmp);
		} else {
			$workspace = $result[0];
			return array($workspace['id']);
		}
	}

	private function fetchReports($workspaceId, DateTime $from, DateTime $to)
	{
		$params = [
			'{workspaceId}' => $workspaceId,
			'{since}' => $from->format('Y-m-d'),
			'{until}' => $to->format('Y-m-d')
		];

		$url = strtr(self::REPORTS_URL, $params);
		$result = $this->requestApi($url);
		$data = $result['data'];

		// api can return only 50 reports at once, so we need to get other
		// other pages while total_count is more than what we already have
		$page = 1;
		while ($result['total_count'] > $result['per_page'] * $page) {
			sleep(1);
			$pageUrl = $url . '&page=' . (++$page);
			$result = $this->requestApi($pageUrl);
			$data = array_merge($result['data'], $data);
		}

		return $data;
	}

	private function requestApi($url)
	{
		$options = [
			'http' => [
				'header' => 'Authorization: Basic ' . base64_encode($this->token . ':api_token'),
				'method' => 'GET'
			]
		];
		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		$result = json_decode($result, true);

		return $result;
	}
}
