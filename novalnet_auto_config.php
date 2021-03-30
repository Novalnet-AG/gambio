<?php

/**
 * Novalnet payment module related file
 * This module is used for real time processing of
 * Novalnet transaction of customers.
 *
 * Copyright (c) Novalnet
 *
 * Released under the GNU General Public License
 * This free contribution made by request.
 * If you have found this script useful a small
 * recommendation as well as a comment on merchant form
 * would be greatly appreciated.
 *
 * Script : novalnet_auto_config.php
 *
 */
  
$request = array_map('trim', $_REQUEST);
$config = new AutoConfig();
$config->sendRequest($request);
class AutoConfig {
       
        public function sendRequest($data)
       
        {
           
            $request = http_build_query($data);
            
            $ch = curl_init('https://payport.novalnet.de/autoconfig');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, '240');
            $response = curl_exec($ch);
            curl_close($ch);
           
            $response = json_decode($response);
           
            $jsonerror = json_last_error();

                if (empty($jsonerror))
                {
                    if (isset($response->status) && $response->status == '100') {
                         $merchant_details = array(
                            'auto_key'    => $data['hash'],
                            'vendor_id'   => $response->vendor,
                            'auth_code'   => $response->auth_code,
                            'product_id'  => $response->product,
                            'access_key'  => $response->access_key,
                            'test_mode'   => $response->test_mode,
                            'tariff'      => $response->tariff,
                            'payment_type' => $response->payment,
                            'notify_url'  => $response->notify_url,
                        );
                        echo json_encode($merchant_details);
                        exit();
                    } elseif (isset($response->status) && $response->status == '106') {
                        $result = 'You need to configure your outgoing server IP address ('.$_SERVER['REMOTE_ADDR'].') at Novalnet. Please configure it in Novalnet Admin Portal or contact technic@novalnet.de';
                    } else {
                        $result = !empty($response->config_result) ? $response->config_result : $response->status_desc;
                    }
                    echo json_encode(array('status_desc' => $result));
                    exit();
                }
            
        }
    }

?>

