<?php

namespace app\controllers;

use Yii;
use yii\caching\Cache;
use yii\web\Controller;
use yii\httpclient\Client;
use yii\helpers\Json;
use yii\helpers\BaseJson;
use yii\helpers\ArrayHelper;
use app\models\Yandex;

class WeatherController extends Controller
{
    public function requestWeatherData()
    {
        $yandex_params = Yandex::find()->asArray()->all();
        $baseUrl = ArrayHelper::getValue($yandex_params, '0.val');
        $setUrl = ArrayHelper::getValue($yandex_params, '7.val');
        $key = ArrayHelper::getValue($yandex_params, '3.val');
        $latitude = ArrayHelper::getValue($yandex_params, '4.val');
        $longitude = ArrayHelper::getValue($yandex_params, '6.val');
        $limit = ArrayHelper::getValue($yandex_params, '5.val');
        $hours = ArrayHelper::getValue($yandex_params, '2.val');
        $extra = ArrayHelper::getValue($yandex_params, '1.val');

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
        if ($response->statusCode != "200")
            throw new \Exception("text");
        return $response->content;        


    }
	public function actionCurrent()
    {
        $cache = Yii::$app->cache;

        $data = $cache->getOrSet('dataCache', function () {
            return $this->requestWeatherData();
        }, 30);

        $data = Json::decode($data);

        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $response->data = array(
            'status' => 'ok',
            'temperature' => $data['fact']['temp'],
            'condition' => $data['fact']['condition']
            );

        return $response;
    }
}
