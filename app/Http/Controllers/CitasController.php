<?php

namespace App\Http\Controllers;
use Throwable;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Notifications\citasSendMail as FncitasSendMail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\citas;
use App\Lib\LibCore;
use Session;

class CitasController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Declaración de variables
    | Auth::user()->email;
    |--------------------------------------------------------------------------
    |
    */
    public $LibCore;

    /*
    |--------------------------------------------------------------------------
    | Inicializar variables comunes
    |--------------------------------------------------------------------------
    |
    */
    public function __construct(){
        $this->LibCore = new LibCore();
    }

    /*
    |--------------------------------------------------------------------------
    | Inicial
    |--------------------------------------------------------------------------
    |
    | Carga solo vista con HTML
    | Todo es controlado por JS citas.js
    |
    */
    public function index()
    {
        $this->LibCore->setSkynet( ['vc_evento'=> 'index_citas' , 'vc_info' => "index - citas" ] );
        return view('citas');
    }


    /*
    |--------------------------------------------------------------------------
    | Datatable registro especial como se requiere en js
    |--------------------------------------------------------------------------
    | 
    | @return json
    |
    */
    public function get_citas_datatable(Request $request)
    {
        if(!\Schema::hasTable('citas')){
            return json_encode(array("data"=>"" ));
        }

        if (   ( isset($request->buscar_id_empleado) && !empty($request->buscar_id_empleado) )
            || ( isset($request->buscar_fecha_cita) && !empty($request->buscar_fecha_cita) )
            || ( isset($request->buscar_hora_inicio) && !empty($request->buscar_hora_inicio) )
            || ( isset($request->buscar_hora_fin) && !empty($request->buscar_hora_fin) )
            || ( isset($request->buscar_id_cliente) && !empty($request->buscar_id_cliente) )
            || ( isset($request->buscar_estado) && !empty($request->buscar_estado) )
        ){
            $buscar= 0;
        }else{
            $buscar= 1;
        }

        $request->search= isset($request->search["value"]) ? $request->search["value"] : '';
        $buscar_id_empleado= isset($request->buscar_id_empleado) ? $request->buscar_id_empleado :'';
        $buscar_fecha_cita= isset($request->buscar_fecha_cita) ? $request->buscar_fecha_cita :'';
        $buscar_hora_inicio= isset($request->buscar_hora_inicio) ? $request->buscar_hora_inicio :'';
        $buscar_hora_fin= isset($request->buscar_hora_fin) ? $request->buscar_hora_fin :'';
        $buscar_id_cliente= isset($request->buscar_id_cliente) ? $request->buscar_id_cliente :'';
        $buscar_estado= isset($request->buscar_estado) ? $request->buscar_estado :'';
        $request->start = isset($request->start) ? $request->start : intval(0);
        $request->length= isset( $request->length) ? $request->length : intval(10);
        $request->column= isset( $request->order[0]['column']) ? $request->order[0]['column'] : intval(0);
        $request->order= isset( $request->order[0]['dir']) ? $request->order[0]['dir'] : 'DESC';

        // Lógica para invocar el procedimiento almacenado
        $sql= 'CALL sp_get_citas(
               '.$buscar.'
            , "'.$request->search.'"
            , "'.$buscar_id_empleado.'"
            , "'.$buscar_fecha_cita.'"
            , "'.$buscar_hora_inicio.'"
            , "'.$buscar_hora_fin.'"
            , "'.$buscar_id_cliente.'"
            , "'.$buscar_estado.'"
            ,  '.$request->start.'
            ,  '.$request->length.'
            ,  '.$request->column.'
            ,  "'.$request->order.'"
            ,  @v_registro_total)';

        $result = DB::select($sql);

        // Recuperar el valor de la variable de salida
        $v_registro_total = DB::select('SELECT @v_registro_total as v_registro_total')[0]->v_registro_total;

        return response()->json([
            'data' => $result,
            'recordsTotal' => $v_registro_total,
            'recordsFiltered' => $v_registro_total,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Agrega o modificar registro
    |--------------------------------------------------------------------------
    | 
    | Modifica el registro solo si manda el parametro '$request->id'
    | @return json
    |
    */
    public function set_citas(Request $request)
    {
        if(!\Schema::hasTable('citas')){
            Notification::route('mail', ['odin0464@gmail.com'])->notify(
                new FncitasSendMail(
                    'Notificación no existe tabla citas'
                    , __DIR__ ." \ n"
                )
            );
            return json_encode(array("b_status"=> false, "vc_message" => "No se encontro la tabla citas"));
        }

        $data=[ 'id_empleado' => isset($request->id_empleado)? $request->id_empleado:"",
                'fecha_cita' => isset($request->fecha_cita)? $request->fecha_cita: "",
                'hora_inicio' => isset($request->hora_inicio)? $request->hora_inicio: "",
                'hora_fin' => isset($request->hora_fin)? $request->hora_fin: "",
                'id_cliente' => isset($request->id_cliente)? $request->id_cliente: "",
                'estado' => isset($request->estado)? $request->estado: "",
        ];

        // Si ya existe solo se actualiza el registro
        if (isset($request->id)){
            DB::table('citas')->where('id', $request->id)->update($data);
            return json_encode(array("b_status"=> true, "vc_message" => "Actualizado correctamente..."));
        }else{ // Nuevo registro
            DB::table('citas')->insert($data);
            return json_encode(array("b_status"=> true, "vc_message" => "Agregado correctamente..."));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validar existencia antes de crear un nuevo registro
    |--------------------------------------------------------------------------
    | 
    | @return json
    |
    */
    public function validar_existencia_citas(Request $request)
    {
        if ( isset($request->id) && $request->id > 0){
            $data= citas::select('id_empleado')
            ->where('id_empleado' ,'=', trim($request->id_empleado))
            ->where('id' ,'<>', $request->id)
            ->where('b_status' ,'>', 0)
            ->get();
        }else{
            $data= citas::select('id_empleado')
            ->where('id_empleado' ,'=', trim($request->id_empleado))
            ->get();
        }

        if ( $data->count() > 0 ){
            return json_encode(array("b_status"=> true, "data" => $data));
        }else{
            return json_encode(array("b_status"=> false, "data" => 'sin resultados'));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Importar pensado para cat, simple
    |--------------------------------------------------------------------------
    |
    */
    public function set_import_citas(Request $request)
    {
        if(!\Schema::hasTable('citas')){
            return json_encode(array("b_status"=> false, "vc_message" => "No se encontro la tabla Cat_tipificacion"));
        }

        $this->LibCore->setSkynet( ['vc_evento'=> 'set_import_citas' , 'vc_info' => "set_import_citas" ] );

        $arr = explode("\r\n", trim( $request->vc_importar ));
        $arr = array_reverse($arr);
         
        for ($i = 0; $i < count($arr); $i++) {
           $line = $arr[$i];

            if (!empty($line)){
                $data[]=  ['id_empleado'=> trim($line)] ;
            }
        }

        if (isset($data) && !empty($data)){
            citas::truncate();
            citas::insert($data);
            return json_encode(array("b_status"=> true, "vc_message" => "Importado correctamente..."));
        }
            return json_encode(array("b_status"=> false, "vc_message" => "No se encontraron registros..."));
    }

      public function getServerSidecitas(Request $request)
    {
        $buscarPor = $request->input('search', ''); 

        $results = DB::table('citas')
            ->Where('id', ' > ', 0)
            ->OrWhere('id_empleado', 'LIKE', '%' . $buscarPor . '%')
            ->select('id'
                , DB::raw('CONCAT(id, " ", id_empleado, " ", fecha_cita ) as id_empleado')
            )
            ->limit(10)
            ->get();

        // Formatea los resultados para el selectpicker
        $options = $results->map(function ($item) {
            return ['id' => $item->id, 'id_empleado' =>Str::headline($item->id_empleado) ];
        });

        return response()->json($options);
    } 

    /*
    |--------------------------------------------------------------------------
    | Importar en excel
    |--------------------------------------------------------------------------
    |
    */
    public function form_importar_citas(Request $request)
    {
        $path = public_path('CargandoExcel');

        if(!File::isDirectory($path)){
            File::makeDirectory($path, 0777, true, true);
        }

        if (!empty($request->file('files'))){
            $path = Storage::putFile( $path, $request->file('files') );
        }

        if (!empty($path)){
            $this->LibCore->setSkynet(['vc_evento'=> 'uploadExcelSuccess' , 'vc_info' => "<b>Subiendo Excel ok </b> ". $path ] );

            ////////////////////////////////////////
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load( Storage::path($path ) );
            $d=$spreadsheet->getSheet(0)->toArray();
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            // DESCOMENTAR PARA OMITIR TITULO EN EXCEL
            // $i=1;
            // unset($sheetData[0]);

            // $sheetData= array_reverse($sheetData);

            foreach ($sheetData as $key => $t) {

                $data_insert[]=  array(  "id_empleado"  =>  isset($t[0]) ? $t[0] : ''
                                        ,"fecha_cita"  =>  isset($t[1]) ? $t[1] : ''
                                        ,"hora_inicio"  =>  isset($t[2]) ? $t[2] : ''
                                        ,"hora_fin"  =>  isset($t[3]) ? $t[3] : ''
                                        ,"id_cliente"  =>  isset($t[4]) ? $t[4] : ''
                                        ,"estado"  =>  isset($t[5]) ? $t[5] : ''
                );
            }

            foreach (array_chunk($data_insert,1000) as $temp)  
            {
                DB::table('citas')->insert( $temp );
            }

            return json_encode(array("b_status"=> true, "data" => [ "vc_path" =>  Storage::url( $path )  ] ));
        }

        return json_encode(array("b_status"=> false, "data" => [ "vc_message" => 'No se adjunto algun archivo' ] ));
    }

    /*
    |--------------------------------------------------------------------------
    | Generar plantilla para descargar Excel
    |--------------------------------------------------------------------------
    | 
    | @return id
    |
    */
    public function descargar_plantilla_citas(Request $request)
    {
            $nombre_archivo= 'plantilla_citas.xlsx';

            $title[]= [  "Id_Empleado"
                        ,"Fecha_Cita"
                        ,"Hora_Inicio"
                        ,"Hora_Fin"
                        ,"Id_Cliente"
                        ,"Estado"
                    ];

            $arr_data= $title;

            $this->LibCore->crear_archivos( $arr_data );

            $process = new Process( [ 'python3', public_path("/")."generico.py" , $nombre_archivo  ] );

            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $output_data = $process->getOutput();

            return Storage::download('public/'.$nombre_archivo);       
    }

    /*
    |--------------------------------------------------------------------------
    | Obtener un registro por id
    |--------------------------------------------------------------------------
    | 
    | @return json
    |
    */
    public function get_citas_by_id(Request $request)
    {
        $data= citas::select('id_empleado'
                                    , 'fecha_cita'
                                    , 'hora_inicio'
                                    , 'hora_fin'
                                    , 'id_cliente'
                                    , 'estado'
        )->where('id', $request->id)->get();

        if ( $data->count() > 0 ){
            return json_encode(array("b_status"=> true, "data" => $data));
        }else{
            return json_encode(array("b_status"=> false, "data" => 'sin resultados'));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Solo se usa para mostrar en una lista <select> ---- </select>
    |--------------------------------------------------------------------------
    | 
    | @return json
    |
    */
    public function get_cat_citas(Request $request)
    {
        $data= citas::select(  'id'
                                    , 'id_empleado'
                                    , 'fecha_cita'
                                    , 'hora_inicio'
                                    , 'hora_fin'
                                )->where('b_status', 1)->get();

        if ( $data->count() > 0 ){
            return json_encode(array("b_status"=> true, "data" => $data));
        }else{
            return json_encode(array("b_status"=> false, "data" => 'sin resultados'));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Scroll diez en diez
    |--------------------------------------------------------------------------
    | 
    | @return json
    |
    */
    public function get_citas_diez(Request $request)
    {
        if(!\Schema::hasTable('citas')){
            return json_encode(array("data"=>"" ));
        }

        $data= DB::table("citas")
        ->select("id"
            , "id_empleado"
            , "fecha_cita"
            , "hora_inicio"
            , "hora_fin"
            , "id_cliente"
            , "estado"
        )
        ->where("citas.b_status", ">", 0)
        ->limit(50)
        ->orderBy("citas.id","desc")
        ->get();

        $total= $data->count();

        if($total > 0){

            foreach ($data as $key => $value) {
                $arr[]= array(    'id'=> $value->id
                                , 'id_empleado'=>$value->id_empleado
                                , 'fecha_cita'=>$value->fecha_cita
                                , 'hora_inicio'=>$value->hora_inicio
                                , 'hora_fin'=>$value->hora_fin
                                , 'id_cliente'=>$value->id_cliente
                                , 'estado'=>$value->estado
                );
            }

            return response()
            ->json($arr)
            ->withCallback($request->input('callback'));

        }else{
            return 1;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Registro especial como se requiere en js
    |--------------------------------------------------------------------------
    | 
    | @return json
    |
    */
    public function get_citas_by_list(Request $request)
    {
        if(!\Schema::hasTable('citas')){
            return json_encode(array("data"=>"" ));
        }

        $data= citas::select(  "id"
                                    , "id_empleado"
                                    , "fecha_cita"
                                    , "hora_inicio"
                                    , "hora_fin"
                                    , "id_cliente"
                                    , "estado"
        )->where('b_status', 1)->orderBy('id', 'desc')->get();
        $total= $data->count();

        if($total > 0){

            foreach ($data as $key => $value) {
                $arr[]= array(    $value->id
                                , $value->id_empleado
                                , $value->fecha_cita
                                , $value->hora_inicio
                                , $value->hora_fin
                                , $value->id_cliente
                                , $value->estado
                );
            }
            return json_encode(array("b_status"=> true, "data" => $arr ));
        }else{
            return json_encode(array("b_status"=> false, "data" => 'Sin resultado' ));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Exportar Excel
    |--------------------------------------------------------------------------
    | 
    | @return id
    |
    */
    public function export_excel_citas(Request $request){

        if(!\Schema::hasTable('citas')){
            return json_encode(array("data"=>"" ));
        }

        $data= citas::select("id"
                                    , "id_empleado"
                                    , "fecha_cita"
                                    , "hora_inicio"
                                    , "hora_fin"
                                    , "id_cliente"
                                    , "estado"
        )->where('b_status', 1)->orderBy('id', 'desc')->get();
        $total= $data->count();

        if($total > 0){

            foreach ($data as $key => $value) {
                $arr_data[]= array(   $value->id
                                    , $value->id_empleado
                                    , $value->fecha_cita
                                    , $value->hora_inicio
                                    , $value->hora_fin
                                    , $value->id_cliente
                                    , $value->estado
                );
            }

            $nombre_archivo= 'Reporte_de_citas.xlsx';

            $title[]= [  "id"
                        ,"Id_Empleado"
                        ,"Fecha_Cita"
                        ,"Hora_Inicio"
                        ,"Hora_Fin"
                        ,"Id_Cliente"
                        ,"Estado"
                    ];

            $arr_data= array_merge($title, $arr_data);

            $this->LibCore->crear_archivos( $arr_data );

            $process = new Process( [ 'python3', public_path("/")."generico.py" , $nombre_archivo  ] );

            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $output_data = $process->getOutput();

            return $nombre_archivo;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Eliminar registro por id
    |--------------------------------------------------------------------------
    | 
    | @return id
    |
    */
    public function delete_citas(Request $request)
    {
        $id=$request->id;
        citas::where('id', $id)->update(['b_status' => 0]);
        return $id;
    }

    /*
    |--------------------------------------------------------------------------
    | Desahacer el registro que se elimino
    |--------------------------------------------------------------------------
    | 
    | @return id
    |
    */
    public function undo_delete_citas(Request $request)
    {
        $id=$request->id;
        citas::where('id', $id)->update(['b_status' => 1]);        
        return $id;
    }

    /*
    |--------------------------------------------------------------------------
    | Truncar toda la tabla util para hacer pruebas
    |--------------------------------------------------------------------------
    | 
    | @return id
    |
    */
    public function truncate_citas()
    {
        citas::where('b_status', 1)->update(['b_status' => 0]);        
    }

    /*
    |--------------------------------------------------------------------------
    | Eliminar Store procedures
    |--------------------------------------------------------------------------
    | 
    | @return id
    |
    */
    public function truncate_sps_citas()
    {
        // Eliminar el SP
        DB::unprepared('DROP PROCEDURE IF EXISTS `sp_get_citas` ');
    }
}
