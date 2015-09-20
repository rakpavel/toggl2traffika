<?php
class Traffika
{
	const AUTH_URL = '/auth';
	const PROJECTS_URL = '/projects';
	const ACITVITIES_URL = '/activities';
	const REPORTS_URL = '/users/{userId}/timesheets';
	const DELETE_TIMESHEET_URL = '/users/{userId}/timesheets/{timesheet_id}';

	private $apiUrl;
	private $authToken;
	private $companyDomain;
	private $user;
	private $projects;
	private $activities;
	private $todayTimesheetApiURL;
	private $timesheetDate;
	private $defaultActivityId;

	public function __construct($config, $logger)
	{
		$this->timesheetDate = $this->transformDate("now");
		$this->todayTimesheetApiURL = self::REPORTS_URL.'/'.$this->timesheetDate.'/'.$this->timesheetDate;

		$this->logger = $logger;

		$this->apiUrl = $config['traffika_api_url'];
		$this->authToken = base64_encode($config['traffika_username'] . ':' . $config['traffika_password']);
		$this->companyDomain = $config['traffika_company_domain'];

		$this->logger->announceTask('Fetching Traffika metadata');
		$this->user = $this->fetchUserInfo();
		$this->projects = $this->fetchProjects();
		$this->activities = $this->fetchActivities();
		if (isset($config['traffika_default_activity'])) {
			$this->defaultActivityId = $this->getActivityId($config['traffika_default_activity']);
		}
		$this->logger->taskDone();
		$this->logger->announceTask('Fetching current Timesheet for today');
		$this->todayTimesheets = $this->fetchTodayTimesheet();
		$this->logger->taskDone();
		$this->deleteTimesheets($this->todayTimesheets);
	}

	public function uploadReports(array $reports)
	{
		if (count($reports) == 0) {
			$this->logger->log('No reports to upload.');
			return;
		}

		$reports = $this->transformReports($reports);
		$url = strtr(self::REPORTS_URL, ['{userId}' => $this->user['id']]);

		$this->logger->startCounter('Uploading reports to Traffika', count($reports));

		foreach($reports as $report) {
			$this->logger->addCounter();
			$this->requestApi($url, 'POST', $report);
		}
	}

	private function fetchUserInfo()
	{
		$result = $this->requestApi(self::AUTH_URL);
		return $result['user'];
	}

	private function fetchProjects()
	{
		$projects = [];
		$result = $this->requestApi(self::PROJECTS_URL);
		foreach ($result['data'] as $project) {
			$projects[$project['title']] = $project['id'];
		}
		return $projects;
	}

	private function fetchActivities()
	{
		$activities = [];
		$result = $this->requestApi(self::ACITVITIES_URL);
		foreach ($result['data'] as $activity) {
			$activities[$activity['title']] = $activity['id'];
		}
		return $activities;
	}

	private function fetchTodayTimesheet()
	{
		$timesheetItems = [];
		$url = strtr($this->todayTimesheetApiURL, ['{userId}' => $this->user['id']]);
		$result = $this->requestApi($url);
		$timesheets = $result['data']['time_entries'][$this->timesheetDate];
		foreach ($timesheets as $timesheet) {
			$timesheetItems[] = $timesheet;
		}
		return $timesheetItems;
	}

	private function deleteTimesheets($timesheets)
	{
		if (count($timesheets) > 0) {
			$confirmation = $this->logger->makeDeleteConfirmation($this->timesheetDate);

			if ($confirmation === true) {
				$this->logger->startCounter('Deleting reports in Traffika for date '.$this->timesheetDate, count($timesheets));

				foreach ($timesheets as $timesheet) {
					$url = strtr(self::DELETE_TIMESHEET_URL, [
						'{userId}' => $this->user['id'],
						'{timesheet_id}' => $timesheet['id'],
						]
					);
					$this->requestApi($url, 'DELETE');
					$this->logger->addCounter();
				}
			}
		}
	}

	private function transformReports(array $reports)
	{
		$newReports = [];

		$this->logger->announceTask('Preparing reports for import');
		foreach($reports as $report) {
			$newReports[] = [
				'id' => null,
				'user_id' => $this->user['id'],
				'project_id' => $this->getProjectId($report['project']),
				'activity_id' => $this->getActivityId(isset($report['tags'][0]) ? $report['tags'][0] : ''),
				'date' => $this->transformDate($report['start']),
				'time_spent' => $this->calculateTimeSpent($report['dur']),
				'description' => $report['description'],
				'deleted' => 0
			];
		}
		$this->logger->taskDone();

		return $newReports;
	}

	private function getProjectId($projectName)
	{
		if (array_key_exists($projectName, $this->projects)) {
			return $this->projects[$projectName];
		}
		$this->logger->taskFail('Project "' . $projectName . '" is unknown to Traffika.');
		exit();
	}

	private function getActivityId($activityName)
	{
		if (array_key_exists($activityName, $this->activities)) {
			return $this->activities[$activityName];
		}
		if ($this->defaultActivityId) {
			return $this->defaultActivityId;
		}
		$this->logger->taskFail('Activity "' . $activityName . '" is unknown to Traffika.');
		exit();
	}

	private function transformDate($dateString)
	{
		$date = new DateTime($dateString);
		return $date->format('Y-m-d');
	}

	private function calculateTimeSpent($duration)
	{
		return $duration / 3600000;
	}

	private function requestApi($url, $method = 'GET', $payload = null)
	{
		$options = [
			'http' => [
				'header' => "Content-Type: application/x-www-form-urlencoded\r\n" . 'Authorization: Basic ' . $this->authToken . "\r\n" . 'X-Company-Domain: ' . $this->companyDomain,
				'method' => $method,
			]
		];

		if ($payload) {
			$options['http']['content'] = json_encode($payload);
		}

		$context = stream_context_create($options);
		$result = file_get_contents($this->apiUrl . $url, false, $context);
		$result = json_decode($result, true);
		return $result;
	}
}
