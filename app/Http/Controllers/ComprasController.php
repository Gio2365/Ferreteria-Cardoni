<?php

namespace App\Http\Controllers;

use App\Http\Requests\FormCrearCompras;
use App\pedidocompra;
use Illuminate\Http\Request;
use App\compra;
use App\producto;
use App\proveedor;
use App\empleado;
use App\historialcompra;
use Illuminate\Support\Facades\Auth;

class ComprasController extends Controller
{


    public function productosProveedor($id)
    {
        return producto::where('cod_proveedor_fk', $id)->get();
    }


    public function __construct()
    {
        $this->middleware('bodega')->only(['index']);
        $this->middleware('compras')->only(['create']);

    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pedidoCompra = pedidocompra::paginate(10);

        return view('compras.vistaCompras', compact('pedidoCompra'));
    }
    public function index2()
    {
        $pedidoCompras = compra::where('estado','pendiente')->get();

        return view('compras.ComprasPendientes', compact('pedidoCompras'));
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $proveedor = proveedor::all();
        // $producto = producto::all();
            
        return view('compras.crearCompras', compact('proveedor'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(FormCrearCompras $request)
    {
        $codEmpleado = Auth::user()->id;
        
        $compras = new compra;

        $compras->cod_empleado_fk = $codEmpleado;
        $compras->cod_proveedor_fk = $request->idproveedor;
        $compras->descripcion = $request->iddescripcion;
        $compras->estado = "pendiente";
        $compras->total = substr($request->totalc,1);
        $compras->save();
        // Recuperando el codigo de la ultima compra
        $codUltimaCompra = compra::orderBy('cod_compra', 'desc')->first()->cod_compra;
        // Arreglo de codigos de productos
        $productos = $request->nombreproducto;
        // Arreglo de cantidades de productos
        $cantidades = $request->idcantidad;
        //arreglo precio
        $precio=$request->idprecioC;

        $i = 0;

        while ($i < sizeof($productos) ) {
            
            $pedidoCompra = new pedidocompra;           
            $pedidoCompra->cod_compra_fk = $codUltimaCompra;
            
            // dd(producto::where('nombre', $productos[$i])->first()->cod_producto);
            $pedidoCompra->cod_producto_fk = producto::where('nombre', $productos[$i])->first()->cod_producto;
            $pedidoCompra->cantidad = $cantidades[$i];
            $pedidoCompra->save();

            $cant=producto::where('nombre', $productos[$i])->first()->cantidad;
            $cant=$cant+$cantidades[$i];
            $pro=producto::find(producto::where('nombre', $productos[$i])->first()->cod_producto);
            $pro->precioCompra=$precio[$i];
            $pro->cantidad=$cant;
            $pro->save();

            $i++;
        }
        return redirect(route('compras.create'))->with('datos','Registro exitoso');
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    { 
        $codProveedor = compra::find($id)->cod_proveedor_fk;

        $total = compra::find($id)->total;

        $proveedores = proveedor::all();

        $nombreproveedor = proveedor::find($codProveedor)->nombre;

        $productoscompra = pedidocompra::where('cod_compra_fk', $id)->get();

        $productosIventario = producto::where('cod_proveedor_fk', $codProveedor)->get();

        // dd($productosIventario);

        return view('compras.modiComprasPendientes', compact('id','codProveedor', 'proveedores', 'nombreproveedor', 'productoscompra', 'total', 'productosIventario'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Actualizando la compra
        $compra = compra::findOrFail($id);

        $totalCompra = $request->totalc;
        // dd($totalCompra);

        $totalCompra = substr($totalCompra, 1);

        // dd($totalCompra);

        $compra->total = $totalCompra;

        $compra->descripcion = $request->iddescripcion;

        // if ($request->nombreventa) {
        //     $compra->cod_cliente_fk = $request->nombreventa;
        // }

        $compra->update();


        $pedidosCompra = pedidocompra::where('cod_compra_fk', $id)->get();

        $productosNombre = array();
        $productosCompradosNombre = array();
        $cantidadesDiccionario = array();
        $codigosPedidoDiccionario = array();
        $cantidadesPedidoDiccionario = array();
        $precioCompraDiccionario = array();

        $productosFormulario = $request->nombreproducto;
        $cantidadesFormulario = $request->idcantidad;
        $preciosCompra = $request->idprecioC;

        // Quitando el precio de los productos del formulario
        $i = 0;
        foreach ($productosFormulario as $productoForm) {
        $productoArray = explode('$', $productoForm);

        $productoArray[0] = rtrim($productoArray[0]);

        $cantidadesDiccionario[$productoArray[0]] = $cantidadesFormulario[$i];
        $precioCompraDiccionario[$productoArray[0]] = $preciosCompra[$i]; 
        $i++;
        array_push($productosNombre, $productoArray[0]);
        }

        // Recuperando los nombres de los productos comprados
        foreach ($pedidosCompra as $productoCompra) {
            $nombreP = producto::where('cod_producto', $productoCompra->cod_producto_fk)->value('nombre');

            $codigosPedidoDiccionario[$nombreP] = $productoCompra->cod_pedidocompra;

            $cantidadesPedidoDiccionario[$nombreP] = $productoCompra->cantidad;

            array_push($productosCompradosNombre, $nombreP);
        }


        // Eliminar pedidosventas

        foreach ($productosCompradosNombre as $productoComprado) {
        

        if (!in_array($productoComprado, $productosNombre, true)) {

            $productoInventario = producto::where('nombre', $productoComprado)->first();

            $productoInventario->cantidad -= $cantidadesPedidoDiccionario[$productoComprado];

            $productoInventario->update();

            $codigoCompraModificar = $codigosPedidoDiccionario[$productoComprado];

            $pedidoCompraModificar = pedidocompra::find($codigoCompraModificar);

            $pedidoCompraModificar->delete();
        }


        }



        // Modificar o agregar pedidoscompras
        foreach ($productosNombre as $producto) {
        
        // Agregando productos a la compra
        if (!in_array($producto, $productosCompradosNombre, true)) {
            
            $productoInventario = producto::where('nombre', $producto)->first();

            $nuevoPedidoCompra = new pedidocompra();

            $nuevoPedidoCompra->cod_compra_fk = $id;

            $nuevoPedidoCompra->cod_producto_fk = producto::where('nombre', $producto)->value('cod_producto');

            $nuevoPedidoCompra->cantidad = $cantidadesDiccionario[$producto];

            $productoInventario->cantidad += $cantidadesDiccionario[$producto];

            $productoInventario->precioCompra = $precioCompraDiccionario[$producto];
            
            $productoInventario->update();

            $nuevoPedidoCompra->save();

        }else{

            $productoInventario = producto::where('nombre', $producto)->first();

            $codigoCompraModificar = $codigosPedidoDiccionario[$producto];

            $pedidoCompraModificar = pedidocompra::find($codigoCompraModificar);


            if ($cantidadesDiccionario[$producto] > $cantidadesPedidoDiccionario[$producto]) {
            $diferencia = $cantidadesDiccionario[$producto] - $cantidadesPedidoDiccionario[$producto];

            $productoInventario->cantidad += $diferencia;

            $productoInventario->precioCompra = $precioCompraDiccionario[$producto];

            $productoInventario->update();
            }else{

            $diferencia = $cantidadesPedidoDiccionario[$producto] - $cantidadesDiccionario[$producto];

            $productoInventario->cantidad -= $diferencia;

            $productoInventario->precioCompra = $precioCompraDiccionario[$producto];

            $productoInventario->update();

            }

            $pedidoCompraModificar->cantidad = $cantidadesDiccionario[$producto];

            $pedidoCompraModificar->update();

        }
        }

        // Registrando el codigo del empleado 

        $bitacora= new historialcompra();
        $bitacora->operacion="Modificar";
        $bitacora->cod_empleado_fk=Auth::user()->cod_empleado_fk;
        $bitacora->cod_compra_fk=$id;
        $bitacora->save();

        return redirect('/PendienteCompra')->with('datos', 'Los datos se actualizaron correctamente');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function buscador(Request $request){
        
        if($request->ajax()){
            
            $query = trim($request->get('query'));
            if($query != ''){
                $productos = producto::where('cod_producto','LIKE','%'.$query.'%')
                            ->orWhere('nombre','LIKE','%'.$query.'%')
                            ->take(10)
                            ->get();
            }else{
                $output='
                <tr>
                    <td align="center" colspan="5">Ingrese el nombre o codigo de producto que desea ver </td>
                </tr>
                ';
            }
            if(isset($productos)){
                $total=$productos->count();
                $output='';
                
                if($total>0)
                { 
                    foreach($productos as $ItemP){
                        $pedidoCompra= pedidocompra::where('cod_producto_fk',$ItemP->cod_producto)
                                                    ->get();
                        foreach($pedidoCompra as $hcompra){
                            $output .='
                            <tr>
                            <th scope="row">'.$ItemP->cod_producto.'</th>
                            <td>'.$ItemP->nombre.'</td>
                            <td>'.$hcompra->cantidad.'</td>
                            <td>'.\Carbon\Carbon::parse($hcompra->created_at)->format('d/m/Y').'</td>
                        ';
                        }
                        $output .= '<tr>';
                    }
    
                }else{
                    $output='
                    <tr>
                        <td align="center" colspan="5">Sin Registros</td>
                    </tr>
                    ';
                }

            }

            echo json_encode($output);
        }
    }
    public function buscadorCompras(Request $request){
        if($request->ajax()){

            $query = trim($request->get('query'));
            $opc= $request->get('opc');
            if($query != ''){
                switch($opc){
                    case 1:{  
                    $pedidocompra = compra::where('cod_compra','LIKE','%'.$query.'%')
                                ->get();
                        if(isset($pedidocompra)){
                        $total=$pedidocompra->count();
                        $output='';
                        if($total>0)
                        {
                            foreach($pedidocompra as $ItemP){
                            $redireccion = route('compras.edit', $ItemP->cod_compra);
                            $empleadoN= empleado::where('cod_empleado',$ItemP->cod_empleado_fk)->value('nombre');
                            $empleadoA= empleado::where('cod_empleado',$ItemP->cod_empleado_fk)->value('apellido');
                            $proveedorN=proveedor::where('cod_proveedor',$ItemP->cod_proveedor_fk)->value('nombre');
                            $output .='
                            <div class="col-sm-4">
                            <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Special title treatment</h5>
                                <p class="card-text">With supporting text below as a natural lead-in to additional content</p>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">Vendido por: '.$empleadoN.' '.$empleadoA.'</li>
                                    <li class="list-group-item">Proveedor: '.$proveedorN.'</li>                                   
                                    <li class="list-group-item">Total: $'.$ItemP->total.'</li>
                                </ul>
                                <a href="'.$redireccion.'" class="btn btn-primary">Editar</a>
                            </div>
                            </div>
                            </div>
                            
            
                            ';       
                            }
            
                        }else{
                            $output='
                            <tr>
                                <td align="center" colspan="5">Sin Registros</td>
                            </tr>
                            ';
                        }
            
                    }
                    }
                    break;
                    case 2:{
                    $empleados= empleado::where('nombre','LIKE','%'.$query.'%') 
                                        ->orWhere('apellido','LIKE','%'.$query.'%')                                     
                                        ->get();
                    if($empleados->count()>0){
                        foreach($empleados as $emp){
                        $Ventaemp= compra::where('cod_empleado_fk',$emp->cod_empleado)->get();
                        $output='';
                        foreach($Ventaemp as $vemp){
                            $pedidocompra = compra::where('cod_compra',$vemp->cod_compra)
                        ->get();
                        if(isset($pedidocompra)){
                            $total=$empleados->count();
                            if($total>0)
                            {
                                foreach($pedidocompra as $ItemP){
                                $redireccion = route('compras.edit', $ItemP->cod_compra);
                                $empleadoN= empleado::where('cod_empleado',$ItemP->cod_empleado_fk)->value('nombre');
                                $empleadoA= empleado::where('cod_empleado',$ItemP->cod_empleado_fk)->value('apellido');
                                $proveedorN=proveedor::where('cod_proveedor',$ItemP->cod_proveedor_fk)->value('nombre');
                                $output .='
                                <div class="col-sm-4">
                                <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Special title treatment</h5>
                                    <p class="card-text">With supporting text below as a natural lead-in to additional content</p>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">Vendido por: '.$empleadoN.' '.$empleadoA.'</li>
                                        <li class="list-group-item">Proveedor: '.$proveedorN.'</li>                                     
                                        <li class="list-group-item">Total: $'.$ItemP->total.'</li>
                                    </ul>
                                    <a href="'.$redireccion.'" class="btn btn-primary">Editar</a>
                                </div>
                                </div>
                            </div>
                            
            
                                ';
            
                                }
            
                            }       
                            }
                        }                   
                        } 
                        
                    }else{
                        $output='
                        <tr>
                            <td align="center" colspan="5">Sin Registros</td>
                        </tr>
                        ';
                    }                     
                    }
                    break;
                    case 3:{
                    $Proveedors= proveedor::where('nombre','LIKE','%'.$query.'%')                                      
                                        ->get();
                    if($Proveedors->count()>0){
                        foreach($Proveedors as $emp){
                        $Ventaemp= compra::where('cod_proveedor_fk',$emp->cod_proveedor)->get();
                        $output='';
                        foreach($Ventaemp as $vemp){
                            $pedidocompra = compra::where('cod_compra',$vemp->cod_compra)
                        ->get();
                        if(isset($pedidocompra)){
                            $total=$Proveedors->count();
                        
            
                            if($total>0)
                            {
                                foreach($pedidocompra as $ItemP){
                                $redireccion = route('compras.edit', $ItemP->cod_compra);
                                $empleadoN= empleado::where('cod_empleado',$ItemP->cod_empleado_fk)->value('nombre');
                                $empleadoA= empleado::where('cod_empleado',$ItemP->cod_empleado_fk)->value('apellido');
                                $proveedorN=proveedor::where('cod_proveedor',$ItemP->cod_proveedor_fk)->value('nombre');
                                $output .='
                                <div class="col-sm-4">
                                <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Special title treatment</h5>
                                    <p class="card-text">With supporting text below as a natural lead-in to additional content</p>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">Vendido por: '.$empleadoN.' '.$empleadoA.'</li>
                                        <li class="list-group-item">Proveedor: '.$proveedorN.'</li>                                       
                                        <li class="list-group-item">Total: $'.$ItemP->total.'</li>
                                    </ul>
                                    <a href="'.$redireccion.'" class="btn btn-primary">Editar</a>
                                </div>
                                </div>
                            </div>
                            
            
                                ';
            
                                }
            
                            }        
                            }
                        }                    
                        } 
                    }else{
                        $output='
                        <tr>
                            <td align="center" colspan="5">Sin Registros</td>
                        </tr>
                        ';
                    }                                   
                    }
                    break;
                }
                
                
            }else{
                $output='
                <tr>
                    <td align="center" colspan="5">Ingrese el valor correspondiente al filtro aplicado </td>
                </tr>
                ';
            } 

            echo json_encode($output);
        }
    }
}
