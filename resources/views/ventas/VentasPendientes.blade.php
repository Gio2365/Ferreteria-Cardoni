@extends('layouts.app')

@section('titulo','Ventas Pendientes')


@section('alert')
<div class="container">
      @if (session('datos'))
  <div class="alert alert-success alert-dismissible fade show" role="alert" align="center">
    {{session('datos')}}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
@endif
@endsection

@section('content')
    <div class="input-group-prepend">
      <div class="col-sm-8">
        <input class="form-control mr-sm-2" name="textoVentaP" id="textoVentaP" type="text" placeholder="Buscar Ventas" aria-label="Search">
      </div>
      <div class="col-sm-4">
        <select class="custom-select" name='opcBuscador' id="opcBuscador" autocomplete="off">
          <option value="1"selected>Codigo Venta</option>
          <option value="2">Empleado</option>
          <option value="3">Cliente</option>
        </select>
      </div>
    </div>

    <br>
<<<<<<< HEAD
  <div class="row">
      <div class="col-sm-4" id="ok1">

        </div>
  </div>
=======
      <div class="row" id="ok1">
      @foreach ($pedidoVentas as $pedido)
        <div class="col-sm-4">
            <div class="card">
            <div class="card-body">
                <h5 class="card-title">Special title treatment</h5>
                <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Vendido por: {{App\empleado::find($pedido->cod_empleado_fk)->nombre}} {{App\empleado::find($pedido->cod_empleado_fk)->apellido}}</li>
                    <li class="list-group-item">Cliente: {{App\cliente::find($pedido->cod_cliente_fk)->nombre}} {{App\cliente::find($pedido->cod_cliente_fk)->apellido}}</li>
                    <li class="list-group-item">Direccion: {{$pedido->direccion}}</li>
                    <li class="list-group-item">Total: ${{$pedido->total}}</li>
                </ul>
                <a href="{{route('Ventas.edit', $pedido->cod_venta)}}" class="btn btn-primary">Editar</a>
                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#exampleModal" data-codigo="{{$pedido->cod_venta}}" data-total="{{$pedido->total}}" data-cliente=" {{App\cliente::find($pedido->cod_cliente_fk)->nombre}} {{App\cliente::find($pedido->cod_cliente_fk)->apellido}}">Eliminar</button>
            </div>
            </div>
        </div>
        @endforeach
    </div>
  
>>>>>>> 59cdc868766d8f0bd509d1bb306dfc37853f251e

@endsection

