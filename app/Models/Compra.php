<?php

namespace App\Models;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Compra extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "compras";
    protected $primaryKey = 'id';
    protected $fillable = [
        'monto_total',
        'tipo_pago',
        'proveedor',
        'fecha_compra',
        'compra_deshecha'
    ];
    public $timestamps = true;

    public function newCompra($compra){
        return ($this->create($compra))->id;
    }

    public function editCompra($compra,$idCompra){
        return $this->where("id","=",$idCompra)->update($compra);
    }

    public function getAllComprasActivas($orden,$proveedor=null,$pago=null,$fInicio=null,$fFin=null){
        $totalCompras = $this->count();
        $compras = $this->with([
                'productosCompra' => function($q) {
                    $q->select(
                        "productos_compras.id",
                        "productos_compras.compra",
                        "productos_compras.producto",
                        "productos_compras.cantidad",
                        "productos_compras.precio_compra"
                    )
                    ->with([
                        'productoRelacion:id,nombre,tipo_medida'
                    ])
                    ->limit(3);
                },
            ])->select(
            "compras.id",
            "compras.monto_total",
            "compras.tipo_pago",
            "compras.proveedor",
            "compras.fecha_compra",
            "proveedores.nombre as proveedor_nombre",
            DB::raw('(SELECT COUNT(*) FROM productos_compras WHERE productos_compras.compra = compras.id) as total_productos')
            )->join("proveedores","proveedores.id","=","compras.proveedor","inner");

        if(isset($proveedor))$compras=$compras->where("compras.proveedor","=",$proveedor);
        if(isset($pago))$compras=$compras->where("compras.tipo_pago","=",$pago);
        if(isset($fInicio))$compras=$compras->where("compras.fecha_compra",">=",$fInicio);
        if(isset($fFin))$compras=$compras->where("compras.fecha_compra","<=",$fFin);
        
        $compras=$compras->orderBy($orden["orden"],$orden["direccion"])->paginate(15);

        return [
            "totalCompra"=>$totalCompras,
            "compras"=>$compras
        ];
    }

    public function gastosMensuales(){
        $todosLosYears = Compra::selectRaw('YEAR(fecha_compra) as año')
                                ->distinct()
                                ->orderBy('año', 'asc')
                                ->pluck('año');
        
        $gastos = $this::select(
                DB::raw('YEAR(fecha_compra) as año'),
                DB::raw('MONTH(fecha_compra) as mes'),
                DB::raw('SUM(monto_total) as gastos')
            )
            ->where('compra_deshecha','=','0')
            ->groupBy('año', 'mes')
            ->orderBy('año', 'asc')
            ->orderBy('mes', 'desc')
            ->withTrashed()
            ->get();

        $yearsVisibles = $todosLosYears->take(-2);

        $datasets=[];
        foreach ($gastos->groupBy('año') as $year => $data) {
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
        foreach ($data as $compra) {
            $total=BigDecimal::of($compra->gastos)->toScale(4,RoundingMode::HALF_UP);
            $datosMensuales[$compra->mes] = $total->__toString();
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


    public function getCompra($idCompra){
        //DB::enableQueryLog();
        return $this::with([
            "recibos" => function($q) {
                $q->select(
                    "recibos_compras.url_img",
                    "recibos_compras.url_img_online",
                    "recibos_compras.compra"
                );
            },
            'productosCompra' => function($q) {
                $q->select(
                    "productos_compras.id",
                    "productos_compras.compra",
                    "productos_compras.producto",
                    "productos_compras.cantidad",
                    "productos_compras.precio_compra",
                    "productos_compras.total_producto"
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
        ])->select(
                    "compras.id",
                    "compras.monto_total",
                    "compras.tipo_pago",
                    "compras.proveedor",
                    "compras.fecha_compra",
                    "compras.created_at",
                    "compras.updated_at",
                    "proveedores.nombre"
                  )
        ->join("proveedores","proveedores.id","=","compras.proveedor","inner")
        ->where("compras.id","=",$idCompra)
        ->first();
        //dd(DB::getQueryLog());
    }

    public function deleteCompra($idCompra){
        $compra = $this->find($idCompra);
        if($compra){
            return $compra->delete();
        }
        return false;
    }

    public function recibos() {
        return $this->hasMany(RecibosCompra::class, "compra");
    }

    public function productosCompra(){
        return $this->hasMany(ProductosCompra::class, 'compra');
    }

    protected static function booted(){
        static::deleting(function ($compra) {
            $compra->productosCompra()->delete();
            $compra->recibos()->delete();
        });
    }

    public function getTotalesCompras($rango="Hoy", $fInicio = null, $fFin = null, $tipoPago="Todo", $proveedor="Todo") {
        $baseQuery = $this->query();
        switch($rango) {
            case "Hoy": 
                $minNow = Carbon::now()->setTime(0,0);
                $baseQuery = $baseQuery->where("compras.fecha_compra", ">=", $minNow);
            break;
            case "Semana":
                $minNow = Carbon::now()->subDays(7);
                $baseQuery = $baseQuery->where("compras.fecha_compra", ">=", $minNow);
            break;
            case "Mes":
                $minNow = Carbon::now()->subMonth();
                $baseQuery = $baseQuery->where("compras.fecha_compra", ">=", $minNow);
            break;
            case "Personal":
                if(isset($fInicio)) $baseQuery = $baseQuery->where("compras.fecha_compra", ">=", $fInicio);
                if(isset($fFin)) $baseQuery = $baseQuery->where("compras.fecha_compra", "<=", $fFin);
            break;
            case "Siempre":
                $baseQuery = $baseQuery->where("compras.fecha_compra", ">=", Carbon::parse(1420081200));
                $baseQuery = $baseQuery->where("compras.fecha_compra", "<=", now());
            break;
            default:
                $minNow = Carbon::now()->setTime(0,0);
                $baseQuery = $baseQuery->where("compras.fecha_compra", ">=", $minNow);
        }

        switch($tipoPago) {
            case "Tarjeta": 
                $baseQuery = $baseQuery->where("compras.tipo_pago", "=", "Tarjeta");
                break;
            case "Efectivo": 
                $baseQuery = $baseQuery->where("compras.tipo_pago", "=", "Efectivo");
                break;
            case "Mixto": 
                $baseQuery = $baseQuery->where("compras.tipo_pago", "=", "Mixto");
            break;
        }
            
        if(in_array($proveedor,session("proveedores"))){
            $baseQuery = $baseQuery->where("compras.proveedor", "=", $proveedor);
        }

        $resultados = $baseQuery->select(
                                    DB::raw('COUNT(*) as total_compras'),
                                    DB::raw('SUM(compras.monto_total) as ganancias_totales_reales')
                                )->first();

        $data = [
            "totalCompras" => $resultados->total_compras ? $resultados->total_compras : 0,
            "gananciasTotalesReales" => $resultados->ganancias_totales_reales ? BigDecimal::of($resultados->ganancias_totales_reales)->toScale(4,RoundingMode::HALF_UP)->__toString() : 0,
            "rango" => $rango,
            "tipoPago" => $tipoPago,
            "proveedor" => $proveedor,
        ];

        if(isset($fInicio)) {
            $data["fInicio"] = $fInicio;
        }
        if(isset($fFin)) {
            $data["fFin"] = $fFin;
        }

        return $data;
    }

}
