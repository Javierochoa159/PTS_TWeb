<?php

namespace App\Models;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Venta extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "ventas";
    protected $primaryKey = 'id';
    protected $fillable = [
        'tipo_pago',
        'tipo_venta',
        'monto_subtotal',
        'monto_total',
        'nombre_receptor',
        'telefono_receptor',
        'estado_entrega',
        'venta_invalida'
    ];
    public $timestamps = true;

    public function newVenta($newVenta){
        return ($this->create($newVenta))->id;
    }

    public function editVenta($venta,$idVenta){
        return $this->where("id","=",$idVenta)
                    ->withTrashed()
                    ->update($venta);
    }

    public function deleteVenta($idVenta){
        $venta = $this->find($idVenta);
        if($venta){
            return $venta->delete();
        }
        return false;
    }

    public function recibos() {
        return $this->hasMany(RecibosVenta::class, "venta");
    }
    public function devoluciones() {
        return $this->hasMany(DevolucionVenta::class, "venta");
    }
    public function ubicacion() {
        return $this->hasOne(UbicacionVenta::class, "venta");
    }

    public function productosVenta(){
        return $this->hasMany(ProductosVenta::class, 'venta');
    }

    protected static function booted(){
    static::deleting(function ($venta) {
        $venta->productosVenta()->delete();
        $venta->recibos()->delete();
        $venta->ubicacion()->delete();
        
        $venta->devoluciones()->each(function($devolucion) {
            $devolucion->productos()->delete();
            $devolucion->recibos()->delete();
        });
        
        $venta->devoluciones()->delete();
    });
}

    public function getAllVentasActivas($orden, $pedido = null, $entrega = null, $pago = null, $fInicio = null, $fFin = null, $mostrarCompletamenteDevueltas = false) {
        $baseQuery = $this->with([
                'devoluciones' => function($q) {
                    $q->select(
                        "devoluciones_ventas.id",
                        "devoluciones_ventas.venta",
                        "devoluciones_ventas.monto_total",
                    );
                },
                'productosVenta' => function($q) {
                    $q->select(
                        "productos_ventas.id",
                        "productos_ventas.venta",
                        "productos_ventas.producto",
                        "productos_ventas.cantidad",
                        "productos_ventas.precio_venta"
                    )
                    ->with([
                        'productoRelacion:id,nombre,tipo_medida'
                    ])
                    ->limit(3);
                },
            ])->select(
                "ventas.id",
                "ventas.tipo_pago",
                "ventas.tipo_venta",
                "ventas.monto_total",
                "ventas.estado_entrega",
                "ventas.fecha_venta",
                DB::raw('(SELECT COUNT(*) FROM productos_ventas WHERE productos_ventas.venta = ventas.id) as total_productos'),
                DB::raw('(ventas.monto_total - COALESCE((SELECT SUM(devoluciones_ventas.monto_total) FROM devoluciones_ventas WHERE devoluciones_ventas.venta = ventas.id), 0)) as monto_total_real'),
                
                DB::raw('(
                    SELECT COUNT(*) 
                    FROM productos_ventas pv
                    WHERE pv.venta = ventas.id 
                    AND pv.cantidad > COALESCE((
                        SELECT SUM(pd.cantidad) 
                        FROM productos_devoluciones_ventas pd 
                        INNER JOIN devoluciones_ventas dv ON pd.devolucion = dv.id 
                        WHERE dv.venta = ventas.id 
                        AND pd.producto = pv.producto
                    ), 0)
                ) as total_productos_real')
            );

       
        if(isset($pedido)) $baseQuery = $baseQuery->where("ventas.tipo_venta", "=", $pedido);
        if(isset($entrega)) $baseQuery = $baseQuery->where("ventas.estado_entrega", "=", $entrega);
        if(isset($pago)) $baseQuery = $baseQuery->where("ventas.tipo_pago", "=", $pago);
        if(isset($fInicio)) $baseQuery = $baseQuery->where("ventas.fecha_venta", ">=", $fInicio);
        if(isset($fFin)) $baseQuery = $baseQuery->where("ventas.fecha_venta", "<=", $fFin);

        
        $totalVentas = $baseQuery->count();
        
        
        if (!$mostrarCompletamenteDevueltas) {
            $baseQuery = $baseQuery->having('monto_total_real', '>', 0);
        }

        
        if ($orden["orden"] === "monto_total") {
            $baseQuery = $baseQuery->orderBy('monto_total_real', $orden["direccion"]);
        } else if ($orden["orden"] === "total_productos") {
            $baseQuery = $baseQuery->orderBy('total_productos_real', $orden["direccion"]);
        } else {
            $baseQuery = $baseQuery->orderBy($orden["orden"], $orden["direccion"]);
        }
        
        $ventas = $baseQuery->paginate(15);

        return [
            "totalVenta" => $totalVentas,
            "ventas" => $ventas,
            "mostrandoCompletamenteDevueltas" => $mostrarCompletamenteDevueltas
        ];
    }
    public function gananciasMensuales(){
        $todosLosYears = Venta::selectRaw('YEAR(fecha_venta) as año')
                                ->distinct()
                                ->orderBy('año', 'asc')
                                ->pluck('año');
        
        $ganancias = $this::select(
                DB::raw('YEAR(fecha_venta) as año'),
                DB::raw('MONTH(fecha_venta) as mes'),
                DB::raw('SUM(ventas.monto_total - COALESCE((SELECT SUM(devoluciones_ventas.monto_total) FROM devoluciones_ventas WHERE devoluciones_ventas.venta = ventas.id), 0)) as ganancias')
            )
            ->where('tipo_pago','!=','Pendiente')
            ->where('venta_invalida','=','0')
            ->groupBy('año', 'mes')
            ->orderBy('año', 'asc')
            ->orderBy('mes', 'desc')
            ->withTrashed()
            ->get();

        $yearsVisibles = $todosLosYears->take(-2);

        $datasets=[];
        foreach ($ganancias->groupBy('año') as $year => $data) {
            $datasets[] = [
                'label' => "Año $year",
                'data' => $this->prepararDatosMensuales($data),
                'borderColor' => $this->generarColor($year),
                'backgroundColor' => $this->generarColor($year, 0.1),
                'borderWidth' => 1.5,
            ];
        }

        $datasetsVisibles = array_filter($datasets, function($dataset) use ($yearsVisibles) {
            $year = (int)str_replace('Año ', '', $dataset['label']);
            return $yearsVisibles->contains($year);
        });
        $datasetsVisibles = array_values($datasetsVisibles);

        return [$datasets,$datasetsVisibles];
    }
    private function prepararDatosMensuales($data){
        $datosMensuales = array_fill(1, 12, 0);
        foreach ($data as $venta) {
            $total=BigDecimal::of($venta->ganancias)->toScale(4,RoundingMode::HALF_UP);
            $datosMensuales[$venta->mes] = $total->__toString();
        }
        return array_values($datosMensuales);
    }
    private function generarColor($year, $opacidad = 1){
        $colores = [
            2023 => 'rgb(175, 122, 62)',  
            2024 => 'rgb(75, 122, 62)',  
            2025 => 'rgb(255, 80, 100)',  
            2026 => 'rgb(54, 162, 235)',  
            2027 => 'rgb(255, 195, 96)',
        ];
        $colorBase = $colores[$year] ?? sprintf('rgb(%d, %d, %d)', 
            rand(50, 200), rand(50, 200), rand(50, 200));
        
        if ($opacidad < 1) {
            return str_replace('rgb', 'rgba', $colorBase) . ", $opacidad)";
        }
        return $colorBase;
    }

    public function getTotalesVentas($rango="Hoy", $fInicio = null, $fFin = null, $tipoPago="Todo") {
        $baseQuery = $this->query();
        switch($rango) {
            case "Hoy": 
                $minNow = Carbon::now()->setTime(0,0);
                $baseQuery = $baseQuery->where("ventas.fecha_venta", ">=", $minNow);
            break;
            case "Semana":
                $minNow = Carbon::now()->subDays(7);
                $baseQuery = $baseQuery->where("ventas.fecha_venta", ">=", $minNow);
            break;
            case "Mes":
                $minNow = Carbon::now()->subMonth();
                $baseQuery = $baseQuery->where("ventas.fecha_venta", ">=", $minNow);
            break;
            case "Personal":
                if(isset($fInicio)) $baseQuery = $baseQuery->where("ventas.fecha_venta", ">=", $fInicio);
                if(isset($fFin)) $baseQuery = $baseQuery->where("ventas.fecha_venta", "<=", $fFin);
            break;
            case "Siempre":
                $baseQuery = $baseQuery->where("ventas.fecha_venta", ">=", Carbon::parse(1420081200));
                $baseQuery = $baseQuery->where("ventas.fecha_venta", "<=", now());
            break;
            default:
                $minNow = Carbon::now()->setTime(0,0);
                $baseQuery = $baseQuery->where("ventas.fecha_venta", ">=", $minNow);
        }

        switch($tipoPago) {
            case "Tarjeta": 
                $baseQuery = $baseQuery->where("ventas.tipo_pago", "=", "Tarjeta");
                break;
            case "Efectivo": 
                $baseQuery = $baseQuery->where("ventas.tipo_pago", "=", "Efectivo");
                break;
            case "Mixto": 
                $baseQuery = $baseQuery->where("ventas.tipo_pago", "=", "Mixto");
                break;
            default:$baseQuery->where('ventas.tipo_pago','!=','Pendiente');
        }

        $resultados = $baseQuery->select(
                                    DB::raw('COUNT(*) as total_ventas'),
                                    DB::raw('SUM(ventas.monto_total - COALESCE(
                                                (SELECT SUM(devoluciones_ventas.monto_total) 
                                                    FROM devoluciones_ventas 
                                                    WHERE devoluciones_ventas.venta = ventas.id), 0)) as ganancias_totales_reales')
                                )->first();

        $data = [
            "totalVentas" => $resultados->total_ventas ?? 0,
            "gananciasTotalesReales" => $resultados->ganancias_totales_reales ? BigDecimal::of($resultados->ganancias_totales_reales)->toScale(4,RoundingMode::HALF_UP)->__toString() : 0,
            "rango" => $rango,
            "tipoPago" => $tipoPago,
        ];

        if(isset($fInicio)) {
            $data["fInicio"] = $fInicio;
        }
        if(isset($fFin)) {
            $data["fFin"] = $fFin;
        }

        return $data;
    }

    public function getVenta($idVenta){
        //DB::enableQueryLog();
        return $this::with([
            "ubicacion" => function($q) {
                $q->select(
                    "ubicaciones_ventas.lat",
                    "ubicaciones_ventas.lng",
                    "ubicaciones_ventas.direccion",
                    "ubicaciones_ventas.manzana_piso",
                    "ubicaciones_ventas.casa_depto",
                    "ubicaciones_ventas.descripcion",
                    "ubicaciones_ventas.fecha_entrega_min",
                    "ubicaciones_ventas.fecha_entrega_max",
                    "ubicaciones_ventas.fecha_entrega",
                    "ubicaciones_ventas.venta"
                );
            },
            "recibos" => function($q) {
                $q->select(
                    "recibos_ventas.url_img",
                    "recibos_ventas.url_img_online",
                    "recibos_ventas.venta"
                );
            },
            'productosVenta' => function($q) {
                $q->select(
                    "productos_ventas.id",
                    "productos_ventas.venta",
                    "productos_ventas.producto",
                    "productos_ventas.cantidad",
                    "productos_ventas.precio_venta",
                    "productos_ventas.total_producto"
                )
                ->with([
                    'productoRelacion' => function($q) {
                        $q->select('productos.id','productos.nombre','productos.tipo_medida')
                            ->with(["foto" => function($q){
                                $q->select("fotos.id","fotos.url_img","fotos.url_img_online","fotos.producto");
                            }
                        ]);
                    }
                ]);
            },
            'devoluciones' => function($q){
                $q->select(
                    "devoluciones_ventas.id",
                    "devoluciones_ventas.venta",
                    "devoluciones_ventas.monto_total",
                )
                ->with([
                    "productos" => function($q){
                        $q->select(
                            "productos_devoluciones_ventas.producto",
                            "productos_devoluciones_ventas.devolucion",
                            "productos_devoluciones_ventas.cantidad",
                            "productos_devoluciones_ventas.total_producto",
                        );
                    }
                ]);
            },
        ])->select(
            "ventas.id",
            "ventas.monto_subtotal",
            "ventas.monto_total",
            "ventas.tipo_venta",
            "ventas.tipo_pago",
            "ventas.fecha_venta",
            "nombre_receptor",
            "telefono_receptor",
            "estado_entrega",
            "ventas.updated_at",
            )
        ->where("ventas.id","=",$idVenta)
        ->first();
        //dd(DB::getQueryLog());
    }

    public function getAllPedidosPendientes($offset=0){
        $totalVentas = $this->where("ventas.tipo_venta","=","Envio")
                            ->where("ventas.estado_entrega","!=","Completa")
                            ->count();
        $ventas = $this->where("ventas.tipo_venta","=","Envio")
                        ->where("ventas.estado_entrega","!=","Completa")
                        ->select(
                            "ventas.id",
                            "ventas.tipo_pago",
                            "ventas.tipo_venta",
                            "ventas.estado_entrega",
                            "ventas.fecha_venta",
                        );
        $ventas = $ventas->addSelect([
                         "fecha_entrega_max" => DB::table('ubicaciones_ventas')
                                                  ->whereColumn('ubicaciones_ventas.venta', 'ventas.id')
                                                  ->select("ubicaciones_ventas.fecha_entrega_max")
                         ])
                         ->addSelect([
                         "direccion" => DB::table('ubicaciones_ventas')
                                            ->whereColumn('ubicaciones_ventas.venta', 'ventas.id')
                                            ->select("ubicaciones_ventas.direccion")
                         ]);
        $ventas = $ventas->orderBy("fecha_entrega_max")
                   ->orderBy("ventas.fecha_venta")
                   ->limit(5)
                   ->offset($offset)
                   ->get();

        return [
            "pendientes"=>$ventas,
            "totalPendientes"=>$totalVentas
        ];
    }
    public function getAllRetirosPendientes($offset=0){
        $totalVentas = $this->where("ventas.tipo_venta","=","Local")
                            ->where("ventas.estado_entrega","!=","Completa")
                            ->count();
        $ventas = $this->where("ventas.tipo_venta","=","Local")
                        ->where("ventas.estado_entrega","!=","Completa")
                        ->select(
                            "ventas.id",
                            "ventas.tipo_pago",
                            "ventas.tipo_venta",
                            "ventas.nombre_receptor",
                            "ventas.estado_entrega",
                            "ventas.fecha_venta",
                        )->orderBy("ventas.fecha_venta","asc")
                        ->limit(5)
                        ->offset($offset)
                        ->get();

        return [
            "retiros"=>$ventas,
            "totalRetiros"=>$totalVentas
        ];
    }



}
