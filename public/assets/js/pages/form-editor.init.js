ClassicEditor.create(document.querySelector("#vc_comando")).then(function(e) {
    e.ui.view.editable.element.style.height = "200px"
}).catch(function(e) {
    console.error(e)
});