<?php

//获取微信token, 定时模板消息需要
class WxTokenAction extends Action {


    public function getToken($id) {
        $wiki = M('Wiki')->find($id);
        if (!$wiki) {
            echo "公众号不存！";
            exit;
        }
        $appid = $wiki['appid'];
        $secret = $wiki['secret'];
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . trim($appid) . '&secret=' . trim($secret);
        $result = $this->curlPost($url);

        if ($token = $result['access_token']) {
            echo $token;
        }
     }
     
    private function curlPost($url, $data = array(), $post = false) {
        $curl = curl_init();

        if ($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($curl, CURLOPT_TIMEOUT, 600);

        $result = curl_exec($curl);

        if (curl_errno($curl))
            echo '<pre><b>错误:</b><br />' . curl_error($curl);

        curl_close($curl);

        return json_decode($result, 'array');
    } 
}
