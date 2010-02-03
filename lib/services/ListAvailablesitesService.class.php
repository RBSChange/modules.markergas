<?php
class markergas_ListAvailablesitesService implements list_ListItemsService
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
		$mgs = markergas_MarkergasService::getInstance();
		$dataArray = $mgs->getAvailableWebsitesForEachMarker();	
		
		$itemArray = array();
	    foreach ($dataArray as $markerId => $websitesAndLangs)
	    {
	    	$itemArray[] = new list_Item($mgs->getLabelFromWebsitesAndLangs($websitesAndLangs), $markerId);
	    }
		return $itemArray;
	}
}