<?php

namespace App\Http\Controllers\API;
use JWTAuth;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
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
            'foto' => ['image', 'mimes:jpeg,png,gif'],
            'nama' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);
            
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        // $token = JWTAuth::fromUser($this->user);

        if ($request->file('foto')) {
            if ($this->user->foto != 'default.jpg') {
                File::delete(public_path('storage'.'/'.$this->user->foto));
            }
            
            $p0 =$request->post('role_id');    
            $p2 =$request->post('nrp');
            $p3 =$request->post('nama');
            $p1 = $request->file('foto')->store('public');
            $p5 =Hash::make($request->post('password'));

            $created = Carbon::Now();
            $updated = Carbon::Now();

            // $user = DB::select('call access_token_auth_jwt("'.$p0.'", "'.$p1.'", "'.$p2.'", "'.$p3.'", "'.$p5.'")');
            // $user = DB::select(DB::raw("CALL access_token_auth_jwt($p0, $p3, $p2, $p3, null, $p5)"));
            DB::select('call access_token_auth_jwt(?,?,?,?,?,?,?)',
                        [
                            $p0,
                            $p1,
                            $p2,
                            $p3,
                            $p5,
                            $created,
                            $updated
                        ]
                    )
                ;

            $user = $this->user->get()->last();
            $token = JWTAuth::fromUser($user);
            $user->token = $token; 
            $user->save(); 

            // $user = DB::select(" EXEC access_token_auth_jwt $p0,'$p1','$p2','$p3','$p5' ");
            // printf($user);
          

            // $role_id =$request->post('role_id');    
            // $nrp =$request->post('nrp');
            // $nama =$request->post('nama');
            // $token =$request->post('token');
            // $foto = $request->file('foto')->store('public');
            // $pass =Hash::make($request->post('password'));
            // DB::statement("call access_token_auth_jwt(@p0=$role_id,'$foto','$nrp','$nama','$token','$pass')");
            // array($request->get('role_id'),
            //         $request->file('foto')->store('public'),
            //         $request->get('nrp'),
            //         $request->get('nama'),
            //         $request->get('token'),
            //         Hash::make($request->get('password'))
        //         )
        //     )
        // ;
            // $this->user->foto = $request->file('foto')->store('public');
            // $this->user->role_id = $request->get('role_id');
            // $this->user->nrp = $request->get('nrp');
            // $this->user->nama = $request->get('nama');
            // $this->user->token = $token;
            // $this->user->password = Hash::make($request->get('password'));
        }

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

    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }
}