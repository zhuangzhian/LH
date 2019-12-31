<?php
require_once('mns-autoloader.php');

use AliyunMNS\Client;
use AliyunMNS\Topic;
use AliyunMNS\Constants;
use AliyunMNS\Model\MailAttributes;
use AliyunMNS\Model\SmsAttributes;
use AliyunMNS\Model\BatchSmsAttributes;
use AliyunMNS\Model\MessageAttributes;
use AliyunMNS\Exception\MnsException;
use AliyunMNS\Requests\PublishMessageRequest;

class AliyunDbMns {

    private $signName;
    private $appTopic;
    private $appKey;
    private $appSecret;
    private $appEndpoint;

    private $tagArray;

    /**
     * 发送消息
     * @param $message
     * @param $phoneNumberArray
     * @param $templateCode
     * @return bool
     */
    public function sendMessage($message, $phoneNumberArray, $templateCode)
    {
        $client = new Client($this->appEndpoint, $this->appKey, $this->appSecret);
        $topic  = $client->getTopicRef($this->appTopic);

        $batchSmsAttributes = new BatchSmsAttributes($this->signName, $templateCode);
        foreach ($phoneNumberArray as $phoneNumber) {
            $batchSmsAttributes->addReceiver($phoneNumber, $this->tagArray);
        }
        $messageAttributes = new MessageAttributes(array($batchSmsAttributes));
        $request = new PublishMessageRequest($message, $messageAttributes);
        try
        {
            $res = $topic->publishMessage($request);
            return true;
            /*$res = $topic->publishMessage($request);
            echo $res->isSucceed();
            echo "\n";
            echo $res->getMessageId();
            echo "\n";*/
        }
        catch (MnsException $e)
        {
            return false;
            /*echo $e;
            echo "\n";*/
        }

    }
    /**
     * 设置短信标签内容
     * @param $messageArray
     */
    public function setMnsTag($tagArray)
    {
        $this->tagArray = $tagArray;
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
     * 设置主题名称
     * @param $appTopic
     */
    public function setAppTopic($appTopic)
    {
        $this->appTopic = $appTopic;
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
     * 设置接入地址
     * @param $appTopic
     */
    public function setAppEndpoint($appEndpoint)
    {
        $this->appEndpoint = $appEndpoint;
    }
}