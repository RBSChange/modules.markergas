<?php
class markergas_BlockDashboardGoogleAnalyticsAction extends website_BlockAction
{	
	/**
	 * @see website_BlockAction::execute()
	 *
	 * @param f_mvc_Request $request
	 * @param f_mvc_Response $response
	 * @return String
	 */
	function execute($request, $response)
	{
		if ($this->isInBackofficeEdition())
		{
			return website_BlockView::DUMMY;
		}
		StyleService::getInstance()->registerStyle('modules.markergas.dashboardGoogleAnalytics');
		return website_BlockView::SUCCESS;
	}
}