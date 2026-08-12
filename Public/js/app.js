document.addEventListener('DOMContentLoaded', function () {
    const contenido = document.getElementById('contenido-principal');
    if (!contenido) return; // Login/registro no tienen sidebar: no aplica

    async function cargarPagina(url, agregarHistorial) {
        try {
            const respuesta = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!respuesta.ok) {
                window.location.href = url;
                return;
            }

            const html = await respuesta.text();
            const urlFinal = respuesta.url; // por si el servidor redirigió (crear/editar/eliminar)

            contenido.innerHTML = html;
            marcarEnlaceActivo(urlFinal);

            if (agregarHistorial) {
                history.pushState({ url: urlFinal }, '', urlFinal);
            }

            window.scrollTo(0, 0);
        } catch (error) {
            window.location.href = url; // Si falla la red, navega de forma normal
        }
    }

    function marcarEnlaceActivo(url) {
        const ruta = new URL(url, window.location.origin);
        const modulo = ruta.pathname.split('/')[1] || 'dashboard';

        document.querySelectorAll('.barra-lateral nav a').forEach(function (enlace) {
            const enlaceModulo = enlace.getAttribute('href').split('/')[1] || 'dashboard';
            enlace.classList.toggle('activo', enlaceModulo === modulo);
        });
    }

    document.querySelector('.app-layout').addEventListener('click', function (evento) {
        const enlace = evento.target.closest('a');
        if (!enlace) return;

        // Si un onclick en línea (ej. confirm() de "Dar de baja") canceló el clic, respetarlo
        if (evento.defaultPrevented) return;

        if (enlace.target === '_blank' || enlace.hasAttribute('download')) return;
        if (enlace.hasAttribute('data-recarga-completa')) return; // ej: Cerrar sesión

        const href = enlace.getAttribute('href');
        if (!href || !href.startsWith('/')) return; // Solo rutas internas

        evento.preventDefault();
        cargarPagina(href, true);
    });

    window.addEventListener('popstate', function () {
        cargarPagina(window.location.pathname + window.location.search, false);
    });
});