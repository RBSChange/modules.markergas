<?php
class markergas_BlockEcommercetrackingAction extends website_BlockAction
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
		if ($this->isInBackoffice())
		{
			return website_BlockView::NONE;
		}
		$order = $this->getCurrentOrder();
		if ($order instanceof order_persistentdocument_order)
		{
			$bills = order_BillService::getInstance()->getByOrder($order);
			$bill = count($bills) == 0 ? null : f_util_ArrayUtils::firstElement($bills);
			if ($this->getConfiguration()->getTrackonlypaidorders() ? ($bill && $bill->getStatus() !== order_BillService::FAILED) : true)
			{
				$website = website_WebsiteModuleService::getInstance()->getCurrentWebsite();
				$lang = RequestContext::getInstance()->getLang();
				$markers = website_MarkerService::getInstance()->getByWebsiteAndLang($website, $lang);
				foreach ($markers as $marker)
				{
					if ($marker instanceof markergas_persistentdocument_markergas && $marker->getUseEcommerce() && in_array(
							$order->getBillingModeId(), 
							DocumentHelper::getIdArrayFromDocumentArray(
									$marker->getBillingmodesArray())))
					{
						$html = markergas_MarkergasService::getInstance()->getEcommercePlainHeadMarker(
								$order, $marker, $this->getConfiguration()->getIncludetaxes());
						$this->getContext()->appendToPlainHeadMarker($html);
						break;
					}
				}
			}
		}
		return website_BlockView::NONE;
	}
	
	private function getCurrentOrder()
	{
		$orderParams = $this->getHTTPRequest()->getModuleParameters("order");
		if (isset($orderParams["orderId"]))
		{
			$orderId = $orderParams["orderId"];
			if (intval($orderId))
			{
				try 
				{
					$order = order_persistentdocument_order::getInstanceById(intval($orderId));
					$customer = customer_CustomerService::getInstance()->getCurrentCustomer();
					if (DocumentHelper::equals($customer, $order->getCustomer()))
					{
						return $order;
					}
				} 
				catch (Exception $e) 
				{
					Framework::exception($e);
				}
				
			}
		}
		return null;
	}
}