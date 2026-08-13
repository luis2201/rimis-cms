<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\{RedirectResponse,Request};
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
class TemporaryPasswordController extends Controller
{
    public function edit():View{return view('auth.change-temporary-password');}
    public function update(Request $r):RedirectResponse{$data=$r->validate(['password'=>['required','confirmed',Password::defaults()]]);$r->user()->forceFill(['password'=>Hash::make($data['password']),'must_change_password'=>false,'remember_token'=>null])->save();return redirect()->route('dashboard')->with('success','Contraseña actualizada.');}
}
