@extends('layouts.app')

@section('titulo','Compras')

@section('content')
    <table class="table table-hover">
    <div class="input-group-prepend">
        <input class="form-control mr-sm-2" name="textoCompra" id="textoCompra" type="text" placeholder="Buscar Productos" aria-label="Search">
    </div>
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Nombre</th>
                <th scope="col">Unidades Compradas</th>
                <th scope="col">Fecha de Compra</th>
            </tr>
        </thead>
        <tbody id="VistaCompra">
        <tr>
          <td align="center" colspan="5">Ingrese el nombre o codigo de producto que desea ver </td>
        </tr>
      </tbody>
        <!-- <tbody>
            @foreach ($pedidoCompra as $compra)
            <tr>
            <th scope="row">{{$compra->cod_producto_fk}}</th>
                {{-- Recuperando el nombre del producto --}}
                <td>{{App\producto::find($compra->cod_producto_fk)->nombre}}</td>
                <td>{{$compra->cantidad}}</td>
                {{-- Mostrando la fecha en el formato dia-mes-año --}}
                <td>{{\Carbon\Carbon::parse($compra->created_at)->format('d/m/Y')}}</td>
            </tr>
            @endforeach
        </tbody> -->
    </table>
  <!--   <div class="row">
        <div class="mx-auto">
            {{$pedidoCompra}}
        </div>
    </div> -->
@endsection
