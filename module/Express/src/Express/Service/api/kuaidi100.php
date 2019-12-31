<?php

class kuaidi100ExpressApi {
    /**
     * 公共调用方法
     * @param $expressApi
     * @param $expressNameCode
     * @param $expressNumber
     * @param $httpType
     * @param string $apiType
     * @return array|mixed
     */
    public function stateContent($expressApi, $expressNameCode, $expressNumber, $httpType)
    {
        $array = array();

        if(!empty($expressApi['api_type']) and $expressApi['api_type'] == 'customer') $array = $this->kuaidi100StateCustomerContent($expressApi, $expressNameCode, $expressNumber, $httpType);
        else $array = $this->kuaidi100StateContent($expressApi, $expressNameCode, $expressNumber, $httpType);

        return $array;
    }
    /**
     * 快递100 获取订单配送状态(免费版本)
     * @param $expressApi
     * @param $expressNameCode
     * @param $expressNumber
     * @return mixed
     */
    private function kuaidi100StateContent($expressApi, $expressNameCode, $expressNumber, $httpType)
    {
        $expressUrl = $httpType . 'www.kuaidi100.com/applyurl?key='.$expressApi['api_code'].'&com='.$expressNameCode.'&nu='.$expressNumber;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $expressUrl);
        curl_setopt($curl, CURLOPT_HEADER,0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if($httpType == 'https://') {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_TIMEOUT,5);
        $getContent = curl_exec($curl);
        curl_close($curl);
        $array['api_type'] = 'kuaidi100';

        $data = json_decode($getContent,true);
        if(isset($data['status']) and $data['status'] != 200)
            $array['content'] = $data['message'];
        else {
            if($httpType == 'https://') $getContent = str_replace('http://', 'https://', $getContent);
            $array['content']  = '<iframe src="'.$getContent.'" width="580" height="380"></iframe>';
        }

        return $array;
    }

    /**
     * 快递100 获取订单配送状态(收费版本)
     * @param $expressApi
     * @param $expressNameCode
     * @param $expressNumber
     * @return array
     */
    private function kuaidi100StateCustomerContent($expressApi, $expressNameCode, $expressNumber, $httpType)
    {
        $array = array();
        //参数设置
        $postData = array();
        $key      = $expressApi['api_code'];
        $postData["customer"]   = $expressApi['api_secret'];
        $postData["param"]      = '{"com":"'.$expressNameCode.'","num":"'.$expressNumber.'"}';
        $postData["sign"]       = md5($postData["param"].$key.$postData["customer"]);
        $postData["sign"]       = strtoupper($postData["sign"]);

        $expressUrl = $httpType . 'poll.kuaidi100.com/poll/query.do';

        $o = '';
        foreach ($postData as $k => $v) {
            $o.= "$k=".urlencode($v)."&";
        }
        $postData=substr($o,0,-1);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $expressUrl);
        if($httpType == 'https://') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $result = curl_exec($ch);
        $data = str_replace("\&quot;",'"',$result );
        $data = json_decode($data,true);

        $array['api_type'] = 'kuaidi100';
        if(isset($data['status'])) {
            if($data['status'] == 200) {
                if(is_array($data['data']) and !empty($data['data'])) {
                    $content = '<table style="width: 98%; margin-left: 5px;">';
                    $content .= '<tr><td width="27%" style="padding-left: 8px;background: #64AADB;border: #75C2EF 1px solid;color: #FFFFFF;font-weight: bold;">时间</td>';
                    $content .= '<td width="73%" style="padding-left: 8px;background: #64AADB;border: #75C2EF 1px solid;color: #FFFFFF;font-weight: bold;">地点和跟踪进度</td></tr>';
                    foreach ($data['data'] as $dKey => $value) {
                        $content .= '<tr>';
                        $content .= '<td style="padding-left: 8px;padding-bottom: 5px;padding-top: 5px;border: 1px solid #DDDDDD;'.($dKey == 0 ? 'color:#FF6600;' : '').'">';
                        $content .= $value['ftime'];
                        $content .= '</td>';
                        $content .= '<td style="padding-left: 8px;padding-bottom: 5px;padding-top: 5px;border: 1px solid #DDDDDD;'.($dKey == 0 ? 'color:#FF6600;' : '').'">';
                        $content .= $value['context'];
                        $content .= '</td></tr>';
                    }
                    $content .= '</table>';
                    $array['content'] = $content;
                }
            } else $array['content'] = $data['message'];
        } else $array['content'] = '设置不正确';

        return $array;
    }
}