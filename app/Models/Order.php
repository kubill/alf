<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_APPLIED = 'applied';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    public static $orderStatusMap = [
        self::STATUS_PENDING => '待支付',
        self::STATUS_APPLIED => '待消费',
        self::STATUS_SUCCESS => '已完成',
        self::STATUS_FAILED => '已过期'
    ];

    protected $fillable = [
        'no',
        'total_amount',
        'remark',
        'paid_at',
        'payment_method',
        'payment_no',
        'status',
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

    public static function findAvailableNo()
    {
        // 订单流水号前缀
        $prefix = date('YmdHis');
        for ($i = 0; $i < 10; $i++) {
            // 随机生成 6 位的数字
            $no = $prefix.str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            // 判断是否已经存在
            if (!static::query()->where('no', $no)->exists()) {
                return $no;
            }
            usleep(100);
        }
        \Log::warning(sprintf('find order no failed'));

        return false;
    }
}
