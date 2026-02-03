<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SocialAuthController extends Controller
{
    protected $providers = ['google','facebook','linkedin'];

    public function redirect($provider)
    {
        if(!in_array($provider, $this->providers)) abort(404);
        return Socialite::driver($provider)->redirect();
    }

    public function callback(Request $request, $provider)
    {
        if(!in_array($provider, $this->providers)) abort(404);

        $socialUser = Socialite::driver($provider)->stateless()->user();

        $providerIdField = $provider . '_id';

        $user = User::where($providerIdField, $socialUser->id)->first();

        if(!$user && $socialUser->email){
            $user = User::where('email', $socialUser->email)->first();
            if($user){
                $user->{$providerIdField} = $socialUser->id;
                $user->save();
            }
        }

        if(!$user){
            $user = User::create([
                'name' => $socialUser->name ?? $socialUser->nickname ?? 'Usuario',
                'email' => $socialUser->email ?? null,
                $providerIdField => $socialUser->id,
                'password' => bcrypt(bin2hex(random_bytes(8)))
            ]);
        }

        Auth::login($user, true);

        return redirect()->intended('/');
    }
}
