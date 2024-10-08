<x-app-layout>
    <!-- row -->
    <div class="row row-sm">
        <div class="col-xxl-12">
            <div class="card">
                <div class="card-body">
                    <div class="row row-sm">
                        <div class="col-xxl-6 col-lg-12 col-md-12">
                            <div class="row">
                                <div class="col-xxl-2 col-xl-2 col-md-2 col-sm-3">
                                    <div class="clearfix carousel-slider">
                                        <div id="thumbcarousel" class="carousel slide" data-bs-interval="false">
                                            <div class="carousel-inner">
                                                @php
                                                    $thumbIndex = 0;
                                                @endphp
                                                <ul class="carousel-item active">
                                                    @foreach ($fotos as $foto)
                                                        @if ($foto->size == 'small')
                                                            <li data-bs-target="#Slider" data-bs-slide-to="{{ $thumbIndex }}" class="thumb {{ $thumbIndex == 0 ? 'active' : '' }} my-sm-2 m-2 mx-sm-0">
                                                                <img src="{{ asset('uploads/productos/'.$foto->foto_url) }}" alt="img" class="img-thumbnail">
                                                            </li>
                                                            @php
                                                                $thumbIndex++;
                                                            @endphp
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-10 col-xl-10 col-md-10 col-sm-9">
                                    <div class="product-carousel border br-5">
                                        <div id="Slider" class="carousel slide" data-bs-ride="false">
                                            <div class="carousel-inner">
                                                @php
                                                    $slideIndex = 0;
                                                @endphp
                                                @foreach ($fotos as $foto)
                                                    @if ($foto->size == 'original')
                                                        <div class="carousel-item carousel-item-hover {{ $slideIndex == 0 ? 'active' : '' }}">
                                                            <div class="img-container">
                                                                <img src="{{ asset('uploads/productos/'.$foto->foto_url) }}" alt="img" class="img-fluid mx-auto d-block img-custom-size" style="max-width: 613px; max-height: 613px;">
                                                            </div>
                                                            <div class="text-center mt-5 mb-5 btn-list">
                                                            </div>
                                                        </div>
                                                        @php
                                                            $slideIndex++;
                                                        @endphp
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="details col-xxl-6 col-lg-12 col-md-12 mt-4">
                            <h4 class="product-title mb-1">{{ $promocion->titulo }}</h4>
                            <p class="text-muted fs-13 mb-1">{{ $promocion->descripcion }}</p>
                            <div class="rating mb-1">
                                <div id="zoomContainer"></div>
                            </div>
                            <h6 class="price">Precio: <span class="h3 ms-2">${{ $promocion->precio }}</span></h6>
                            <div class="text-center mt-4 btn-list">
                                <a href="javascript:void(0);" class="btn ripple btn-primary me-2 add-to-cart-button" data-product-id="{{$_REQUEST['id']}}"><i class="fe fe-shopping-cart"> </i> Añadir al carrito</a>
                                <a href="checkOut?id={{$_REQUEST['id']}}" class="btn ripple btn-secondary"><i class="fe fe-credit-card"> </i> Comprar ahora</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>

<style type="text/css">
    .ui-pdp-description__content {
        font-size: 17px;
        font-weight: 424;
        word-wrap: break-word;
    }
    .img-custom-size {
        max-width: 100%;
        height: auto;
        object-fit: cover; /* Ajusta la imagen para cubrir el contenedor manteniendo la proporción */
    }
    .img-container {
        position: relative;
    }
    #zoomContainer {
        position: absolute;
        top: 4%;
        left: 50%;
        width: 424px;
        height: 424px;
        overflow: hidden;
        border: 1px solid #000;
        display: none;
        z-index: 10; /* Asegura que el contenedor de zoom esté por encima de otros elementos */
    }
    #zoomContainer img {
        position: absolute;
        width: auto;
        height: auto;
        max-width: none;
    }
    @media (min-width: 768px) {
        #zoomContainer {
            width: 150px;
            height: 150px;
        }
    }
    @media (min-width: 1200px) {
        #zoomContainer {
            width: 424px;
            height: 424px;
        }
    }

</style>
<script src="assets/js/core_js/detalle.js?{{ rand() }}"></script>
<script type="text/javascript">

$(document).on('click', '.add-to-cart-button', function() {
    var button = $(this);
    button.attr('disabled', true).text('Agregando...');

    var productId = button.data('productId');
    var postData = {
        product_id: productId,
        quantity: 1,
    };

    $.ajax({
        url: "/carrito_agregado",
        data: postData,
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        type: 'POST',
        success: function (response) {
            button.attr('disabled', false).text('Agregar al Carrito');
            if (response.b_status) {

                $('#cart-data').text(`Artículos (${response.cartTotal})`);
                $('#cart-icon-badge').text(`${response.cartTotal}`);

                Swal.fire({
                    title: '¡Éxito!',
                    text: 'Producto añadido al carrito con éxito',
                    icon: 'success',
                    timer: 3000
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    text: response.message || 'No se pudo añadir el producto al carrito',
                    icon: 'error'
                });
            }
        },
        error: function () {
            button.attr('disabled', false).text('Agregar al Carrito');
            alert('Error en la solicitud');
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    var thumbs = document.querySelectorAll('.thumb');
    var carouselElement = document.getElementById('Slider');
    var carousel = new bootstrap.Carousel(carouselElement, {
        interval: false
    });

    var zoomContainer = document.getElementById('zoomContainer');

    function setupZoom(image) {
        image.addEventListener('mouseover', function () {
            zoomContainer.style.display = 'block';
            var zoomImage = document.createElement('img');
            zoomImage.src = image.src;
            zoomContainer.appendChild(zoomImage);

            image.addEventListener('mousemove', function (e) {
                var rect = image.getBoundingClientRect();
                var x = e.clientX - rect.left;
                var y = e.clientY - rect.top;

                var zoomWidth = zoomContainer.offsetWidth;
                var zoomHeight = zoomContainer.offsetHeight;

                var imgWidth = image.offsetWidth;
                var imgHeight = image.offsetHeight;

                var zoomX = x * (zoomImage.naturalWidth / imgWidth) - (zoomWidth / 2);
                var zoomY = y * (zoomImage.naturalHeight / imgHeight) - (zoomHeight / 2);

                // Limita la posición de zoomX y zoomY para que no salga del contenedor de zoom
                zoomX = Math.max(0, Math.min(zoomX, zoomImage.naturalWidth - zoomWidth));
                zoomY = Math.max(0, Math.min(zoomY, zoomImage.naturalHeight - zoomHeight));

                zoomImage.style.left = -zoomX + 'px';
                zoomImage.style.top = -zoomY + 'px';
            });

            image.addEventListener('mouseout', function () {
                zoomContainer.style.display = 'none';
                zoomContainer.innerHTML = '';
            });
        });
    }

    // Initial setup for the first visible image
    var initialImage = document.querySelector('.carousel-item.active img.img-custom-size');
    setupZoom(initialImage);

    thumbs.forEach(function (thumb) {
        thumb.addEventListener('mouseover', function () {
            if (!this.classList.contains('active')) {
                thumbs.forEach(function (el) {
                    el.classList.remove('active');
                });
                this.classList.add('active');
                
                var targetIndex = this.getAttribute('data-bs-slide-to');
                carousel.to(targetIndex);

                // Apply zoom effect to the new visible image
                var newActiveImage = document.querySelector('.carousel-item.active img.img-custom-size');
                setupZoom(newActiveImage);
            }
        });
    });

    carouselElement.addEventListener('slid.bs.carousel', function () {
        var activeImage = document.querySelector('.carousel-item.active img.img-custom-size');
        setupZoom(activeImage);
    });
});


</script>
