<table>
    <tr>
        <th><b>ISBN</b></th>
        <th><b>LIBRO</b></th>
        <th><b>EXISTENCIA</b></th>
        <th><b>ENTRADAS</b></th>
        <th><b>DEVOLUCIÓN (REMISIONES)</b></th>
        <th><b>DEVOLUCIÓN (ENTRADAS)</b></th>
        <th><b>REMISIONES</b></th>
        <th><b>NOTAS</b></th>
        <th><b>PROMOCIONES</b></th>
        <th><b>DONACIONES</b></th>
    </tr>
    @foreach($movimientos as $movimiento)
        <tr>
            <td>{{ $movimiento['ISBN'] }}</td>
            <td>{{ $movimiento['libro'] }}</td>
            <td>{{ $movimiento['existencia'] }}</td>
            <td>{{ $movimiento['entradas'] }}</td>
            <td>{{ $movimiento['devoluciones'] }}</td>
            <td>{{ $movimiento['entdevoluciones'] }}</td>
            <td>{{ $movimiento['remisiones'] }}</td>
            <td>{{ $movimiento['notas'] }}</td>
            <td>{{ $movimiento['promociones'] }}</td>
            <td>{{ $movimiento['donaciones'] }}</td>
        </tr>
    @endforeach
</table>