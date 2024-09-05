let core = {
    init: function () {
        // Establece la visibilidad inicial de la terminal y configura la funcionalidad de alternancia
        core.checkTerminalVisibility();
        core.toggleTerminal(); // Considera llamarlo solo bajo ciertas condiciones si no quieres que se active al cargar la página
    },

    toggleTerminal: function () {
        var terminalBody = document.querySelector('.terminal-body');
        if (terminalBody.style.display === 'none' || !terminalBody.style.display) {
            terminalBody.style.display = 'block';
            localStorage.setItem('terminalVisible', 'true');
        } else {
            terminalBody.style.display = 'none';
            localStorage.setItem('terminalVisible', 'false');
        }
    },

    checkTerminalVisibility: function () {
        var terminalBody = document.querySelector('.terminal-body');
        var terminalVisible = localStorage.getItem('terminalVisible');
        console.log("terminalVisible", terminalVisible);
        
        // Asegurarse de que la terminal se muestre o se oculte según el valor almacenado
        if (terminalVisible === 'true') {
            terminalBody.style.display = 'block';
        } else if (terminalVisible === 'false') {
            terminalBody.style.display = 'none';
        } else {
            // Si no hay valor almacenado, decide un comportamiento por defecto
            terminalBody.style.display = 'block'; // O 'none', dependiendo de tus necesidades
            localStorage.setItem('terminalVisible', 'true'); // Alineado con el comportamiento por defecto
        }
    }
};

document.addEventListener('DOMContentLoaded', function() {
    core.init();
});
