<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    use HasFactory;

    public $table = "users";
    public $timestamps = false;

    protected $fillable = [
        'is_block',
        'gender',
        'savedprofile',
        'interests',
        'age',
        'identity',
        'username',
        'fullname',
        'instagram',
        'youtube',
        'facebook',
        'live',
        'bio',
        'about',
        'lattitude',
        'longitude',
        'login_type',
        'device_token',
        'blocked_users',
        'wallet',
        'boost_balance',
        'boost_expires_at',
        'total_gifts_sent',
        'total_gifts_received',
        'total_collected',
        'total_streams',
        'device_type',
        'is_notification',
        'is_verified',
        'show_on_map',
        'anonymous',
        'is_video_call',
        'can_go_live',
        'is_live_now',
        'is_fake',
        'password',
        'following',
        'followers',
        'gender_preferred',
        'age_preferred_min',
        'age_preferred_max',
        'invite_code',
        'points',
        'role',
        'created_at',
        'update_at',
    ];

    public function images()
    {
        return $this->hasMany(Images::class, 'user_id', 'id');
    }

    public function notifications()
    {
        return $this->hasMany(UserNotification::class, 'user_id', 'id');
    }

    public function interests()
    {
        return $this->hasMany(Interest::class, 'id', 'interests');
    }

    function liveApplications()
    {
        return $this->hasOne(LiveApplications::class, 'user_id', 'id');
    }

    function verifyRequest()
    {
        return $this->hasOne(VerifyRequest::class, 'user_id', 'id');
    }

    function liveHistory()
    {
        return $this->hasMany(LiveHistory::class, 'user_id', 'id');
    }

    function redeemRequests()
    {
        return $this->hasMany(RedeemRequest::class, 'user_id', 'id');
    }

    public function stories()
    {
        return $this->hasMany(Story::class, 'user_id', 'id');
    }

    public function likedProfiles()
    {
        return $this->hasMany(LikedProfile::class, 'my_user_id', 'id');
    }

    public function getLikesCountAttribute()
    {
        return $this->likedProfiles()->count();
    }

    public function package()
    {
        return $this->hasOne(UserPackages::class, 'user_id', 'id');
    }

    public function promotionPackage()
    {
        return $this->hasOne(UserPromotionPackage::class, 'user_id', 'id');
    }

    public function shopUser()
    {
        return $this->hasOne(ShopUser::class, 'shop_users_id', 'id');
    }

    public function workerProfile()
    {
        return $this->hasOne(WorkerProfile::class, 'user_id', 'id');
    }
    public function jobs()
    {
        return $this->hasMany(Job::class, 'user_id', 'id');
    }

    public function userPackageTransactions()
    {
        return $this->hasMany(UserPackageTransactions::class, 'user_id', 'id');
    }
    public function promotionPackageTransactions()
    {
        return $this->hasMany(PromotionPackageTransactions::class, 'user_id', 'id');
    }
    public function walletTransactions()
    {
        return $this->hasMany(WalletTransactions::class, 'user_id', 'id');
    }

    public function sendGift()
    {
        return $this->hasMany(GiftHistory::class, 'sender_id', 'id');
    }
    public function recipientGift()
    {
        return $this->hasMany(GiftHistory::class, 'recipient_id', 'id');
    }
    public function invitedUsers()
    {
        return $this->hasMany(Invites::class, 'inviter_id', 'id')->with('invitee');
    }

    public function inviter()
    {
        return $this->hasOneThrough(
            Users::class,      // Model ปลายทาง (คนที่เชิญ)
            Invites::class,    // Model ตัวกลาง
            'invitee_id',      // foreign key ของ invites → user นี้ถูกเชิญ
            'id',              // primary key ของ inviter (users)
            'id',              // local key ของ user ปัจจุบัน
            'inviter_id'       // foreign key ของ invites → ผู้เชิญ
        );
    }
    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class)->orderBy('created_at', 'desc');
    }

    public function redemptions()
    {
        return $this->hasMany(Redemption::class);
    }

    public function isStaff()
    {
        return $this->role === 'staff';
    }
}
