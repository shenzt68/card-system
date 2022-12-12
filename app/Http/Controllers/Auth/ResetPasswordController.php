<?php
namespace App\Http\Controllers\Auth; use App\Http\Controllers\Controller; use Illuminate\Auth\Events\PasswordReset; use Illuminate\Http\Request; use Illuminate\Http\Response; use Illuminate\Support\Facades\Hash; use Illuminate\Support\Facades\Auth; use Illuminate\Support\Facades\Password; class ResetPasswordController extends Controller { public function reset(Request $sp517903) { $this->validate($sp517903, array('token' => 'required', 'email' => 'required|email', 'password' => 'required|confirmed|min:6')); $sp0223d2 = Password::broker()->reset($sp517903->only('email', 'password', 'password_confirmation', 'token'), function ($sp3db17d, $spe46211) { $this->resetPassword($sp3db17d, $spe46211); }); return $sp0223d2 == Password::PASSWORD_RESET ? response(array()) : response(array('message' => trans($sp0223d2)), 400); } public function change(Request $sp517903) { $this->validate($sp517903, array('old' => 'required|string', 'password' => 'required|string|min:6|max:32|confirmed')); $sp3db17d = Auth::user(); if (!Hash::check($sp517903->post('old'), $sp3db17d->password)) { return response(array('message' => '旧密码错误，请检查'), Response::HTTP_BAD_REQUEST); } $sp5f3012 = $this->resetPassword($sp3db17d, $sp517903->post('password')); return response(array(), 200, array('Authorization' => 'Bearer ' . $sp5f3012)); } public static function resetPassword($sp3db17d, $spe46211, $sp85da8d = true) { $sp3db17d->password = Hash::make($spe46211); $sp3db17d->setRememberToken(time()); $sp3db17d->saveOrFail(); event(new PasswordReset($sp3db17d)); if ($sp85da8d) { return Auth::login($sp3db17d); } else { return true; } } }