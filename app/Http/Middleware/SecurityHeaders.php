<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class SecurityHeaders
{
 public function handle(Request $request,Closure $next){$response=$next($request);$response->headers->set('X-Content-Type-Options','nosniff');$response->headers->set('X-Frame-Options','SAMEORIGIN');$response->headers->set('Referrer-Policy','strict-origin-when-cross-origin');$response->headers->set('Permissions-Policy','camera=(), microphone=(), geolocation=()');if($request->user()||$request->is('admin/*','investigador/*','postulacion*','profile*','login','register','forgot-password','reset-password*','verify-email*','dashboard'))$response->headers->set('X-Robots-Tag','noindex, nofollow');if($request->routeIs('admin.submissions.download','admin.researcher-applications.cv','researcher.*.download','researcher.publications.pdf','profile.cv.download','researchers.cv','applications.certificate'))$response->headers->set('Cache-Control','private, no-store, max-age=0');return $response;}
}
