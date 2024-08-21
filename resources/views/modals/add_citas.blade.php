@php
    // Esta funcion no se ocupa solo lo ocupo como atajo
    // en sublime con F12 puedes llegar a esta vista

    function add_citas(){}
@endphp

<div class="modal fade" id="modalFormIUcitas" tabindex="-1" aria-labelledby="add_new_citasLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="form-material form-action-post" action="#form_citas" id="form_citas" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="add_new_citasLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-sm-12">
                            <label for="id_user" class="form-label">User ID</label>
                            <input type="text" class="form-control" id="id_user" name="id_user" placeholder="Escribe el ID del User">
                        </div>
                        <div class="col-sm-12">
                            <label for="id_empleado" class="form-label">Empleado ID</label>
                            <input type="text" class="form-control" id="id_empleado" name="id_empleado" placeholder="Escribe el ID del Empleado">
                        </div>
                        <div class="col-sm-12 d-none tipo-ya-existe">
                            <span class="badge bg-danger text-uppercase">Este registro ya existe</span>
                        </div>
                        <div class="col-12">
                            <label for="id_cliente" class="form-label">Cliente ID</label>
                            <input type="text" class="form-control" id="id_cliente" name="id_cliente" placeholder="Escribe el ID del Cliente">
                        </div>
                        <div class="col-sm-12">
                            <label for="fecha_cita" class="form-label">Fecha de Cita</label>
                            <input type="text" class="form-control" id="fecha_cita" name="fecha_cita" placeholder="Escribe la Fecha de la Cita">
                        </div>
                        <div class="col-sm-12">
                            <label for="hora_inicio" class="form-label">Hora de Inicio</label>
                            <input type="text" class="form-control" id="hora_inicio" name="hora_inicio" placeholder="Escribe la Hora de Inicio">
                        </div>
                        <div class="col-12">
                            <label for="hora_fin" class="form-label">Hora de Fin</label>
                            <input type="text" class="form-control" id="hora_fin" name="hora_fin" placeholder="Escribe la Hora de Fin">
                        </div>
                        <div class="col-12">
                            <label for="estado" class="form-label">Estado</label>
                            <input type="text" class="form-control" id="estado" name="estado" placeholder="Escribe el Estado">
                        </div>
                    </div>
                </div>
                <div class="modal-footer m-t-10">
                    <div class="col-lg-12">
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary btn-action-form">Guardar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /.modalFormIUcitas -->