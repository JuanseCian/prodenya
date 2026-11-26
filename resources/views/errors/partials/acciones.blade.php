<a href="{{ url('/') }}" class="error-button error-button--primary">Ir al inicio</a>
<button type="button" class="error-button error-button--ghost" onclick="history.length > 1 ? history.back() : window.location.assign('{{ url('/') }}')">
    Volver atrás
</button>
