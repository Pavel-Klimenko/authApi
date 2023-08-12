<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class yandexAuthController extends Controller
{
    public function auth() {
        $authUrl = $this->generateAuthLink();
        return response()->json($authUrl);
    }

    public function handleServiceResponse(Request $request) {
        $tmpCode = $request->code;
        if (!empty($tmpCode)) {
            //dump($yandexTmpCode);
            $tokenData = $this->getTokenByCode($tmpCode);

            if (!empty($tokenData['access_token'])) {
                $userData = $this->getUserData($tokenData['access_token']);

                if (!User::where('email', $userData['default_email'])->exists()) {
                    //Если пользователя с таким email нет в базе, то создаем нового
                    $newUser = User::create([
                        'name' => $userData['real_name'],
                        'email' => $userData['default_email'],
                        'password' => bcrypt($userData['default_email'].$userData['real_name']),
                    ]);
                    $userId = $newUser->id;
                } else {
                    $existedUser = User::where('email', $userData['default_email'])->first();
                    $userId = $existedUser->id;
                }
            }
        }

        $comeBackUrl = getenv('FRONT_APP_LINK').'?userId='.$userId. '&authorized=Y';
        return redirect($comeBackUrl);
    }

    private function generateAuthLink() {
        $params = array(
            'client_id'     => getenv('YANDEX_CLIENT_ID'),
            'redirect_uri'  => getenv('YANDEX_REDIRECT_URI'),
            'response_type' => 'code',
        );
        return 'https://oauth.yandex.ru/authorize?' . urldecode(http_build_query($params));
    }

    /**Получение данных пользователя Яндекс
     *
     * @param $accessToken
     * @return mixed
     */
    private function getUserData($accessToken) {
        $ch = curl_init('https://login.yandex.ru/info');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('format' => 'json'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: OAuth ' . $accessToken));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $userInfo = curl_exec($ch);
        curl_close($ch);

        return json_decode($userInfo, true);
    }

    /**Получение токена по проверочному коду
     *
     * @param $yandexTmpCode
     * @return mixed
     */
    private function getTokenByCode($yandexTmpCode) {
        $params = array(
            'grant_type'    => 'authorization_code',
            'code'          => $yandexTmpCode,
            'client_id'     => getenv('YANDEX_CLIENT_ID'),
            'client_secret' => getenv('YANDEX_CLIENT_SECRET'),
        );

        $ch = curl_init('https://oauth.yandex.ru/token');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $data = curl_exec($ch);
        curl_close($ch);

        return json_decode($data, true);
    }
}
