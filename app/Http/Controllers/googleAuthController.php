<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class googleAuthController extends Controller
{
    public function auth() {
        $authUrl = $this->generateAuthLink();
        return response()->json($authUrl);
    }

    public function handleServiceResponse(Request $request) {
        $tmpCode = $request->code;
        if (!empty($tmpCode)) {
            $tokenData = $this->getTokenByCode($tmpCode);
            if (!empty($tokenData['access_token'])) {
                $userData = $this->getUserData($tokenData);
                if (!User::where('email', $userData['email'])->exists()) {
                    //Если пользователя с таким email нет в базе, то создаем нового
                    $newUser = User::create([
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                        'password' => bcrypt($userData['email'].$userData['name']),
                    ]);
                    $userId = $newUser->id;
                } else {
                    $existedUser = User::where('email', $userData['email'])->first();
                    $userId = $existedUser->id;
                }
            }
        }

        $comeBackUrl = getenv('FRONT_APP_LINK').'?userId='.$userId. '&authorized=Y';
        return redirect($comeBackUrl);
    }

    private function generateAuthLink() {
        $params = array(
            'client_id'     => getenv('GOOGLE_CLIENT_ID'),
            'redirect_uri'  => getenv('GOOGLE_REDIRECT_URI'),
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
            'client_id'     => getenv('GOOGLE_CLIENT_ID'),
            'client_secret' => getenv('GOOGLE_CLIENT_SECRET'),
            'redirect_uri'  => getenv('GOOGLE_REDIRECT_URI'),
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
