<?php

namespace xj\oauth;

use yii\authclient\OAuth2;

/**
 * Renren OAuth
 * @author light <light-li@hotmail.com>
 * @see http://wiki.dev.renren.com/wiki/Authentication
 */
class RenrenAuth extends OAuth2 implements IAuth {

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
     * Try to use getUserAttributes to get simple user info
     * @see http://wiki.dev.renren.com/wiki/Authentication
     *
     * @inheritdoc
     */
    protected function initUserAttributes() {
        return $this->getAccessToken()->getParams()['user'];
    }

    /**
     * Get authed user info
     *
     * @see http://wiki.dev.renren.com/wiki/V2/user/get
     * @return array
     */
    public function getUserInfo() {
        $user = $this->getUserAttributes();
        return $this->api("v2/user/get", 'GET', ['userId' => $user['id']]);
    }

    /**
     * @inheritdoc
     */
    protected function defaultName() {
        return 'renren';
    }

    /**
     * @inheritdoc
     */
    protected function defaultTitle() {
        return 'Renren';
    }

    /**
     * @inheritdoc
     */
    protected function defaultViewOptions() {
        return [
            'popupWidth' => 800,
            'popupHeight' => 500,
        ];
    }

}
