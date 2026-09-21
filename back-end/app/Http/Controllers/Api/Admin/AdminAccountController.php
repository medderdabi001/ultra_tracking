<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminAccountController extends Controller
{
    /**
     * Create a new Account along with its primary Manager (Gérant).
     */
    public function store(Request $request)
    {
        $request->validate([
            'account_name' => 'required|string|max:255',
            'type' => 'required|in:individual,company',
            'manager_name' => 'required|string|max:255',
            'manager_email' => 'required|email|unique:users,email',
            'manager_password' => 'required|string|min:6',
        ]);

        // Wrap operations in DB Transaction for data integrity
        return DB::transaction(function () use ($request) {

            // 1. Create the Account
            $account = Account::create([
                'name' => $request->account_name,
                'type' => $request->type,
                'status' => 'active',
                'created_by' => auth()->id(), // Admin ID from Sanctum
            ]);

            // 2. Create the primary Manager user for this account
            $manager = User::create([
                'account_id' => $account->id,
                'name' => $request->manager_name,
                'email' => $request->manager_email,
                'password' => Hash::make($request->manager_password),
                'role' => 'gerant',
                'status' => 'active',
            ]);

            return response()->json([
                'message' => 'Account and Manager created successfully',
                'account' => $account,
                'manager' => $manager,
            ], 201);
        });
    }
}
