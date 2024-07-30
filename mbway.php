<?php

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

$document = Factory::getDocument();
// Load Bootstrap CSS
$document->addStyleSheet('https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css');
// Load intlTelInput CSS and JS
$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css');
$document->addScript('https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js');

// Load jQuery
$document->addScript('https://code.jquery.com/jquery-3.6.0.min.js');

// Load custom CSS and JS
$document->addStyleSheet(Uri::base() . 'plugins/gurupayment/mbway/assets/css/style.css');
$document->addScript(Uri::base() . 'plugins/gurupayment/mbway/assets/js/script.js');

$input = Factory::getApplication()->input; 

class plgGuruPaymentMbway extends CMSPlugin
{
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage();

    }

    public function onAjaxMbway()
    {
            if (isset($_POST['orderid'], $_POST['phone'], $_POST['amountTotal'], $_POST['mbwayKey'])) {
                $data = array(
                    "mbWayKey" => $_POST['mbwayKey'],  
                    "orderId" => $_POST['orderid'],
                    "amount" => $_POST['amountTotal'],
                    "mobileNumber" => $_POST['phone']
                );
        
                $postData = json_encode($data);
                $curl = curl_init();
        
                // Set cURL options
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://api.ifthenpay.com/spg/payment/mbway',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $postData,
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Cookie: ASP.NET_SessionId=z3rdyoo1l2phhc0p2tyubbcj'
                    ),
                )
                );
        
                $response = curl_exec($curl);
        
                if (curl_errno($curl)) {
                    $response = json_encode(['error' => curl_error($curl)]);
                }
        
                curl_close($curl);
                header('Content-Type: application/json');
                echo $response;
                JFactory::getApplication()->close();
            } elseif(isset($_POST['reqID']) && !empty($_POST['reqID'])) {
                $requestId = $_POST['reqID'];
            
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://api.ifthenpay.com/spg/payment/mbway/status?mbWayKey=DQL-903755&requestId=' . $requestId,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'Cookie: ASP.NET_SessionId=sk0fo102szxv1tpci1bhy1uv'
                    ),
                ));
            
                $response = curl_exec($curl);
                header('Content-Type: application/json');
                echo $response;
                JFactory::getApplication()->close();
                curl_close($curl);
            
            } else {
                echo json_encode('Missing required fields');
                Factory::getApplication()->close();
            }    
    }

    //initiate payment
    function onSendPayment(&$post)
    {
        $db = Factory::getDBO();

        if ($post['processor'] != 'mbway') {
            return false;
        }
        global $mbWayKey;
        global $phishingKey;
        $params = new Registry($post['params']);
        $param['mbwaykey'] = $params->get('mbway_key');
        $param['phishingkey'] = $params->get('phishing_key');
        $mbWayKey = $param['mbwaykey'];
        $phishingKey = $param['phishingkey'];
        
        //Factory::getApplication()->enqueueMessage("<pre>" . $mbWayKey . "</pre>", 'error');
        //Factory::getApplication()->enqueueMessage("<pre>" . $phishingKey . "</pre>", 'error');

        if (!$param['mbwaykey'] && !$param['phishingkey']) {
            $error = Text::_('PLG_MBWAY_MISSING_FIELDS_ERROR');
            Factory::getApplication()->enqueueMessage($error, 'error');
            return;
        }

        $params = json_decode($post["params"], true);
        
        $link_params = array();
		$link_params['option'] = $post['option'];
		$link_params['controller'] = $post['controller'];
		$link_params['task'] = $post['task'];
		$link_params['processor'] = $post['processor'];
		$link_params['order_id'] = @$post['order_id'];
		//$link_params['sid'] = @$post['sid'];
		//$link_params['Itemid'] = isset($post['Itemid']) ? $post['Itemid'] : '0';
		//$notify_url = JURI::base().'index.php?'.$this->DotPayArray2Url($link_params).'&customer_id='.intval($post['customer_id']).'&pay=ipn'; with customer id
		$notify_url = JURI::base().'index.php?'.$this->DotPayArray2Url($link_params).'&orderId='.$post['order_id'].'&key='.$phishingKey.'&pay=ipn';
		$callback_url = JURI::base().'index.php?'.$this->DotPayArray2Url($link_params).'&orderId=[ORDER_ID]&key=[ANTI_PHISHING_KEY]&amount=[AMOUNT]&requestId=[REQUEST_ID]&pay=ipn';
		$myorder_url = JURI::base().'guruorders/myorders';
		$cart_page_url = JURI::base().'gurubuy';
        //Factory::getApplication()->enqueueMessage("<pre>" . $notify_url . "</pre>", 'error');
        //Factory::getApplication()->enqueueMessage("<pre>" . $callback_url . "</pre>", 'error');
        //Factory::getApplication()->enqueueMessage("<pre>" . $myorder_url . "</pre>", 'error');
        // get order details
        $db = JFactory::getDbo();
		$sql = "select `userid`, `amount`, `amount_paid` from #__guru_order where `id`=".intval($link_params['order_id']);
		$db->setQuery($sql);
		$db->execute();
		$order_details = $db->loadAssocList();
        $amount = $order_details[0]['amount'];

        $return = JURI::root() . 'index.php?option=com_guru&view=guruorders&layout=mycourses';
        $cancel_return = JURI::root() . 'index.php?option=com_guru&controller=guruBuy&processor=offline&task=' . $post['task'] . '&sid=' . $post['sid'] . '&order_id=' . $post['order_id'] . '&pay=fail';

        $form = '<form id="mbwayform" style="text-align: center;" name="mbwayform" action="' . JUri::current() . '" method="post">';
        $form .= '<div class="alert alert-info">'.Text::_('PLG_MBWAY_HEADING').'</div>';
        $form .= '<img src="' . JURI::base(true) . '/plugins/gurupayment/mbway/mbway-logo.png" alt="Logo">';
        $form .= '<input style="display: none;" type="hidden" id="orderid" name="orderid" value="' . $link_params['order_id'] . '" required disabled>';
        $form .= '<input style="display: none;" type="hidden" id="amountid" name="amountid" value="' . $amount . '" required disabled>';
        $form .= '<input style="display: none;" type="hidden" id="mbwayKey" name="mbwayKey" value="' . $mbWayKey . '" required disabled>';
        $form .= '<input type="hidden" id="cancel_return" name="cancel_return" value="' . $cart_page_url . '" required disabled>';
        $form .= '<input type="hidden" id="myorder_url" name="myorder_url" value="' . $myorder_url . '" required disabled>';
        $form .= '<label for="phone">'.Text::_('PLG_MBWAY_PHONE_NUMBER').'</label>';
        $form .= '<input id="phone" type="tel" name="phone" />';
        $form .= '<div class="mbwayform-btn">';
        $form .= '<input type="button" class="btn btn-primary" onclick="window.location=\'' . $cart_page_url . '\';" value="'.Text::_('PLG_MBWAY_CANCEL').'" />';
        $form .= '&nbsp;&nbsp;';
        $form .= '<input id="make-payment" type="button" class="btn btn-warning" value="'.Text::_('PLG_MBWAY_MAKE_PAYMENT').'" />';
        $form .= '</div>';
        $form .= '</form>';
        $form .= '<div style="display: none;" id="timer">
                    '.Text::_('PLG_MBWAY_TIME_LEFT').': <span id="time"></span> 
                    <div id="status">'.Text::_('PLG_MBWAY_PAYMENT_STATUS').': <span id="payment-status">'.Text::_('PLG_MBWAY_PENDING').'</span></div>
                    </div>';
        // lodu loader
        $form .= '<div style="display: none;" class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>';
        // pay check
        $form .= '<div style="display: none;" id="pay-check">
                  <svg width="200" height="200">
                  <circle fill="none" stroke="#4285f4" stroke-width="10" cx="100" cy="100" r="90" class="circle"
                  stroke-linecap="round" transform="rotate(-90 100 100)" />
                  <polyline fill="none" stroke="#4285f4" stroke-width="10" points="44,107 87,142 152,69"
                  stroke-linecap="round" stroke-linejoin="round" class="tick" />
                  </svg>
                  <h2>'.Text::_('PLG_MBWAY_PAYMENT_STATUS').'</h2>
                  <audio style="display: none;" id="myAudio" controls>
                  <source src="' . JURI::base(true) . '/plugins/gurupayment/mbway/mbwayaudio.mp3" type="audio/mpeg">
                  </audio>
                  </div>
        ';
        return $form;
    }

    function onReceivePayment($post) 
	{

		if($post['processor'] != 'mbway'){
			return 0;
		}
	
		$order_id = $post['orderId'];

        $params = new Registry($post['params']);
        $param['phishingkey'] = $params->get('phishing_key');
        $phishingKey = $param['phishingkey'];
        // match the phishing key
		if($phishingKey != $post['key']){
            return 0;
        }

		$db = JFactory::getDbo();
                $sql = "SELECT `userid`, `amount`, `amount_paid` FROM #__guru_order WHERE `id`=" . intval($order_id);
                $db->setQuery($sql);
                $order_details = $db->loadAssocList();
		$customer_id = $order_details["0"]["userid"];
		$gross_amount = $order_details["0"]["amount"];
		
		if($order_details["0"]["amount_paid"] != -1){
			$gross_amount = $order_details["0"]["amount_paid"];
		}
		
		require_once(JPATH_SITE . '/components/com_guru/models/gurubuy.php');
		$guru_buy_model = new guruModelguruBuy();
		$submit_array = array("customer_id" => intval($customer_id), "order_id" => intval($order_id), "price" => $gross_amount);

		$guru_buy_model->proccessSuccess("guruBuy", $submit_array, false);
		
		//in case of cancellation
		//$guru_buy_model->proccessFail("guruBuy", $submit_array);
		
	}
    
    // create a string of parameters
    function DotPayArray2Url($param){
		foreach($param AS $k => $v){
			$out[] = "$k=$v";
		}
		return implode('&', $out );
	}

}

?>