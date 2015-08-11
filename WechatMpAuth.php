<?php

namespace xj\oauth;

use yii\authclient\OAuth2;

/**
 * Weixin 开放平台
 * @author xjflyttp <xjflyttp@gmail.com>
 * @see http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html
 */
class WechatMpAuth extends OAuth2 implements IAuth
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

}

