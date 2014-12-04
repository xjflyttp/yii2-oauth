<?php
namespace xj\oauth;
use yii\authclient\OAuth2;
/**
 * Renren OAuth
 * @author light <light-li@hotmail.com>
 */
class RenrenAuth extends OAuth2 implements IAuth
{
    /**
     * @inheritdoc
     */
    public $authUrl = 'https://graph.renren.com/oauth/authorize';
    /**
     * @inheritdoc
     */
    public $tokenUrl = 'https://graph.renren.com/oauth/token';
    /**
     * @inheritdoc
     */
    public $apiBaseUrl = 'https://api.renren.com';
    /**
     * Get authed user info
     *
     * @return array
     */
    public function getUserInfo()
    {
        return $this->getAccessToken()->getParams()['user'];
    }
    /**
     * @inheritdoc
     */
    protected function defaultName()
    {
        return 'renren';
    }
    /**
     * @inheritdoc
     */
    protected function defaultTitle()
    {
        return 'Renren';
    }
    /**
     * @inheritdoc
     */
    protected function defaultViewOptions()
    {
        return [
            'popupWidth' => 800,
            'popupHeight' => 500,
        ];
    }
}