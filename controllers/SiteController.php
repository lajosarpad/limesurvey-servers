<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\Server;
use app\models\Plugin;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'os' => Server::getSupportedOperatingSystems(),
            'versions' => Plugin::getSupportedVersionsJSON()
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function beforeAction($action)
    {
        if (array_search($action->id, ['generate-test-data', 'distribute-servers']) !== false) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    /**
     * generates test data
     * 
     * @return string
     */
    public function actionGenerateTestData()
    {
        $serverCount = intval((Yii::$app->request->post('servers')));
        $pluginCount = intval((Yii::$app->request->post('plugins')));
        $availableServers = [Server::nothing, Server::CentOS, Server::RedHat, Server::Ubuntu];
        $availableVersions = [Plugin::v_1_2, Plugin::v_1_3, Plugin::v_2, Plugin::latest];
        $servers = [];
        while ($serverCount--) {
            $servers[]=Server::createServer(rand(64, 8192), $availableServers[rand(0, count($availableServers) - 1)])->getFriendlyArray();
        }
        $totalPlugins = $pluginCount;
        while ($pluginCount--) {
            $theServers = [];
            for ($i = 1; $i < count($availableServers); $i++) {
                if (rand(0, 1) > 0) $theServers[]=$availableServers[$i];
            }
            if (!count($theServers)) $theServers[]=$availableServers[rand(1, count($availableServers) - 1)];
            $theVersions = [];
            for ($i = 0; $i < count($availableVersions); $i++) {
                if (rand(0, 1) > 0) $theVersions[]=$availableVersions[$i];
            }
            if (!count($theVersions)) $theVersions[]=$availableVersions[rand(0, count($availableVersions) - 1)];
            $plugins[]=Plugin::createPlugin("Plugin" . $totalPlugins - $pluginCount, $theVersions, $theServers, rand(1, 100))->getFriendlyArray();
        }

        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $response->data = [
            'servers' => $servers,
            'plugins' => $plugins
        ];
    
        return $response;
    }

    /**
     * distribute test server
     * 
     * @return string
     */
    public function actionDistributeServers()
    {
        $data = json_decode(base64_decode(Yii::$app->request->post('data')), true);
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $response->data = Server::findBestDistribution($data['servers'], $data['plugins']);

        return $response;
    }

}
