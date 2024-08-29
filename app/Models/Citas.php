<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Citas extends Model
{
    use HasFactory;
    public $table = "citas";
    // protected $connection = 'other_bd'; // Descomentar esta linea y agregar la bd que se requiere...
    protected $fillable =   [     'id'
                                , 'id_empleado'
                                , 'fecha_cita'
                                , 'hora_inicio'
                                , 'hora_fin'
                                , 'id_cliente'
                                , 'estado'
                                , 'b_status'
                            ];

    public $timestamps = false;
}
