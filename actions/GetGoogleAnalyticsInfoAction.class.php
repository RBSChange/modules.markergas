<?php
class markergas_GetGoogleAnalyticsInfoAction extends change_Action
{
	/**
	 * @param change_Context $context
	 * @param change_Request $request
	 */
	public function _execute($context, $request)
	{
		$report = $request->getParameter('report');
		$format = $request->getParameter('format', 0);
		$markerId = $request->getParameter('markerId');
		try 
		{
			$marker = DocumentHelper::getDocumentInstance($markerId);
		}
		catch (Exception $e)
		{
			Framework::exception($e);
			echo LocaleService::getInstance()->trans('m.markergas.bo.dashboard.error-invalid-marker', array('ucf'));
			exit;
		}
		
		$reader = new markergas_GoogleAnalyticsReader($marker->getLogin(), $marker->getPassword(), $marker->getGaSiteId(), 'fr_FR');
		$reader->setHeader($format);
		echo $reader->query($report, $format);
		$reader->close();
		exit;
	}
	
	public function isSecure()
	{
		return true;
	}
}