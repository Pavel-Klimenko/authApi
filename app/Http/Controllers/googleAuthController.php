<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class googleAuthController extends Controller
{
    CONST CLIENT_ID = '574736163037-js2tmlaa7spl0a08rfp9mvjdgq0m7iai.apps.googleusercontent.com';
    CONST CLIENT_SECRET = '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';
    CONST REDIRECT_URI = 'https://authapi.biz/login_google/';

    public function auth() {
        $authUrl = $this->generateAuthLink();
        return redirect($authUrl);
    }

    public function handleServiceResponse(Request $request) {
        $tmpCode = $request->code;
        if (!empty($tmpCode)) {
            //dump($tmpCode);
            $tokenData = $this->getTokenByCode($tmpCode);

            //dump($tokenData);

            if (!empty($tokenData['access_token'])) {
                $userData = $this->getUserData($tokenData);

                //dump($userData);

                if (!User::where('email', $userData['email'])->exists()) {
                    //Если пользователя с таким email нет в базе, то создаем нового
                    $newUser = User::create([
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                        'password' => bcrypt($userData['email'].$userData['name']),
                    ]);
                    $result = ['status' => 'new', 'message' => 'Создан новый пользователь', 'email' => $newUser->email, 'name' => $newUser->name];
                } else {
                    $existedUser = User::where('email', $userData['email'])->first();
                    $result = ['status' => 'existing', 'message' => 'Пользователь с email '.$existedUser->email.' уже существует'];
                }
            }

            //dump($result);
        }

        if (!isset($result)) return redirect('/');
        return view('result', compact('result'));
    }

    private function generateAuthLink() {
        $params = array(
            'client_id'     => self::CLIENT_ID,
            'redirect_uri'  => self::REDIRECT_URI,
            'response_type' => 'code',
            'scope'         => 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
        );
        return 'https://accounts.google.com/o/oauth2/auth?' . urldecode(http_build_query($params));
    }


    private function getUserData($tokenData) {
        $params = array(
            'access_token' => $tokenData['access_token'],
            'id_token'     => $tokenData['id_token'],
            'token_type'   => 'Bearer',
            'expires_in'   => 3599
        );

        $userInfo = file_get_contents('https://www.googleapis.com/oauth2/v1/userinfo?' . urldecode(http_build_query($params)));
        return json_decode($userInfo, true);
    }


    private function getTokenByCode($tmpCode) {
        $params = array(
            'client_id'     => self::CLIENT_ID,
            'client_secret' => self::CLIENT_SECRET,
            'redirect_uri'  => self::REDIRECT_URI,
            'grant_type'    => 'authorization_code',
            'code'          => $tmpCode
        );

        $ch = curl_init('https://accounts.google.com/o/oauth2/token');
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
