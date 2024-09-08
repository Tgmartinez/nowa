#!/bin/bash

# Array con las URLs a descargar
urls=(
    "https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap"
    "https://ajax.googleapis.com/ajax/libs/angularjs/1.5.6/angular.min.js"
    "https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"
    "https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
    "http://cdn.rawgit.com/needim/noty/77268c46/lib/noty.css"
    "https://cdnjs.cloudflare.com/ajax/libs/mousetrap/1.6.5/mousetrap.min.js"
    "https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"
    "https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"
    "https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js"
    "http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"
    "https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"
    "https://fonts.googleapis.com/icon?family=Material+Icons"
    "https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"
)

# Directorio de destino
output_dir="descargas"
mkdir -p "$output_dir"

# Descargar cada archivo
for url in "${urls[@]}"; do
    echo "Descargando $url..."
    curl -o "$output_dir/$(basename ${url%%\?*})" "$url"
done

echo "Descargas completadas."
