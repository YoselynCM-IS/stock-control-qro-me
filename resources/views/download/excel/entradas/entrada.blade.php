<table>
    <tr>
        <td><b>Folio:</b></td>
        <td>{{ $entrada->folio }}</td>
        <td><b>Fecha de creación:</b></td>
        <td>{{ $entrada->created_at->format('Y-m-d') }}</td>
    </tr>
    <tr>
        <td><b>Editorial:</b></td>
        <td>{{ $entrada->editorial }}</td>
    </tr>
    <tr></tr>
    <tr>
        <th>ISBN</th>
        <th>Libro</th> 
        <th>Costo unitario</th>
        <th>Unidades</th>
        <th>Subtotal</th>
    </tr>
    @foreach($entrada->registros as $registro)
        <tr>
            <td>{{ $registro->libro->ISBN }}</td> 
            <td>{{ $registro->libro->titulo }}</td> 
            <td>${{ number_format($registro->costo_unitario, 2) }}</td>
            <td>{{ number_format($registro->unidades) }}</td>
            <td>${{ number_format($registro->total, 2) }}</td>
        </tr>
    @endforeach  
    <tr>
        <td></td><td></td>
        <td></td>
        <td><b>{{ number_format($entrada->unidades) }}</b></td>
        <td><b>${{ number_format($entrada->total, 2) }}</b></td>
    </tr>
</table>
