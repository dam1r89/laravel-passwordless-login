<?php

namespace dam1r89\PasswordlessAuth;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LoginToken extends Model
{
    protected $keyType = 'string';
    protected $primaryKey = 'email';

    public $timestamps = false;

    protected $fillable = ['email', 'intended_url', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function make(array $attrs = [])
    {
        $email = $attrs['email'];

        // delete old tokens
        static::query()->where($email)->delete();

        return parent::create($attrs);
    }

    protected static function boot()
    {
        self::creating(function (LoginToken $model) {
            $model->created_at = Carbon::now();
            $model->token = $model->createNewToken();
        });
        parent::boot();
    }

    public function user()
    {
        // TODO: Pull config out of here
        return $this->belongsTo(config('passwordless.provider'), 'email', 'email');
    }

    /**
     * @see \Illuminate\Auth\Passwords\DatabaseTokenRepository::createNewToken
     */
    public function createNewToken()
    {
        $key = config('app.key');

        /*
         * @see \Illuminate\Auth\Passwords\PasswordBrokerManager::createTokenRepository
         */
        if (substr($key, 0, 7) == 'base64:') {
            $key = base64_decode(substr($key, 7));
        }

        return hash_hmac('sha256', Str::random(40), $key);
    }

    public function getLoginLink()
    {
        return route('passwordless.auth', ['token' => $this->token]);
    }
}
