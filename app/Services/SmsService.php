<?php
namespace App\Services;

class SmsService {

    private $username = "u09055251197";
    private $password = "Faraz@2198120016782461"; 
    private $from = "+983000505";   

    public function sendBulk(array $phones, string $message) {
        // حذف شماره‌های تکراری و خالی
        $phones = array_unique(array_filter($phones));
        
        if (empty($phones)) {
            return false;
        }

        $url = "https://ippanel.com/services.jspd";
        
        $param = array(
            'uname' => $this->username,
            'pass' => $this->password,
            'from' => $this->from,
            'message' => $message,
            'to' => json_encode(array_values($phones)), 
            'op' => 'send'
        );

        $handler = curl_init($url);
        curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($handler, CURLOPT_POSTFIELDS, $param);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($handler);
        curl_close($handler);

        return $response;
    }
}