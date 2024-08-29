let checkOut = {

    init: function () {

        const urlParams = new URLSearchParams(window.location.search);

        // Obtener el valor del parámetro 'id'
        const id = urlParams.get('id');            

        // Agregar el id del producto
        $('#id_producto').val(id);

        // Obtener lista para pagar
        checkOut.fn_customerConnekta();

        //Crear la Orden
        checkOut.fn_fnCreateOrder();

        checkOut.fn_eventos_clicks();

        checkOut.fn_modalAgregarEfecto();
    },

    fn_customerConnekta: function () {

        $.ajax({
            url: "fn_getCustomerConekta",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            type: 'POST',
            success: function(data) {
                let cardsHTML = '';
                
                if (!data){
                    return;
                }

                data.forEach(function(card) {
                    cardsHTML += `
                        <li class="badge-type-selection__list-item ui-list__item">
                            <div>
                                <label tabindex="0" class="ui-radio__label">
                                    <div class="ui-radio-element">
                                        <input type="radio" name="selected_card[]" id="card_${card.payment_source_id}" value="${card.payment_source_id}" data-customer-id="${card.customer_id}" class="ui-radio__input d-none">
                                        <div class="ui-radio__background">
                                            <div class="ui-radio__outer-circle"></div>
                                            <div class="ui-radio__inner-circle"></div>
                                        </div>
                                    </div>
                                    <div class="ui-radio__text">
                                        <span class="avatar me-2 avatar-rounded">
                                            <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="${card.brand} logo" width="30">
                                        </span>
                                        <div class="badge-type__metadata">
                                            <span class="badge-type-selection__list-title">${card.name}</span>
                                            <p class="badge-type-selection__list-text">
                                                <span class="phone">********${card.number.slice(-4)}</span>
                                                <span>${card.card_type.charAt(0).toUpperCase() + card.card_type.slice(1)} - ${card.brand.charAt(0).toUpperCase() + card.brand.slice(1)}</span>
                                            </p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </li>`;
                });

                // Assuming you have a container element to append this card HTML
                $('#DivCustomerConekta').html(cardsHTML);

                $(document).on('click', '.ui-list__item', function() {

                    let selectedRadio = $(this).find('input[type="radio"]').first();
                    selectedRadio.prop('checked', true); // Selecciona el radio button

                    let value = selectedRadio.val();
                    let customerId = selectedRadio.data('customer-id');
                    
                    $('#customer_id').val(customerId);
                    $('#card_id').val(value);

                });
            }
        });
    },

    fn_fnCreateOrder: function () {

        // https://techlaboratory.net/jquery-smartwizard
        function getTwoDaysLater() {
            var today = new Date();
            var twoDaysLater = new Date(today);
            twoDaysLater.setDate(today.getDate() + 1);
            return twoDaysLater.toISOString().split('T')[0]; // Convertir a formato YYYY-MM-DD
        }

        var calendarInitialized = false;
        var calendar;
        var selectedLabel = null;  // Keep track of the previously selected label
        var countdownInterval;  // Variable to hold the interval ID

        var selectedDate = null;

        // Configuración dinámica para mostrar o no los sábados y domingos
        var mostrarSabados = true; // Cambia esto a false para ocultar los sábados
        var mostrarDomingos = false; // Cambia esto a false para ocultar los domingos

        var countdownInterval;  // Variable to hold the interval ID

        // Configuración de Toastr para evitar mensajes duplicados
        toastr.options = {
            "preventDuplicates": true
        };

        // Configurar hiddenDays según la visibilidad de sábados y domingos
        var hiddenDays = [];
        if (!mostrarSabados) hiddenDays.push(6); // 6 representa el sábado
        if (!mostrarDomingos) hiddenDays.push(0); // 0 representa el domingo

        function initializeCalendar() {
            var calendarEl = document.getElementById('agendar_cita_automotriz');
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es', // Cambia el idioma a español
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: ''
                },
                validRange: {
                    start: getTwoDaysLater() // Fecha de inicio válida: dos días después de hoy
                },
                hiddenDays: hiddenDays, // Ocultar días específicos
                height: 'auto', // Ajustar la altura automáticamente
                contentHeight: 500, // Ajustar la altura del contenido
                aspectRatio: 1.35, // Reducir la relación de aspecto
                events: function(fetchInfo, successCallback, failureCallback) {
                    $.ajax({
                        url: "horarasSeleccionados",
                        type: "GET",
                        dataType: "json",
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        success: function(response) {
                            var events = response.map(function(item) {
                                return {
                                    title: item.title,
                                    start: item.start,
                                    backgroundColor: item.isFull ? 'green' : '#3788d8',
                                    textColor: item.isFull ? 'white' : 'black',
                                    display: item.isFull ? 'background' : 'block',
                                    classNames: item.isFull ? ['Lleno'] : []
                                };
                            });
                            successCallback(events);
                        },
                        error: function() {
                            failureCallback();
                        }
                    });
                },
                eventClick: function(info) {

                    var events = calendar.getEvents();

                    // Uso de la función
                    var exists = checkCookie("fechaSeleccionado");

                    if(exists && selectedLabel){
                        selectedLabel.classList.remove('bg-danger', 'text-white');
                        selectedLabel.querySelector('input').disabled = false;
                        selectedLabel.innerHTML = selectedLabel.innerHTML.replace(' (Ocupado)', '');

                        // Eliminar eventos excepto los que tienen la clase 'Lleno'
                        calendar.getEvents().forEach(function(event) {
                                console.log("event.classNames", event.classNames);
                            if (!event.classNames.includes('Lleno')) {
                                event.remove();
                            }
                        });

                        // Quitar el contador.
                        $('#countdownToast').addClass('d-none');

                        console.log(exists ? "La cookie existe." : "La cookie no existe.");
                    }
                    if (info.event.title === 'Lleno') {
                        var formattedDate = new Intl.DateTimeFormat('es-ES', { dateStyle: 'full' }).format(new Date(info.event.startStr));
                        toastr.error(`No se puede seleccionar el ${formattedDate}.`, 'Error');
                        return;
                    }

                    selectedDate = info.event.start.toISOString().split('T')[0];

                    selectedDate = info.event.start.toISOString().split('T')[0];
                    var offcanvasElement = document.getElementById('offcanvasEvento');
                    var offcanvas = new bootstrap.Offcanvas(offcanvasElement);
                    offcanvas.show();

                },
                dateClick: function(info) {

                    //=================================================
                    // Obtener el día seleccionado
                    var selectedDatePersonalizado = info.date;

                    // Formatear la fecha en yyyy-mm-dd
                    var year = selectedDatePersonalizado.getFullYear();
                    var month = ('0' + (selectedDatePersonalizado.getMonth() + 1)).slice(-2); // Agregar ceros iniciales si es necesario
                    var day = ('0' + selectedDatePersonalizado.getDate()).slice(-2); // Agregar ceros iniciales si es necesario

                    // Fecha que se esta seleccionado en el calendario
                    var fechaSeleccionado = `${year}-${month}-${day}`;

                    //=================================================
                    // Obtener el día de la semana en formato textual
                    var dayOfWeek = selectedDatePersonalizado.toLocaleDateString('es-ES', { weekday: 'long' });

                    // Convertir a formato Camel Case
                    var diaSeleccionado = dayOfWeek.charAt(0).toUpperCase() + dayOfWeek.slice(1).toLowerCase();

                    // =================================================
                    // Obtener los horarios disponible 
                    $.ajax({
                        url: "get_cita_disponible",
                        data: {fechaSeleccionado: fechaSeleccionado , diaSeleccionado : diaSeleccionado },
                        cache: false,
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        type: 'POST',
                        success: function (response) {

                            // Asegúrate de que la respuesta contiene "horariosOcupados"
                            if (response.horariosOcupados && response.horariosOcupados.length > 0) {

                                // Recorrer los valores de "horariosOcupados"
                                response.horariosOcupados.forEach(function(horario) {
                                    // Deshabilitar las opciones que coinciden con "hora_inicio"
                                    $('input[name="hora"]').each(function() {

                                        if ($(this).val() === horario.hora_inicio && horario.total == horario.contador) {
                                            $(this).attr('disabled', true);
                                            $(this).closest('label').addClass('bg-danger text-white');
                                        }
                                    });
                                });
                            }else{

                                // Habilitar las opciones que coinciden con "hora_inicio"
                                $('input[name="hora"]').each(function() {
                                    $(this).attr('disabled', false);
                                    $(this).closest('label').removeClass('bg-danger text-white');
                                });                                
                            }

                            // RECORRER VALORES
                            if ($('#fechaSeleccionado').length){
                                $('#form_check_out #fechaSeleccionado').remove();
                            }

                            $('#form_check_out').append('<input type="text" id="fechaSeleccionado" name="fechaSeleccionado" value="'+fechaSeleccionado+'"> ');

                        },
                        error: function (response) {

                        }
                    });

                    //=================================================
                    // FIN Obtener el día seleccionado

                    // Uso de la función
                    var exists = checkCookie("fechaSeleccionado");

                    if(exists && selectedLabel){
                        selectedLabel.classList.remove('bg-danger', 'text-white');
                        selectedLabel.querySelector('input').disabled = false;
                        selectedLabel.innerHTML = selectedLabel.innerHTML.replace(' (Ocupado)', '');

                        // Eliminar eventos excepto los que tienen la clase 'Lleno'
                        calendar.getEvents().forEach(function(event) {
                                console.log("event.classNames", event.classNames);
                            if (!event.classNames.includes('Lleno')) {
                                event.remove();
                            }
                        });

                        // Quitar el contador.
                        $('#countdownToast').addClass('d-none');

                        console.log(exists ? "La cookie existe." : "La cookie no existe.");
                    }

                    // Verificar si hay un evento de "Lleno" en la fecha seleccionada
                    var events = calendar.getEvents();
                    var formattedDate = new Intl.DateTimeFormat('es-ES', { dateStyle: 'full' }).format(new Date(info.dateStr));
                    var isDateFull = events.some(event => event.startStr === info.dateStr && event.title === 'Lleno');

                    document.getElementById('eventDetails').innerText = formattedDate;

                    if (isDateFull) {
                        toastr.error(`No se puede seleccionar el ${formattedDate}.`);
                        return;
                    }

                    // Si se selecciona una nueva fecha, restablecer el calendario y el formulario
                    if (selectedDate && selectedDate !== info.dateStr) {
                        // document.getElementById('agendarCitaForm').reset();
                    }

                    selectedDate = info.dateStr;
                    var offcanvasElement = document.getElementById('offcanvasEvento');
                    var offcanvas = new bootstrap.Offcanvas(offcanvasElement);
                    offcanvas.show();
                }
            });
            calendar.render();
            calendarInitialized = true;

            // Manejar el clic del botón de enviar
            document.getElementById('submitCitaForm').addEventListener('click', function() {
                var form = document.getElementById('agendarCitaForm');
                var formData = new FormData(form);
                var hora = formData.get('hora');
                var titulo = 'Agendar';

                console.log('Formulario enviado:', Object.fromEntries(formData.entries()));

                document.cookie = "fechaSeleccionado=ok";

                if (selectedDate && hora) {
                    // Calcular la hora de fin
                    var startHour = parseInt(hora.split(':')[0]);
                    var endHour = startHour + 2;
                    var endHourFormatted = endHour < 10 ? '0' + endHour + ':00' : endHour + ':00';
                    var endTime = new Intl.DateTimeFormat('es-ES', { timeStyle: 'short' }).format(new Date(`1970-01-01T${endHourFormatted}:00`));
                    var startTime = new Intl.DateTimeFormat('es-ES', { timeStyle: 'short' }).format(new Date(`1970-01-01T${endHourFormatted}:00`));
                    calendar.addEvent({
                        title: `${startTime} - ${endTime} (${titulo})`,
                        start: selectedDate + 'T' + hora,
                        allDay: false,
                        classNames: ['text-success']  // Agregar la clase personalizada
                    });

                    if ($('#form_check_out #hora_inicio').length){
                        $('#form_check_out #hora_inicio').remove();
                    }

                    $('#form_check_out').append('<input type="text" id="hora_inicio" name="hora_inicio" value="'+startTime+'"> ');

                    if ($('#form_check_out #hora_fin').length){
                        $('#form_check_out #hora_fin').remove();
                    }

                    $('#form_check_out').append('<input type="text" id="hora_inicio" name="hora_fin" value="'+endTime+'"> ');

                    // Limpiar la clase de la selección anterior
                    if (selectedLabel) {
                        selectedLabel.classList.remove('bg-danger', 'text-white');
                        selectedLabel.querySelector('input').disabled = false;
                        selectedLabel.innerHTML = selectedLabel.innerHTML.replace(' (Ocupado)', '');
                    }

                    // Aplicar la clase 'bg-danger' a la nueva hora seleccionada
                    selectedLabel = document.querySelector(`input[name="hora"][value="${hora}"]`).parentElement;
                    selectedLabel.classList.add('bg-danger', 'text-white');
                    selectedLabel.querySelector('input').disabled = true;
                    selectedLabel.append(' (Ocupado)');

                    // Calcular la diferencia en días
                    var today = new Date();
                    var targetDate = new Date(selectedDate);
                    var differenceInTime = targetDate.getTime() - today.getTime();
                    var differenceInDays = Math.ceil(differenceInTime / (1000 * 3600 * 24));

                    // Formatear la diferencia en días
                    var daysText;
                    if (differenceInDays === 0) {
                        daysText = "mañana";
                    } else if (differenceInDays === 1) {
                        daysText = "1 día restante";
                    } else {
                        daysText = `${differenceInDays} días restantes`;
                    }

                    // Formatear fecha y hora para el mensaje de confirmación
                    var formattedDate = new Intl.DateTimeFormat('es-ES', { dateStyle: 'full' }).format(targetDate);
                    var formattedTime = new Intl.DateTimeFormat('es-ES', { timeStyle: 'short' }).format(new Date(`1970-01-01T${hora}:00`));
                    var formattedDateTime = `<strong style="color: green;">${formattedDate} a las ${formattedTime}</strong> (${daysText})`;

                    // Mostrar el modal de confirmación
                    showConfirmationModal(`Cita seleccionado exitosamente para el ${formattedDateTime}.<br>Tienes 10 minutos para realizar el pago, de lo contrario, la cita será liberada para otro usuario.`);

                    // Iniciar el contador después de que el toast se haya mostrado
                    setTimeout(function() {
                        var countdownElement = document.getElementById('countdownToast');
                        if (countdownElement) {
                            $('#countdownToast').removeClass('d-none');
                            startCountdown(10 * 60, countdownElement);
                        }
                    }, 500); // Aumenta el tiempo de espera a 500ms para asegurar que el toast esté en el DOM

                    // Cerrar el OffCanvas
                    var offcanvasElement = document.getElementById('offcanvasEvento');
                    var offcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
                    offcanvas.hide();
                } else {
                    toastr.error('Por favor, seleccione una fecha y una hora.', 'Error');
                }

                function showConfirmationModal(message) {
                    var confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
                    document.querySelector('#confirmationModal .modal-body').innerHTML = message;
                    confirmationModal.show();
                }

                function startCountdown(duration, display) {
                    var timer = duration, minutes, seconds;
                    clearInterval(countdownInterval); // Clear any existing interval
                    countdownInterval = setInterval(function() {
                        minutes = parseInt(timer / 60, 10);
                        seconds = parseInt(timer % 60, 10);

                        minutes = minutes < 10 ? "0" + minutes : minutes;
                        seconds = seconds < 10 ? "0" + seconds : seconds;

                        display.textContent = 'Tiempo restante ' + minutes + ":" + seconds;

                        if (--timer < 0) {
                            clearInterval(countdownInterval);
                            display.textContent = "Tiempo agotado";
                            // Aquí puedes agregar lógica adicional cuando el tiempo se agote
                        }
                    }, 1000);
                }

            });          
        }

        function checkCookie(name) {
            // Añadimos el signo igual porque las cookies están en formato nombre=valor
            var cookieName = name + "=";
            var decodedCookie = decodeURIComponent(document.cookie);
            var ca = decodedCookie.split(';');
            for(var i = 0; i < ca.length; i++) {
                var c = ca[i].trim();
                if (c.indexOf(cookieName) == 0) {
                    return true;
                }
            }
            return false;
        }

        // Inicializar SmartWizard
        $('#smartwizardCheckOut').smartWizard({
            selected: 0,
            theme: 'dots',
            justified: true,
            toolbar: {
                showNextButton: true, // mostrar/ocultar el botón Siguiente
                showPreviousButton: true, // mostrar/ocultar el botón Atrás
                position: 'bottom', // none|top|bottom|both
                extraHtml: `<button type="submit" class="btn btn-success sw-btn d-none" id="comprar-checkout">Comprar</button>`
            },
            lang: { // Variables de idioma para el botón
                next: 'Siguiente',
                previous: 'Atras'
            },
            keyboard: {
                keyNavigation: false, // Habilitar/Deshabilitar la navegación con el teclado (las teclas izquierda y derecha se usan si están habilitadas)
                keyLeft: [37], // Código de la tecla izquierda
                keyRight: [39] // Código de la tecla derecha
            }
        });

        // Inicializar FullCalendar cuando se carga la página
        initializeCalendar();

        // Manejar el evento de cambio de paso en SmartWizard
        $('#smartwizardCheckOut').on("showStep", function(e, anchorObject, stepNumber, stepDirection, stepPosition) {
            if (stepNumber === 0 && !calendarInitialized) { // Paso 1 y el calendario no está inicializado
                initializeCalendar();
            } else if (stepNumber === 0 && calendarInitialized) {
                calendar.render(); // Renderizar nuevamente si el calendario ya está inicializado
            }
        });

        $("#form_check_out").validate({
            submitHandler: function (form) {
                let get_form = document.getElementById("form_check_out");
                let postData = new FormData(get_form);

                let element_by_id= 'form_check_out';
                let message=  'Cargando...' ;
                let $loading= LibreriaGeneral.f_cargando(element_by_id, message);

                $.ajax({
                    url: "fnCreateOrder",
                    data: postData,
                    cache: false,
                    processData: false,
                    contentType: false,
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    type: 'POST',
                    success: function (response) {
                        console.log("response", response);

                        let json ='';
                        try {
                            json = JSON.parse(response);
                        } catch (e) {
                            $loading.waitMe('hide');
                            console.log("response", response);
                            return;
                        }

                        if (json["b_status"]) {
                            window.location.href = 'completado';                            
                        } else {

                            Swal.fire({
                                title: json["vc_message"],
                                confirmButtonText: 'Aceptar',
                                showClass: {
                                    popup: 'animate__animated animate__bounceIn'
                                },
                                hideClass: {
                                    popup: 'animate__animated animate__bounceOut'
                                }
                            });

                            $loading.waitMe('hide');
                        }
                    },
                    error: function (response) {
                        console.log("response", response);
                        $loading.waitMe('hide');
                    }
                });

            }
            , rules: {
                appointmentTime: {
                    required: true
                },
                "horario_dispobible[]": {
                    required: true
                }
            }
            , messages: {
                appointmentDateTime: {
                    required: "Por favor seleccione una fecha disponible"
                },
                appointmentTime: {
                    required: "Por favor seleccione una hora de cita"
                },
               "horario_dispobible[]": {
                    required: "Por favor seleccione un horario disponible" // Asegúrate de que el nombre del campo esté entre comillas
                }
            },
            ignore: "", // Esta línea asegura que los campos ocultos también se validen
            errorPlacement: function(error, element) {
                // Personaliza dónde y cómo se colocan los mensajes de error
                if (element.attr("name") == "appointmentDateTime" ) {
                    error.appendTo("#errorSeleccionarFecha");
                }
                if (element.attr("name") == "horario_dispobible[]" ) {
                    error.appendTo("#errorSeleccionarHorario");
                }else {
                    error.insertAfter(element);
                }
            }
        });

        // Set event to validate before moving to the next step
        $("#smartwizardCheckOut").on("leaveStep", function (e, anchorObject, stepNumber, stepDirection) {

            if (stepDirection === 0) {
                $(".sw-toolbar-elm #comprar-checkout").addClass('d-none');
                $(".sw-toolbar-elm .sw-btn-next").removeClass('d-none');
                return $("#form_check_out").valid();
            }

            if (stepDirection === 1) {
                $(".sw-toolbar-elm #comprar-checkout").addClass('d-none');
                $(".sw-toolbar-elm .sw-btn-next").removeClass('d-none');
                return $("#form_check_out").valid();
            }

            if (stepDirection === 2) {
                $(".sw-toolbar-elm .sw-btn-next").addClass('d-none');
                $(".sw-toolbar-elm #comprar-checkout").removeClass('d-none');
            }

            return true;
        });

        // Handle navigation button clicks
        $('.nexttab').on('click', function() {
            $('#smartwizardCheckOut').smartWizard("next");
        });

        $('.previestab').on('click', function() {
            $('#smartwizardCheckOut').smartWizard("prev");
        });
    },

    fn_eventos_clicks: function(){

        $('.main-contact-item').on('click touch', function () {
            $(this).addClass('selected');
            $(this).siblings().removeClass('selected');
            $('body').addClass('main-content-body-show');
        });        
    },

    fn_modalAgregarEfecto: function(){

        var exampleModal = document.getElementById('modalFormIUclienteConekta')
        exampleModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget
            var recipient = button.getAttribute('data-bs-whatever')
            var modalTitle = exampleModal.querySelector('.modal-title')
            var modalBodyInput = exampleModal.querySelector('.modal-body input')
            modalTitle.textContent = 'New message to ' + recipient
            modalBodyInput.value = recipient
        });

        document.querySelectorAll(".modal-effect").forEach(e => {
            e.addEventListener('click', function (e) {
                e.preventDefault();
                let effect = this.getAttribute('data-bs-effect');
                document.querySelector("#modalFormIUclienteConekta").classList.add(effect);
            });
        })
        /* hide modal effects */
        document.getElementById("modalFormIUclienteConekta").addEventListener('hidden.bs.modal', function (e) {
            let removeClass = this.classList.value.match(/(^|\s)effect-\S+/g);
            removeClass = removeClass[0].trim();
            this.classList.remove(removeClass);
        });
    },

    fn_eventos_extra_check_out: function(){
    },

};

checkOut.init();