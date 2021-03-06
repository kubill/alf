<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    const STATUS_PENDING = '1';
    const STATUS_APPLIED = '2';
    const STATUS_SUCCESS = '3';
    const STATUS_FAILED = '0';
    public static $orderStatusMap = [
        self::STATUS_PENDING => '待支付',
        self::STATUS_APPLIED => '待消费',
        self::STATUS_SUCCESS => '已完成',
        self::STATUS_FAILED => '已过期'
    ];

    const ORDER_TYPE_RESERVE = 1;//订单类型 订场
    const ORDER_TYPE_RECHARGE = 2;//订单类型 充值
    public static $typeMap = [
        self::ORDER_TYPE_RESERVE => '订场',
        self::ORDER_TYPE_RECHARGE => '充值',
    ];

    const PAYMENT_TYPE_CASH = 'cash';//订单支付方式 现金
    const PAYMENT_TYPE_WECHAT = 'wechat';//订单支付方式 微信支付 充值只能是这个
    const PAYMENT_TYPE_BALANCE = 'balance';//账户余额支付，只能订场
    public static $paymentMap = [
        self::PAYMENT_TYPE_CASH => '线下支付',
        self::PAYMENT_TYPE_WECHAT => '微信支付',
        self::PAYMENT_TYPE_BALANCE => '余额支付',
    ];

    protected $fillable = [
        'no',
        'total_fees',
        'remark',
        'paid_at',
        'payment_method',
        'payment_no',
        'status',
        'type',
    ];


    protected $dates = [
        'paid_at',
    ];

    protected static function boot()
    {
        parent::boot();
        // 监听模型创建事件，在写入数据库之前触发
        static::creating(function ($model) {
            // 如果模型的 no 字段为空
            if (!$model->no) {
                // 调用 findAvailableNo 生成订单流水号
                $model->no = static::findAvailableNo();
                // 如果生成失败，则终止创建订单
                if (!$model->no) {
                    return false;
                }
            }
        });
    }

    public function getTotalFeesAttribute($value)
    {
        return $value / 100;
    }

    public function setTotalFeesAttribute($value)
    {
        $this->attributes['total_fees'] = $value * 100;
    }

    public static function findAvailableNo()
    {
        // 订单流水号前缀
        $prefix = date('YmdHis');
        for ($i = 0; $i < 10; $i++) {
            // 随机生成 6 位的数字
            $no = $prefix . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            // 判断是否已经存在
            if (!static::query()->where('no', $no)->exists()) {
                return $no;
            }
            usleep(100);
        }
        \Log::warning(sprintf('find order no failed'));

        return false;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->hasOne(OrderItem::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
