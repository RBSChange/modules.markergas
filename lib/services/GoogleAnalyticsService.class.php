<?php
/**
 * markergas_GoogleAnalyticsService
 * @package modules.markergas.lib.services
 */
class markergas_GoogleAnalyticsService extends BaseService
{
	/**
	 * Singleton
	 * @var markergas_GoogleAnalyticsService
	 */
	private static $instance = null;

	/**
	 * @return markergas_GoogleAnalyticsService
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * @param String $xmlReport
	 * @return Array
	 */
	public function parseXmlReport($xmlReport)
	{
		$domData = DOMDocument::loadXML($xmlReport);
		if ($domData)
		{
			// Get report name.
			$reports = $domData->getElementsByTagName('Report');
			if ($reports->length > 0)
			{
				$reportName = $reports->item(0)->getAttribute('name');
			
				// Table case.
				$tables = $domData->getElementsByTagName('Table');
				if ($tables->length > 0)
				{
					$widget = $this->parseXmlTable($tables->item(0));
				}
				
				// Bar case.
				$tables = $domData->getElementsByTagName('HorizontalBar');
				if ($tables->length > 0)
				{
					$widget = $this->parseXmlHorizontalBar($tables->item(0));
				}
				
				if (isset($widget))
				{
					$widget['reportName'] = $reportName;
					return $widget;
				}
			}
		}
		return null;
	}
	
	/**
	 * @param DOMNode $table
	 */
	private function parseXmlTable($table)
	{
		$columns = array();
		$columns[] = array('Name' => '');
		$domColumns = $table->getElementsByTagName('Column');
		for ($i = 0; $i < $domColumns->length; $i++)
		{
			$domColumn = $domColumns->item($i);
			$column = array();
			$children = $domColumn->childNodes;
			for ($j = 0; $j < $children->length; $j++)
			{
				$child = $children->item($j);
				if ($child->nodeName == 'Active' && $child->nodeValue == 'false')
				{
					continue 2;
				}
				else 
				{
					$column[$child->nodeName] = $child->nodeValue;
				}
			}
			$columns[] = $column;
		}
		
		$lines = array();
		$domRows = $table->getElementsByTagName('Row');
		for ($i = 0; $i < $domRows->length; $i++)
		{
			$domRow = $domRows->item($i);
			$line = array();
			$line[] = array('Value' => $domRow->getElementsByTagName('PrimaryKey')->item(0)->nodeValue);
			$domCells = $domRow->getElementsByTagName('Cell');
			for ($j = 0; $j < $domCells->length; $j++)
			{
				$domCell = $domCells->item($j);
				$value = $domCell->getElementsByTagName('Content')->item(0)->firstChild->nodeValue;
				$line[] = array('Value' => $value);
			}
			$lines[] = $line;
		}
		return array('lines' => $lines, 'columns' => $columns);
	}
	
	/**
	 * @param DOMNode $table
	 */
	private function parseXmlHorizontalBar($table)
	{
		$columns = array();
					
		$lines = array();
		$domRows = $table->getElementsByTagName('BarList');
		for ($i = 0; $i < $domRows->length; $i++)
		{
			$domRow = $domRows->item($i);
			$line = array();
			$children = $domRow->childNodes;
			for ($j = 0; $j < $children->length; $j++)
			{
				$child = $children->item($j);
				switch ($child->nodeName)
				{
					case 'Label' :
						$line[1] = array('Value' => $child->nodeValue);
						break;
				
					case 'Percent' :
						$line[2] = array('Value' => $child->nodeValue);
						break;
				
					case 'Value' :
						$line[3] = array('Value' => $child->nodeValue);
						break;
				}
			}
			ksort($line);
			$lines[] = $line;
		}
		return array('lines' => $lines, 'columns' => $columns);
	}
}