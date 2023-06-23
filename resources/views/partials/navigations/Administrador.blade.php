<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Remisiones <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('administrador.remisiones') }}">
			{{ __('Remisiones') }}
		</a>
		<a class="dropdown-item" href="{{ route('administrador.pagos') }}">
			{{ __('Pagos') }}
		</a>
		<a class="dropdown-item" href="{{ route('administrador.cerrar') }}">
			{{ __('Devoluciones / Cerrar') }}
		</a>
		<a class="dropdown-item" href="{{ route('administrador.fecha-adeudo') }}">
			{{ __('Fecha de adeudos') }}
		</a>
	</div>
</li>
<li>
	<a class="nav-link" href="{{ route('administrador.libros') }}">{{ __("Libros") }}</a>
</li>
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Entradas <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('administrador.entradas') }}">
			{{ __('Lista') }}
		</a>
		<a class="dropdown-item" href="{{ route('administrador.entradas.pagos') }}">
			{{ __('Pagos') }}
		</a>
	</div>
</li>
<li>
	<a class="nav-link" href="{{ route('administrador.clientes') }}">{{ __("Clientes") }}</a>
</li>
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Movimientos <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('administrador.entradas-salidas') }}">
			{{ __("Entradas / Salidas") }}
		</a>
		<a class="dropdown-item" href="{{ route('administrador.movimientos') }}">
			{{ __('Movimientos (Unidades)') }}
		</a>
		<a class="dropdown-item" href="{{ route('administrador.movimientos_monto') }}">
			{{ __('Movimientos (Monto)') }}
		</a>
		<a class="dropdown-item" href="{{ route('administrador.unidades') }}">
			{{ __('Ventas por cliente') }}
		</a>
		<a class="dropdown-item" href="{{ route('administrador.unidades_libro') }}">
			{{ __('Ventas por libro') }}
		</a>
	</div>
</li>
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Otros <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('administrador.notas') }}">{{ __("Notas") }}</a>
		<a class="dropdown-item" href="{{ route('administrador.pedidos') }}">{{ __("Pedidos") }}</a>
		<a class="dropdown-item" href="{{ route('administrador.promociones') }}">{{ __("Promociones") }}</a>
		<a class="dropdown-item" href="{{ route('administrador.donaciones') }}">{{ __("Donaciones") }}</a>
	</div>
</li>
@include('partials.navigations.logged')