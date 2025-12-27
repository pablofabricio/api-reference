<?php
// Simple script to check channels visible to a given user by generating a token and calling the service.
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;

$user = DB::table('users')->where('email', 'pablo@example.com')->first();
if (! $user) {
    echo "user not found\n";
    exit(1);
}

// Create token for user using jwt-auth user model
$token = JWTAuth::fromUser(\App\Models\User::find($user->id));

echo "token: $token\n";
// set token in the JWTAuth instance so middleware/guards can read it
JWTAuth::setToken($token);

// Set Auth facade user as well
Illuminate\Support\Facades\Auth::setUser(\App\Models\User::find($user->id));

$svc = app('App\\Services\\ChannelService');
$p = $svc->getPaginateForUser();

echo "total: " . $p->total() . "\n";
foreach ($p->items() as $c) {
    echo $c->id . ' ' . $c->name . ' created_by=' . $c->created_by . PHP_EOL;
}
