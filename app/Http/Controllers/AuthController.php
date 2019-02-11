<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use JWT;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AuthController extends Controller
{
    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Create a new token.
     *
     * @param  \App\User $user
     * @return string
     */
    protected function jwt(User $user)
    {
        $payload = [
            'iss' => "lumen-jwt", // Issuer of the token
            'sub' => $user->id, // Subject of the token
            'iat' => time(), // Time when JWT was issued.
            'exp' => time() + 60 * 60 // Expiration time
        ];

        // As you can see we are passing `JWT_SECRET` as the second parameter that will
        // be used to decode the token in the future.
        return JWT::encode($payload, env('JWT_SECRET'));
    }

    /**
     * Authenticate a user and return the token if the provided credentials are correct.
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate()
    {
        $this->validate($this->request, [
            'username' => 'bail|required|max:50',
            'password' => 'bail|required|max:255'
        ]);
        // Find the user by email
        $user = User::where('username', $this->request->input('username'))->first();
        if (!$user) {
            return response()->json([
                'status' => '401_LOGIN',
                'message' => config()['errors']['401_LOGIN']
            ], 401);
        }

        // Verify the password and generate the token
        if (Hash::check($this->request->input('password'), $user->password)) {
            return response()->json([
                'admin' => $user["isAdmin"],
                'token' => $this->jwt($user)
            ], 200);
        }

        return response()->json([
            'status' => '401_LOGIN',
            'message' => config()['errors']['401_LOGIN']
        ], 401);
    }

    public function validateToken()
    {
        return response()->json([
            'token' => $this->jwt($this->request->auth)
        ], 200);
    }
}
