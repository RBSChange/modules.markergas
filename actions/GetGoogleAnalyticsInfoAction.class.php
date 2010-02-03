<?php
class markergas_GetGoogleAnalyticsInfoAction extends f_action_BaseAction
{
	/**
	 * @param Context $context
	 * @param Request $request
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
			echo f_Locale::translate('&modules.markergas.bo.dashboard.Error-invalid-marker;');
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