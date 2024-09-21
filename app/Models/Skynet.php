<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skynet extends Model
{
    use HasFactory;
    public $table = "skynet";
    protected $fillable =   [     'id'
                                , 'id_user_o_id_cliente'
                                , 'vc_evento'
                                , 'vc_query'
                                , 'vc_info'
                                , 'created_at'
                                , 'b_status'
                            ];

    public $timestamps = false;
}
