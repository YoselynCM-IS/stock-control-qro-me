<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Remisiones <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('visitor.remisiones') }}">
			{{ __('Remisiones') }}
		</a>
		<a class="dropdown-item" href="{{ route('visitor.cortes') }}">
			{{ __('Cortes') }}
		</a>
		<a class="dropdown-item" href="{{ route('visitor.fecha-adeudo') }}">
			{{ __('Fecha de adeudos') }}
		</a>
	</div>
</li>
<li>
	<a class="nav-link" href="{{ route('visitor.libros') }}">{{ __("Libros") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('visitor.entradas') }}">{{ __("Entradas") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('visitor.clientes') }}">{{ __("Clientes") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('visitor.notas') }}">{{ __("Notas") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('visitor.pedidos') }}">{{ __("Pedidos") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('visitor.promociones') }}">{{ __("Promociones") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('visitor.donaciones') }}">{{ __("Donaciones") }}</a>
</li>
@include('partials.navigations.logged')