<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DigestUnsubscribeController extends Controller
{
    public function __invoke(Request $request, User $user): View
    {
        if (! $request->hasValidSignature()) {
            abort(403);
        }

        $user->forceFill(['digest_opt_out' => true])->save();

        return view('digest.unsubscribed', [
            'user' => $user,
        ]);
    }
}
