<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

$user = DB::table('users')->where('email', 'pablo@example.com')->first();
if (! $user) { echo "user not found\n"; exit(1); }
$token = JWTAuth::fromUser(\App\Models\User::find($user->id));

// create a request to the application route
$request = Request::create('/api/channels/9/with-references', 'GET');
$request->headers->set('Authorization', 'Bearer ' . $token);

// run through the app
$response = $app->handle($request);

echo "status: " . $response->getStatusCode() . PHP_EOL;
echo $response->getContent() . PHP_EOL;
