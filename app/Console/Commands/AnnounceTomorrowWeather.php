<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AnnounceTomorrowWeather extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:tomorrows_weather';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Announce tomorrows weather';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $app_id = env('OPEN_WEATHER_MAP_API_KEY', false);
        $url = 'http://api.openweathermap.org/data/2.5/forecast?id=1850147&cnt=11&lang=ja&units=metric&APPID=' . $app_id;
        $response_json = file_get_contents($url); //外部サイトにあるjsonなどを取得する
        $response = json_decode($response_json, true);  // JSONデータを配列にする
    
        $weather =array();
        $temp = array();
        
        foreach($response['list'] as $weather_at_time){
            $weather[] = $weather_at_time['weather'][0]['description'];
            $temp[] = (int)$weather_at_time['main']['temp'];
        }
        
        $message = '明日の天気' . PHP_EOL . '朝：' . $weather[6] . ', ' . $temp[6] . '℃' . PHP_EOL . '昼：' . $weather[8] . ', '. $temp[8] . '℃'. PHP_EOL . '夜：' . $weather[10] . ', '.$temp[10] . '℃';
        
        $unnatural = array('厚い雲', '適度な雨');
        $natural = array('くもり', '雨');
        
        $message = str_replace($unnatural, $natural, $message);

        
        $authorization = "Authorization: Bearer " . env('LINE_ACCESS_TOKEN');
        
        $headers = array(
            "Content-Type:application/json",
            $authorization,
        );
        
        $params = array(
            'messages' => array(
                array(
                    'type' => 'text',
                    'text' => $message
                )
            )
        );
        
        $params = json_encode($params);
        
    
        $line_url = 'https://api.line.me/v2/bot/message/broadcast';
        
        //cURLセッションを初期化する
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $line_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        
        $line_response =  curl_exec($ch);
        
        //セッションを終了する
        curl_close($ch);
    }
}
