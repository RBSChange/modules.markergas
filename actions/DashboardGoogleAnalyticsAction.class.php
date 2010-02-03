<?php
class markergas_DashboardGoogleAnalyticsAction extends dashboard_BaseModuleAction
{
	/**
	 * @see dashboard_BaseModuleAction::getIcon()
	 *
	 * @return string
	 */
	protected function getIcon()
	{
		return 'line-chart';
	}
	
	/**
	 * @see dashboard_BaseModuleAction::getTitle()
	 *
	 * @return string
	 */
	protected function getTitle()
	{
		return f_Locale::translate('&modules.markergas.bo.dashboard.Report-block;');
	}
	
	/**
	 * @see dashboard_BaseModuleAction::getContent()
	 *
	 * @param Context $context
	 * @param Request $request
	 * @return string
	 */
	protected function getContent($context, $request)
	{
		$report = $request->getParameter('report', 'VisitsReport');
		$markerId = $request->getParameter('markerId');
		try 
		{
			$marker = DocumentHelper::getDocumentInstance($markerId);
			$mgs = markergas_MarkergasService::getInstance();
			$websitesAndLangs = $mgs->getRelatedWebsitesAndLangsByMarkergas($marker);
			$websitesLabel = $mgs->getLabelFromWebsitesAndLangs($websitesAndLangs);
		}
		catch (Exception $e)
		{
			Framework::exception($e);
			return f_Locale::translate('&modules.markergas.bo.dashboard.Error-invalid-marker;');
		}
		
		if ($marker->getLogin() && $marker->getPassword() && $marker->getGaSiteId())
		{
			$reader = new markergas_GoogleAnalyticsReader($marker->getLogin(), $marker->getPassword(), $marker->getGaSiteId(), 'fr_FR');
			$xmlData = $reader->queryAsXml($report);
			$reader->close();
			
			$widget = markergas_GoogleAnalyticsService::getInstance()->parseXmlReport($xmlData);
			if ($widget !== null)
			{
				$templateObject = $this->createNewTemplate('modules_markergas', 'Markergas-Action-DashboardGoogleAnalytics', 'html');
				$templateObject->setAttribute('websitesLabel', $websitesLabel);
				$templateObject->setAttribute('widget', $widget);
				$templateObject->setAttribute('report', $report);
				$templateObject->setAttribute('markerId', $markerId);
				return $templateObject->execute();
			}
			else
			{
				return f_Locale::translate('&modules.markergas.bo.dashboard.Error-getting-data;');
			}
		}
		else
		{
			return f_Locale::translate('&modules.markergas.bo.dashboard.Error-invalid-marker;');
		}
	}
}