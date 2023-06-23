<li>
	<a class="nav-link" href="{{ route('captura.remisiones') }}">{{ __("Remisiones") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('captura.libros') }}">{{ __("Libros") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('captura.promociones') }}">{{ __("Promociones") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('captura.donaciones') }}">{{ __("Donaciones") }}</a>
</li>
@include('partials.navigations.logged')