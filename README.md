yii2-oauth
==========

OAUTH QQ|WEIBO

composer.json
-----
```json
"require": {
        "xj/yii2-oauth": "*"
},
```

components configure
------
```php
'components' => [
    'authClientCollection' => [
        'class' => 'yii\authclient\Collection',
        'clients' => [
            'qq' => [
                'class' => 'xj\oauth\QqAuth',
                'clientId' => '111',
                'clientSecret' => '111',

            ],
            'sina' => [
                'class' => 'xj\oauth\SinaAuth',
                'clientId' => '111',
                'clientSecret' => '111',
            ],
            'weixin' => [
                'class' => 'xj\oauth\WeixinAuth',
                'clientId' => '111',
                'clientSecret' => '111',
            ],
        ]
    ]
    ...
]
```

Controller
----------
```php
class SiteController extends Controller
{
    public function actions()
    {
        return [
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'successCallback'],
            ],
        ];
    }

    /**
     * Success Callback
     * @param QqAuth|WeiboAuth $client
     * @see http://wiki.connect.qq.com/get_user_info
     * @see http://stuff.cebe.cc/yii2docs/yii-authclient-authaction.html
     */
    public function successCallback($client) {
        $id = $client->getId(); // qq | sina | weixin
        $attributes = $client->getUserAttributes(); // basic info
        $userInfo = $client->getUserInfo(); // user extend info
        var_dump($id, $attributes, $userInfo);
    }
}
```

View
-----------
```php
<?=
yii\authclient\widgets\AuthChoice::widget([
    'baseAuthUrl' => ['site/auth'],
    'popupMode' => false,
])
?>
```
