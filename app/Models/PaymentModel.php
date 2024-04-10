<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentModel extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = ['tenant_id',
    'payment_type', /* manual ->0 , cheque ->1*/
    'cheque_no',
    'payment_date',
    'amount',
    'status', /* 1.upcoming payment 2.voided 3.settled  4.Overdue 5.cheque returned
            6.payment in default*/
    'building_id',
    'unit_id',
    'remark',
    'pm_company_id',
    'payment_code'
    ];
}

/*
for status
cheques/manual           upcoming payment(1)
cheques/manual           voided(2)
cheques/manual           settled (3)
cheques/manual           Overdue payment(4)
cheques           cheque returned(5) 
cheques/manual              Payment In Defaul(6)
*/
