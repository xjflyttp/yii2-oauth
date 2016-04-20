<?php

namespace xj\oauth;

use xj\oauth\exception\WeixinException;
use xj\oauth\weixin\models\MpUserInfoResult;
use Yii;
use xj\oauth\weixin\models\MpTicketResult;
use xj\oauth\weixin\models\MpAccessTokenResult;
use xj\oauth\exception\WeixinAccessTokenException;
use xj\oauth\exception\WeixinTicketException;
use yii\authclient\OAuth2;
use yii\authclient\OAuthToken;
use yii\base\Exception;

/**
 * Weixin 开放平台
 * @author xjflyttp <xjflyttp@gmail.com>
 * @see http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html
 */
class WeixinMpAuth extends OAuth2 implements IAuth
{

    public $authUrl = 'https://open.weixin.qq.com/connect/oauth2/authorize';
    public $tokenUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token';
    public $apiBaseUrl = 'https://api.weixin.qq.com';
    public $scope = 'snsapi_base';

    /**
     * Composes user authorization URL.
     * @param array $params additional auth GET params.
     * @return string authorization URL.
     */
    public function buildAuthUrl(array $params = [])
    {
        $defaultParams = [
            'appid' => $this->clientId,
            'redirect_uri' => $this->getReturnUrl(),
            'response_type' => 'code',
        ];
        if (!empty($this->scope)) {
            $defaultParams['scope'] = $this->scope;
        }

        return $this->composeUrl($this->authUrl, array_merge($defaultParams, $params));
    }

    /**
     * Fetches access token from authorization code.
     * @param string $authCode authorization code, usually comes at $_GET['code'].
     * @param array $params additional request params.
     * @return OAuthToken access token.
     */
    public function fetchAccessToken($authCode, array $params = [])
    {
        $defaultParams = [
            'appid' => $this->clientId,
            'secret' => $this->clientSecret,
            'code' => $authCode,
            'grant_type' => 'authorization_code',
        ];
        $response = $this->sendRequest('POST', $this->tokenUrl, array_merge($defaultParams, $params));
        $token = $this->createToken(['params' => $response]);
        $this->setAccessToken($token);
        return $token;
    }

    /**
     * @inheritdoc
     */
    protected function apiInternal($accessToken, $url, $method, array $params, array $headers)
    {
        $params['access_token'] = $accessToken->getToken();
        $params['openid'] = $this->getOpenid();
        return $this->sendRequest($method, $url, $params, $headers);
    }

    /**
     *
     * @return []
     */
    protected function initUserAttributes()
    {
        $tokenParams = $this->getAccessToken()->params;
        return [
            'openid' => isset($tokenParams['openid']) ? $tokenParams['openid'] : '',
            'unionid' => isset($tokenParams['unionid']) ? $tokenParams['unionid'] : '',
        ];
    }

    /**
     * You must have grant scope=snsapi_userinfo
     * @return []
     * @see https://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html
     */
    public function getUserInfo()
    {
        return $this->api('sns/userinfo', 'GET', ['openid' => $this->getOpenid()]);
    }

    /**
     * @return string
     */
    public function getOpenid()
    {
        $attributes = $this->getUserAttributes();
        return $attributes['openid'];
    }

    protected function defaultName()
    {
        return 'weixin-mp';
    }

    protected function defaultTitle()
    {
        return 'WeixinMp';
    }

    /**
     * 获取公众号AccessToken
     * @return MpAccessTokenResult
     * @throws WeixinAccessTokenException
     */
    public function getMpAccessToken()
    {
        try {
            $result = $this->sendRequest('GET', $this->apiBaseUrl . '/cgi-bin/token', [
                'grant_type' => 'client_credential',
                'appid' => $this->clientId,
                'secret' => $this->clientSecret,
            ]);
            return new MpAccessTokenResult($result);
        } catch (Exception $e) {
            throw new WeixinAccessTokenException($e->getMessage(), $e->getCode());
        }

    }

    /**
     * 获取jsapi|wx_card Ticket
     * @param string $accessToken
     * @param string $type jsapi|wx_card
     * @return MpTicketResult
     * @throws WeixinTicketException
     */
    public function getTicket($accessToken, $type = 'jsapi')
    {
        try {
            $result = $this->sendRequest('GET', $this->apiBaseUrl . '/cgi-bin/ticket/getticket', [
                'access_token' => $accessToken,
                'type' => $type,
            ]);
            return new MpTicketResult($result);
        } catch (Exception $e) {
            throw new WeixinTicketException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param $openid
     * @param string $lang
     * @return MpUserInfoResult
     * @throws Exception
     * @see http://mp.weixin.qq.com/wiki/14/bb5031008f1494a59c6f71fa0f319c66.html
     */
    public function getUserInfoByOpenid($openid, $lang = 'zh_CN')
    {
        try {
            $params = [
                'openid' => $openid,
                'lang' => $lang,
            ];
            $result = $this->api('cgi-bin/user/info', 'GET', $params);
            return new MpUserInfoResult($result);
        } catch (Exception $e) {
            throw new WeixinException($e->getMessage(), $e->getCode());
        }
    }
}

