<?php
include 'aliyun-php-sdk-core/Config.php';
include_once 'Dysmsapi/Request/V20170525/SendSmsRequest.php';
include_once 'Dysmsapi/Request/V20170525/QuerySendDetailsRequest.php';

class NewAliyunDbMns {
    private $signName;  //签名
    private $appKey;    //Key
    private $appSecret; //Secret
    private $region;    //区域
    private $domain;    //短信API产品域名
    private $product;   //短信API产品名

    private $endpointName;
    private $regionId;

    /**
     * 发送消息
     * @param $jsonMessage
     * @param $phoneNumbers
     * @param $templateCode
     */
    public function sendMessage($jsonMessage, $phoneNumbers, $templateCode)
    {
        //初始化访问的acsCleint
        $profile = DefaultProfile::getProfile($this->region, $this->appKey, $this->appSecret);
        DefaultProfile::addEndpoint($this->endpointName, $this->regionId, $this->product, $this->domain);
        $acsClient  = new DefaultAcsClient($profile);

        $request   = new Dysmsapi\Request\V20170525\SendSmsRequest();
        //必填-短信接收号码，多个号码用英文逗号分隔
        $request->setPhoneNumbers($phoneNumbers);
        //必填-短信签名
        $request->setSignName($this->signName);
        //必填-短信模板Code
        $request->setTemplateCode($templateCode);
        //选填-假如模板中存在变量需要替换则为必填(JSON格式)
        $request->setTemplateParam($jsonMessage);
        //选填-发送短信流水号
        $request->setOutId(time());

        //发起访问请求
        $acsResponse = $acsClient->getAcsResponse($request);
        //var_dump($acsResponse);
    }
    /**
     * 设置签名名称
     * @param $signName
     */
    public function setSignName($signName)
    {
        $this->signName = $signName;
    }
    /**
     * 设置 Key
     * @param $appTopic
     */
    public function setAppKey($appKey)
    {
        $this->appKey = $appKey;
    }
    /**
     * 设置 Secret
     * @param $appTopic
     */
    public function setAppSecret($appSecret)
    {
        $this->appSecret = $appSecret;
    }
    /**
     * 设置区域
     * @param $region
     */
    public function setRegion($region)
    {
        $this->region = $region;
    }
    /**
     * 短信API产品域名
     * @param $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }
    /**
     * 短信API产品名
     * @param $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }
    /**
     *
     * @param $endpointName
     */
    public function setEndpointName($endpointName)
    {
        $this->endpointName = $endpointName;
    }
    /**
     *
     * @param $regionId
     */
    public function setRegionId($regionId)
    {
        $this->regionId = $regionId;
    }
}