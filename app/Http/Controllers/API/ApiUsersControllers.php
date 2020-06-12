<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Illuminate\Support\Facades\File;
use Tymon\JWTAuth\Exceptions\JWTException;

class ApiUsersControllers extends Controller
{
    protected $user;
    
    public function __construct(User $user){
        $this->user = $user;
    }

    public function login(Request $request)
    {
        $credentials = $request->only('nrp', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'token tidak valid'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'tidak bisa membuat token'], 500);
        }

        return response()->json(compact('token'));
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|string|max:255',
            'nrp' => 'required|string|max:255',
            'foto' => ['image', 'mimes:jpeg,png,gif', 'max:2048'],
            'nama' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);
            
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $this->user->nama = $request->nama;

        if ($request->file('foto')) {
            if ($this->user->foto != 'default.jpg') {
                File::delete(public_path('storage'.'/'.$this->user->foto));
            }
            $this->user->foto = $request->file('foto')->store('public');
            $this->user->role_id = $request->get('role_id');
            $this->user->nrp = $request->get('nrp');
            $this->user->nama = $request->get('nama');
            $this->user->password = Hash::make($request->get('password'));
        }

        // $user = [
        //     'role_id' => ,
        //     'nama' => ,
        //     'password' => ),
        // ];
        
        $this->user->save();
        $token = JWTAuth::fromUser($this->user);

        return response()->json(compact('user','token'),201);
    }

    public function getAuthenticatedUser()
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['Pengguna tidak ditemukan'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token sudah kadaluarsa'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token tidak valid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token tidak ada'], $e->getStatusCode());

        }

        return response()->json(compact('user'));
    }
}