<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller

{
    public function get_role(){
        $data = Role::all();
        return response()->json([
            'status' => true,
            'message' => 'Roles retrieved successfully',
            'data' => $data
        ]);
      }

    // create raters account
    // public function Raters_register(Request $request)
    // {


    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'username' => 'required|string|max:255|unique:users,username',
    //         'job_batches_rsp_id' => 'required|array',
    //         'job_batches_rsp_id.*' => 'exists:job_batches_rsp,id',
    //         'position' => 'required|string|max:255',
    //         'office_id' => 'required|integer',
    //         'password' => 'required|string|min:5',
    //     ]);

    //     $user = User::create([
    //         'name' => $validated['name'],
    //         'username' => $validated['username'],
    //         'position' => $validated['position'],
    //         'office_id' => $validated['office_id'],
    //         'password' => Hash::make($validated['password']),
    //         'role_id' => 2,
    //         'remember_token' => Str::random(32)
    //     ]);

    //     $user->job_batches_rsp()->attach($validated['job_batches_rsp_id']);

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Rater Registered Successfully',
    //         'data' => $user->load('job_batches_rsp')
    //     ], 201);
    // }
    public function Raters_register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'sometimes|string|max:255|unique:users,username',
            'job_batches_rsp_id' => 'required|array',
            'job_batches_rsp_id.*' => 'exists:job_batches_rsp,id',
            'position' => 'required|string|max:255',
            'office' => 'required|string|max:255',
            'password' => 'required|string|min:5',
        ]);

        // Generate username from name if not provided
        if (empty($validated['username'])) {
            $validated['username'] = strtolower(str_replace(' ', '', $validated['name']));
        }

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['name'],
            'position' => $validated['position'],
            'office' => $validated['office'],
            'password' => Hash::make($validated['password']),
            'role_id' => 2, // Assuming 2 is for raters
            'remember_token' => Str::random(32)
        ]);

        // Attach job batches
        $user->job_batches_rsp()->attach($validated['job_batches_rsp_id']);
        return response()->json([
            'status' => true,
            'message' => 'Rater Registered Successfully',
            'data' => $user->load('job_batches_rsp')
        ], 201);
    }

    // create account
    public function Token_Register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users|max:255',
            'password' => 'required|string|min:3',
            'position' => 'required|string|max:255',
            'active' => 'required|boolean',

            // Optional permission flags
            'permissions.isFunded' => 'boolean',
            'permissions.isUserM' => 'boolean',
            'permissions.isRaterM' => 'boolean',
            'permissions.isCriteria' => 'boolean',
            'permissions.isDashboardStat' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $validatedData['name'],
                'username' => $validatedData['username'],
                'password' => Hash::make($validatedData['password']),
                'position' => $validatedData['position'],
                'active' => $validatedData['active'],
                'role_id' => 1,
                'remember_token' =>Str::random(32)
            ]);

            if ($request->has('permissions')) {
                $user->rspControl()->create([
                    'isFunded' => $request->input('permissions.isFunded', false),
                    'isUserM' => $request->input('permissions.isUserM', false),
                    'isRaterM' => $request->input('permissions.isRaterM', false),
                    'isCriteria' => $request->input('permissions.isCriteria', false),
                    'isDashboardStat' => $request->input('permissions.isDashboardStat', false),
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'User Registered Successfully',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Registration Failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    // Login
    public function Token_Login(Request $request)
    {
        // First check if username and password are provided
        if (empty($request->username) || empty($request->password)) {
            return response([
                'status' => false,
                'message' => 'Invalid Credentials',
                'errors' => [
                    'username' => empty($request->username) ? ['Please enter username'] : [],
                    'password' => empty($request->password) ? ['Please enter password'] : []
                ]
            ], 401);
        }

        $user = User::where('username', $request->username)->first();
        if (!$user) {
            return response([
                'status' => false,
                'message' => 'Invalid Credentials',
                'errors' => [
                    'username' => ['Username does not exist'],
                    'password' => ['Please enter password']
                ]
            ], 401);
        }

        // Then check if the password is correct
        if (!Hash::check($request->password, $user->password)) {
            return response([
                'status' => false,
                'message' => 'Invalid Credentials',
                'errors' => [
                    'password' => ['Wrong password']
                ]
            ], 401);
        }

        // Only allow users with role_id == 1
        if ($user->role_id != 1) {
            return response([
                'status' => false,
                'message' => 'Access Denied: You do not have permission to login.',
                'errors' => [
                    'role_id' => ['Only users admin can login.']
                ]
            ], 403);
        }

        // Authenticate the user
        Auth::login($user);

        $user = Auth::user();

        // Check if the user is active
        if (!$user->active) {
            return response([
                'status' => false,
                'message' => 'Your account is inactive. Please contact the administrator.',
            ], 403);
        }

        // Generate a token for the user
        $token = $user->createToken('my-secret-token')->plainTextToken;

        // Set the token in a secure cookie
        $cookie = cookie('admin_token', $token, 60 * 24, null, null, true, true, false, 'None');

        return response([
            'status' => true,
            'message' => 'Login Successfully',
            'user' => [
                'name' => $user->name,
                'position' => $user->position,
                'role_id' => (int)$user->role_id, // Always integer
            ],
            'token' => $token,
        ])->withCookie($cookie);
    }


    // logout
    public function Token_Logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $user->tokens()->delete();
        }

        $cookie = cookie()->forget('admin_token');

        return response([
            'status' => true,
            'message' => 'Logout Successfully',
        ])->withCookie($cookie);
    }


    public function Rater_logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $user->tokens()->delete();
        }

        $cookie = cookie()->forget('rater_token');

        return response([
            'status' => true,
            'message' => 'Logout Successfully',
        ])->withCookie($cookie);
    }


    // login
    public function Raters_Login(Request $request)
    {
        // First check if username and password are provided
        if (empty($request->username) || empty($request->password)) {
            return response([
                'status' => false,
                'message' => 'Invalid Credentials',
                'errors' => [
                    'username' => empty($request->username) ? ['Please enter username'] : [],
                    'password' => empty($request->password) ? ['Please enter password'] : []
                ]
            ], 401);
        }

        $user = User::where('username', $request->username)->first();
        if (!$user) {
            return response([
                'status' => false,
                'message' => 'Invalid Credentials',
                'errors' => [
                    'username' => ['Username does not exist'],
                    'password' => ['Please enter password']
                ]
            ], 401);
        }

        // Then check if the password is correct
        if (!Hash::check($request->password, $user->password)) {
            return response([
                'status' => false,
                'message' => 'Invalid Credentials',
                'errors' => [
                    'password' => ['Wrong password']
                ]
            ], 401);
        }

        // Only allow users with role_id == 1
        if ($user->role_id != 2) {
            return response([
                'status' => false,
                'message' => 'Access Denied: You do not have permission to login.',
                'errors' => [
                    'role_id' => ['Only Rater admin can login.']
                ]
            ], 403);
        }

        // Authenticate the user
        Auth::login($user);

        $user = Auth::user();

        // Check if the user is active
        if (!$user->active) {
            return response([
                'status' => false,
                'message' => 'Your account is inactive. Please contact the administrator.',
            ], 403);
        }

        // Generate a token for the user
        $token = $user->createToken('my-secret-token')->plainTextToken;

        // Set the token in a secure cookie
        $cookie = cookie('rater_token', $token, 60 * 24, null, null, true, true, false, 'None');

        return response([
            'status' => true,
            'message' => 'Login Successfully',
            'user' => [
                'name' => $user->name,
                'position' => $user->position,
                'role_id' => (int)$user->role_id, // Always integer
            ],
            'token' => $token,
        ])->withCookie($cookie);
    }
    // public function Token_Login(Request $request)
    // {
    //     // Attempt to authenticate the user with the provided username and password
    //     // First check if the username exists
    //     // First check if username and password are provided
    //     if (empty($request->username) || empty($request->password)) {
    //         return response([
    //             'status' => false,
    //             'message' => 'Invalid Credentials',
    //             'errors' => [
    //                 'username' => empty($request->username) ? ['Please enter username'] : [],
    //                 'password' => empty($request->password) ? ['Please enter password'] : []
    //             ]
    //         ], 401);
    //     }

    //     $user = User::where('username', $request->username)->first();
    //     if (!$user) {
    //         return response([
    //             'status' => false,
    //             'message' => 'Invalid Credentials',
    //             'errors' => [
    //                 'username' => ['Username does not exist'],
    //                 'password' => ['Please enter password']
    //             ]
    //         ], 401);
    //     }

    //     // Then check if the password is correct
    //     if (!Hash::check($request->password, $user->password)) {
    //         return response([
    //             'status' => false,
    //             'message' => 'Invalid Credentials',
    //             'errors' => [
    //                 'password' => ['Wrong password']
    //             ]
    //         ], 401);
    //     }

    //     // If both checks pass, authenticate the user
    //     Auth::login($user);

    //     $user = Auth::user();

    //     // Check if the user is active
    //     if (!$user->active) {
    //         return response([
    //             'status' => false,
    //             'message' => 'Your account is inactive. Please contact the administrator.',
    //         ], 403);
    //     }

    //     // Generate a token for the user
    //     $token = $user->createToken('my-secret-token')->plainTextToken;

    //     // Set the token in a secure cookie
    //     $cookie = cookie('auth_token', $token, 60 * 24, null, null, true, true, false, 'None');

    //     return response([
    //         'status' => true,
    //         'message' => 'Login Successfully',
    //         'user' => [
    //             'name' => $user->name,
    //             'position' => $user->position,
    //             'role_id' => (int)$user->role_id, // <-- Always integer
    //         ],
    //         'token' => $token,
    //     ])->withCookie($cookie);
    // }

    // logout

    // Get all users (User Management) with rspControl data
    public function getAllUsers()
    {
        try {
            $users = User::where('role_id', 1)->with('rspControl')
                ->select('id', 'name', 'username', 'position', 'active', 'created_at', 'updated_at')
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Users retrieved successfully',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve users',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    // Get all users (User Management) with rspControl data
    public function get_all_raters()
    {
        try {
            $users = User::where('role_id', 2)
                ->orderBy('created_at', 'desc') // Order by latest created first
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                    'job_batches_rsp' => $user->job_batches_rsp->pluck('Position')->implode(', '),
                    'office' => $user->office,
                        'created_at' => $user->created_at->format('Y-m-d H:i:s'), // Include created date
                        'updated_at' => $user->updated_at->format('Y-m-d H:i:s'), // Include updated date
                    ];
                });

            return response()->json([
                'status' => true,
                'message' => 'Raters retrieved successfully',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve raters',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    // Get all users (User Management) with rspControl data
    public function get_rater_usernames()
    {
        try {
            $users = User::where('role_id', 2)
                ->orderBy('created_at', 'desc') // Order by latest created first
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'username' => $user->username,
                        'office'=> $user->office,
                    ];
                });

            return response()->json([
                'status' => true,
                'message' => 'Raters retrieved successfully',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve raters',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Get a specific user by ID with rspControl data
    public function getUserById($id)
    {
        try {
            $user = User::with('rspControl')
                ->select('id', 'name', 'username', 'position', 'active')
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'User retrieved successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    // Update user details including permissions
    public function updateUser(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);

            // Validation rules
            $rules = [
                'name' => 'required|string|max:255',
                'position' => 'required|string|max:255',
                'active' => 'required|boolean',
                'permissions.isFunded' => 'boolean',
                'permissions.isUserM' => 'boolean',
                'permissions.isRaterM' => 'boolean',
                'permissions.isCriteria' => 'boolean',
                'permissions.isDashboardStat' => 'boolean',
            ];

            // Add username validation, but exclude the current user's username from the unique check
            $rules['username'] = 'required|string|max:255|unique:users,username,' . $id;

            // If password is provided, add validation rule
            if ($request->filled('password')) {
                $rules['password'] = 'string|min:3';
            }

            $validatedData = $request->validate($rules);

            // Update user data
            $user->name = $validatedData['name'];
            $user->username = $validatedData['username'];
            $user->position = $validatedData['position'];
            $user->active = $validatedData['active'];

            // Only update password if provided
            if ($request->filled('password')) {
                $user->password = Hash::make($validatedData['password']);
            }

            $user->save();

            // Update or create permissions
            if ($request->has('permissions')) {
                if ($user->rspControl) {
                    $user->rspControl->update([
                        'isFunded' => $request->input('permissions.isFunded', false),
                        'isUserM' => $request->input('permissions.isUserM', false),
                        'isRaterM' => $request->input('permissions.isRaterM', false),
                        'isCriteria' => $request->input('permissions.isCriteria', false),
                        'isDashboardStat' => $request->input('permissions.isDashboardStat', false),
                    ]);
                } else {
                    $user->rspControl()->create([
                        'isFunded' => $request->input('permissions.isFunded', false),
                        'isUserM' => $request->input('permissions.isUserM', false),
                        'isRaterM' => $request->input('permissions.isRaterM', false),
                        'isCriteria' => $request->input('permissions.isCriteria', false),
                        'isDashboardStat' => $request->input('permissions.isDashboardStat', false),
                    ]);
                }
            }

            DB::commit();

            // Fetch updated user with permissions
            $updatedUser = User::with('rspControl')
                ->select('id', 'name', 'username', 'position', 'active')
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'User updated successfully',
                'data' => $updatedUser
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete a user and associated rspControl data
    public function deleteUser($id)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);

            // Prevent deleting currently authenticated user
            if (Auth::id() == $id) {
                return response()->json([
                    'status' => false,
                    'message' => 'You cannot delete your own account'
                ], 403);
            }

            // Delete associated rspControl first (if not using cascading deletes)
            if ($user->rspControl) {
                $user->rspControl->delete();
            }

            // Delete the user
            $user->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'User and associated permissions deleted successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function rater_register(Request $request)
    {
        $validatedData = $request->validate([


            // Optional permission flags

        ]);

        try {
            DB::beginTransaction();

            $user = User::create([

                'role_id' => 2,
                'remember_token' => Str::random(32)
            ]);



            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Rater Registered Successfully',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Registration Failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
