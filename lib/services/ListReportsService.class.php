<?php
/**
 * @package modules.markergas
 * @method markergas_ListReportsService getInstance()
 */
class markergas_ListReportsService implements list_ListItemsService
{
	/**
	 * Returns an array of list_Item representing the available "websites" withe their markergas id.
	 *
	 * @return list_Item[]
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