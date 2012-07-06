<?php
class markergas_GoogleAnalyticsReader
{
	const TYPE_PDF = 0;
	const TYPE_XML = 1;
	const TYPE_CSV = 2;
	const TYPE_TSV = 3;
	
	private $client, $postdata, $id, $lang;
	
	public function __construct($email, $password, $id, $lang = 'fr_FR')
	{
		$this->id = $id; // site profile id.
		$this->lang = $lang;
		
		$postdata = array(
			'Email' => urlencode($email),
			'Passwd' => $password,
			'GA3T' => '5AS_gBsvDHI',
			'nui' => '15',
			'fpui' => '3',
			'askapache' => 'http://www.askapache.com/',
			'service' => 'analytics',
			'ifr' => 'true', 
			'rm' => 'hide',
			'itmpl' => 'true',
			'hl' => $lang,
			'alwf' => 'true',
			'continue' => 'https://www.google.com/analytics/settings/'
		);
		
		$this->client = HTTPClientService::getInstance()->getNewHTTPClient();
		$this->client->setReferer('https://www.google.com/analytics/');		
		$this->client->post('https://www.google.com/accounts/ServiceLoginBoxAuth', $postdata);
	}
	
	/**
	 * @param string $report
	 * @param string $startDate
	 * @param string $endDate
	 * @return string
	 */
	public function queryAsXml($report, $startDate = null, $endDate = null)
	{ 
		return $this->query($report, self::TYPE_XML, $startDate, $endDate);
	}
	
	/**
	 * @param string $report
	 * @param string $startDate
	 * @param string $endDate
	 * @return string
	 */
	public function queryAsPdf($report, $startDate = null, $endDate = null)
	{ 
		return $this->query($report, self::TYPE_PDF, $startDate, $endDate);
	}
	
	/**
	 * @param string $report
	 * @param string $startDate
	 * @param string $endDate
	 * @return string
	 */
	public function queryAsCsv($report, $startDate = null, $endDate = null)
	{ 
		return $this->query($report, self::TYPE_CSV, $startDate, $endDate);
	}
	
	/**
	 * @param string $report
	 * @param string $startDate
	 * @param string $endDate
	 * @return string
	 */
	public function queryAsTsv($report, $startDate = null, $endDate = null)
	{ 
		return $this->query($report, self::TYPE_TSV, $startDate, $endDate);
	}
	
	/**
	 * @param string $report
	 * @param integer $type
	 * @param string $startDate
	 * @param string $endDate
	 * @return string
	 */
	public function query($report, $type, $startDate = null, $endDate = null)
	{ 
		if (!in_array($report, self::getExistingReports()))
		{
			throw new BaseException('Invalid report "'.$report.'" specified!', 'm.markergas.bo.dashboard.error-invalid-report', array('report' => $report));
		}
		$period = $this->getPeriod($endDate, $startDate);
		$url = "https://www.google.com/analytics/reporting/export?fmt={$type}&id={$this->id}&pdr={$period}&cmp=average&rpt={$report}";
		return $this->client->post($url, array('hl' => $this->lang));
	}
	
	/**
	 * @param integer $type
	 */
	public function setHeader($type)
	{ 
		switch ($type)
		{
			case self::TYPE_PDF :
				header('Content-type: application/pdf', true);
				break;
			
			case self::TYPE_XML : 
				header('Content-type: text/xml; charset="utf-8"', true);
				break;
			
			case self::TYPE_CSV : 
				header('Content-type: application/vnd.ms-excel; charset="utf-8"', true);
				break;
			
			case self::TYPE_TSV :
				header('Content-type: application/tsv; charset="utf-8"', true);
				break;
			
			default :
				break;
		}
	}
	
	public function close()
	{
		$this->client->close();
	}
	
	/**
	 * @return string[]
	 */
	public static function getExistingReports()
	{
		return array(
			'VisitsReport',
			'PageviewsReport',
			'GeoMapReport',
			'TrafficSourcesReport',
			'VisitorsOverviewReport', 
			'VisitorTypesReport', 
			'LanguagesReport', 
			'UniqueVisitorsReport', 
			'AveragePageviewsReport', 
			'TimeOnSiteReport', 
			'BounceRateReport', 
			'LoyaltyReport', 
			'RecencyReport', 
			'LengthOfVisitReport', 
			'DepthOfVisitReport', 
			'BrowsersReport', 
			'PlatformsReport', 
			'OsBrowsersReport', 
			'ColorsReport', 
			'ResolutionsReport', 
			'FlashReport', 
			'JavaReport', 
			'NetworksReport', 
			'HostnamesReport', 
			'SpeedsReport', 
			'UserDefinedReport', 
			'TrafficSourcesReport', 
			'DirectSourcesReport', 
			'ReferringSourcesReport', 
			'SearchEnginesReport', 
			'AllSourcesReport', 
			'KeywordsReport', 
			'AdwordsReport', 
			'KeywordPositionReport', 
			'offline.OfflineAudioReport', 
			'offline.OfflineTvReport', 
			'CampaignsReport', 
			'AdVersionsReport', 
			'ContentReport', 
			'TopContentReport', 
			'ContentByTitleReport', 
			'ContentDrilldownReport', 
			'EntrancesReport', 
			'ExitsReport', 
			'GoalsReport', 
			'GoalConversionReport', 
			'GoalConversionRateReport', 
			'GoalVerificationReport', 
			'ReverseGoalPathReport', 
			'GoalValueReport', 
			'GoalAbandonedFunnelsReport', 
			'GoalFunnelReport', 
			'DashboardReport'
		);
	}
	
	// Private stuff.
		
	private function getPeriod($startDate, $endDate)
	{
		if ($startDate === null)
		{
			$startDate = date_Calendar::yesterday();
		}
		else
		{
			$startDate = date_Calendar::getInstance($startDate);
		}
		
		if ($endDate === null)
		{
			$endDate = date_Calendar::getInstance();
			$endDate->sub(date_Calendar::MONTH, 1);
		}
		else
		{
			$endDate = date_Calendar::getInstance($endDate);
		}
		
		return date_Formatter::format($endDate, 'Ymd') . '-' . date_Formatter::format($startDate, 'Ymd');
	}
}