<?php
class markergas_ListReportsService implements list_ListItemsService
{
    /**
     * @var markergas_ListAvailablesitesService
     */
	private static $instance = null;

	/**
	 * @return markergas_ListAvailablesitesService
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			$className = get_class();
			self::$instance = new $className;
		}
		return self::$instance;
	}

	/**
	 * Returns an array of list_Item representing the available "websites" withe their markergas id.
	 *
	 * @return Array<list_Item>
	 */
	public function getItems()
	{
		$itemArray = array();
	    foreach (markergas_GoogleAnalyticsReader::getExistingReports() as $report)
	    {
	    	$itemArray[] = new list_Item(f_Locale::translate('&modules.markergas.bo.dashboard.reports.'.$report.';'), $report);
	    }
		return $itemArray;
	}
}