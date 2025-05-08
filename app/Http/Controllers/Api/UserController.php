<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a paginated listing of users with optional search.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validated = Validator::make($request->all(), [
            'search'    => 'sometimes|string|max:255',
            'per_page'  => 'sometimes|integer|min:1|max:100',
            'page'      => 'sometimes|integer|min:1',
        ], [
            'search.string' => 'The search field must be a string.',
            'search.max'    => 'The search field may not be greater than 255 characters.',
            'per_page.integer' => 'The per page field must be an integer.',
            'per_page.min'    => 'The per page field must be at least 1 characters.',
            'per_page.max'    => 'The per page field must be at least 100 characters.',
            'page.integer' => 'The page field must be an integer.',
            'page.min'    => 'The page field must be at least 1 characters.',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'message' => 'Invalid input.',
                'validation'  => $validated->errors()->toArray()
            ], 400);
        }

        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                    ->orWhere('username', 'LIKE', "%$search%")
                    ->orWhere('email', 'LIKE', "%$search%");
            });
        }

        $perPage = $request->input('per_page', 10);
        $users = $query->paginate($perPage);

        return response()->json([
            'data'    => $users->items(),
            'meta'    => [
                'current_page' => $users->currentPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
                'last_page'    => $users->lastPage(),
            ]
        ]);
    }

    /**
     * Display the specified user.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        return response()->json([
            'data' => $user
        ]);
    }

    /**
     * Store a newly created user in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|string|min:8|same:password',
        ];

        $messages = [
            'name.required' => 'Name is required.',
            'name.string' => 'Name must be a valid string.',
            'name.max' => 'Name cannot exceed 255 characters.',

            'username.required' => 'Username is required.',
            'username.string' => 'Username must be a valid string.',
            'username.max' => 'Username cannot exceed 255 characters.',
            'username.unique' => 'Username already exists.',

            'email.required' => 'Email is required.',
            'email.string' => 'Email must be a string.',
            'email.email' => 'Email format is invalid.',
            'email.max' => 'Email cannot exceed 255 characters.',
            'email.unique' => 'This email is already registered.',

            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
            'password.min' => 'Password must be at least 8 characters.',

            'confirm_password.required' => 'Confirm password is required.',
            'confirm_password.string' => 'Confirm password must be a string.',
            'confirm_password.min' => 'Confirm password must be at least 8 characters.',
            'confirm_password.same' => 'Confirm password must match the password.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid Input.',
                'validation' => $validator->errors()->toArray()
            ], 400);
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'name'     => $request->name,
                'username'     => $request->username,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'User are successfully saved.',
                'data'    => $user
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create user.',
                'errors'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified user in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        $rules = [
            'name' => 'required|string|min:4|max:100',
            'username' => 'required|string|min:4|max:100',
        ];

        $messages = [
            'name.required' => 'Name is required when provided.',
            'name.string' => 'Name must be a valid string.',
            'name.min' => 'Name must be at least 4 characters.',
            'name.max' => 'Name may not be greater than 255 characters.',

            'username.required' => 'Username is required.',
            'username.string' => 'Username must be a string.',
            'username.min' => 'Username must be at least 4 characters.',
            'username.max' => 'Username cannot exceed 100 characters.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid input.',
                'validation'  => $validator->errors()->toArray()
            ], 400);
        }

        DB::beginTransaction();
        try {
            $user->name  = $request->input('name', $user->name);
            $user->username  = $request->input('username', $user->username);
            $user->email = $request->input('email', $user->email);

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            DB::commit();

            return response()->json([
                'message' => 'User are successfully updated.',
                'data'    => $user
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update user.',
                'errors'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the password of the specified user.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updatePassword(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        $rules = [
            'password' => 'required|string|min:8|max:100',
            'confirm_password' => 'required|string|min:8|max:100|same:password',
        ];

        $messages = [
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.max' => 'Password may not be greater than 100 characters.',

            'confirm_password.required' => 'Confirm password is required.',
            'confirm_password.string' => 'Confirm password must be a string.',
            'confirm_password.min' => 'Confirm password must be at least 8 characters.',
            'confirm_password.max' => 'Confirm password may not be greater than 100 characters.',
            'confirm_password.same' => 'Confirm password must match the password.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user->password = Hash::make($request->password);
            $user->save();

            DB::commit();

            return response()->json([
                'message' => 'User password are successfully updated.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update password.',
                'errors'   => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Remove the specified user from storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        if (auth()->id() === $user->id) {
            return response()->json([
                'message' => 'You cannot delete your own account.'
            ], 403);
        }

        $rules = [
            'confirm_password' => 'required|string|min:8|max:100',
        ];

        $messages = [
            'confirm_password.required' => 'Confirm password is required.',
            'confirm_password.string' => 'Confirm password must be a string.',
            'confirm_password.min' => 'Confirm password must be at least 8 characters.',
            'confirm_password.max' => 'Confirm password may not be greater than 100 characters.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $validator->errors()
            ], 422);
        }

        if (!Hash::check($request->confirm_password, auth()->user()->password)) {
            return response()->json([
                'message' => 'The confirm password does not match.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $user->delete();

            DB::commit();

            return response()->json([
                'message' => 'User are successfully deleted.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to delete user.',
                'errors'   => $e->getMessage()
            ], 500);
        }
    }
}
