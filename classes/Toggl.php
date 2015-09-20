<?php
class Toggl
{
	const WORKSPACES_URL = 'https://www.toggl.com/api/v8/workspaces';
	const REPORTS_URL = 'https://toggl.com/reports/api/v2/details?workspace_id={workspaceId}&since={since}&until={until}&user_agent=api_test';

	private $token;
	private $logger;
	private $workspace;

	public function __construct($config, $logger)
	{
		$this->logger = $logger;
		$this->token = $config['toggl_token'];
		if (isset($config['toggl_workspace'])) {
			$this->workspace = $config['toggl_workspace'];
		}
	}

	public function getTodayReports()
	{
		$from = new DateTime();
		$to = new DateTime('+1 days');
		return $this->getReports($from, $to);
	}

	public function getReports(DateTime $from, DateTime $to)
	{
		$this->logger->announceTask('Downloading reports from Toggl');
		$workspaceId = $this->fetchWorkspace();
		sleep(1);
		$repors = $this->fetchReports($workspaceId, $from, $to);
		$this->logger->taskDone();
		return $repors;
	}

	private function fetchWorkspace()
	{
		$result = $this->requestApi(self::WORKSPACES_URL);
		$workspace = null;
		if ($this->workspace) {
			$tmp = array_filter($result, function($workspace) {
				return $workspace['name'] === $this->workspace;
			});
			if (empty($tmp)) {
				throw new RuntimeException("Invalid workspace. Workspace '{$this->workspace}' not found-");
			}
			$workspace = array_shift($tmp);
		} else {
			$workspace = $result[0];
		}
		return $workspace['id'];
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
		return $result['data'];
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
