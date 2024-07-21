<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property string $email
 * @property string $name
 * @property-read Committer $committer
 * @property-write string $password
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected const SSH_KEY_STORAGE = 'keys';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function committer(): HasOne
    {
        return $this->hasOne(Committer::class);
    }

    public function getAccessToken(): ?string
    {
        // TODO: need to implement
        return null;
    }

    ///////////////////////////////////////
    /// SSH KEY
    public function saveSshKey(string $key): bool
    {
        $filename = $this->getSshKeyFilename();

        $storage = $this->getSshKeyStorage();
        $storage->put($filename, $key);
        return $storage->setVisibility($filename, 'private');
    }

    public function hasSshKey(): bool
    {
        return $this->getSshKeyStorage()->has($this->getSshKeyFilename());
    }

    public function getSshKeyPath(): string
    {
        return $this->getSshKeyStorage()->path($this->getSshKeyFilename());
    }

    private function getSshKeyStorage(): FilesystemAdapter
    {
        return Storage::disk(self::SSH_KEY_STORAGE);
    }

    private function getSshKeyFilename(): string
    {
        return $this->id;
    }
    /// END OF SSH KEY
    ///////////////////////////////////////
}
