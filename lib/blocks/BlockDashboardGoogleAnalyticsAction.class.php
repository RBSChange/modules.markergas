<?php
class markergas_BlockDashboardGoogleAnalyticsAction extends website_BlockAction
{	
	/**
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return string
	 */
	public function execute($request, $response)
	{
		if ($this->isInBackofficeEdition())
		{
			return website_BlockView::DUMMY;
		}
		website_StyleService::getInstance()->registerStyle('modules.markergas.dashboardGoogleAnalytics');
		return website_BlockView::SUCCESS;
	}
}