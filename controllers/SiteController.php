<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use yii\httpclient\Client;
use yii\helpers\Json;
use yii\helpers\BaseJson;
use app\models\Yandex;

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
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
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

    public function actionWeather()
    {
        $yandex_params = Yandex::find()->asArray()->all();
        $baseUrl = array_values($yandex_params[0])[1];
        $setUrl = array_values($yandex_params[7])[1];
        $key = array_values($yandex_params[3])[1];
        $latitude = array_values($yandex_params[4])[1];
        $longitude = array_values($yandex_params[6])[1];
        $limit = array_values($yandex_params[5])[1];
        $hours = array_values($yandex_params[2])[1];
        $extra = array_values($yandex_params[1])[1];


        $client = new Client(['baseUrl' => $baseUrl, 
            'requestConfig' => ['format' => Client::FORMAT_JSON],
            'responseConfig' => ['format' => Client::FORMAT_JSON],
            'parsers' => [Client::FORMAT_JSON => ['class' => 'yii\httpclient\JsonParser', 'asArray' => true]]
        ]);

        $response = $client->createRequest()
            ->setUrl($setUrl)
            ->addOptions(['lat' => $latitude, 'lon' => $longitude, 'limit' => $limit, 'hours' => $hours, 'extra' => $extra])
            ->addHeaders(['X-Yandex-API-Key' => $key])
            ->send();
        echo 'current temperature: '.Json::decode($response->content, true)['fact']['temp'];
        echo '</br>current condition: '.Json::decode($response->content, true)['fact']['condition'];
        die;
    }
    
}

