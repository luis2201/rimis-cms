<?php
namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;
class HealthController extends Controller
{
 public function __invoke():JsonResponse{try{DB::select('select 1');return response()->json(['status'=>'ok','application'=>'RIMIS','environment'=>app()->environment()],200);}catch(Throwable){return response()->json(['status'=>'unavailable','application'=>'RIMIS'],503);}}
}
