<?php

namespace App\Http\Controllers\Api;

use App\Enums\Pagamentos;
use App\Http\Controllers\Controller;
use App\Models\Trabalho;
use App\Models\ViewTrabalhosUsuarios as TrabalhosUsuarios;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class WorkController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/trabalhos",
     *     summary="Get all works",
     *     operationId="getAllWorks",
     *     tags={"Works"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         )
     *     ),
     *     security={}
     * )
     */
    public function getAllWorks()
    {
        try {
            $works = TrabalhosUsuarios::where('ativo', 1)
                ->get()
                ->toJson(JSON_PRETTY_PRINT);

            return response($works, 200);
        } catch (Throwable $e) {
            dd($e);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/trabalhos/{type}",
     *     summary="Get works by type",
     *     operationId="getWorksByType",
     *     tags={"Works"},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=true,
     *         description="Work type",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         )
     *     ),
     *     security={}
     * )
     */
    public function getWorksByType(Request $request)
    {
        try{
            $works = TrabalhosUsuarios::where('ativo',1)
                ->where('trabalho','=',$request->type)
                ->get()
                ->toJson(JSON_PRETTY_PRINT);
            return response($works, 200);
        }catch(Throwable $e){
            dd($e);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/trabalho",
     *     summary="Cria um novo trabalho",
     *     description="Cria um novo trabalho com base nos parâmetros fornecidos",
     *     operationId="postNewWork",
     *     tags={"Trabalho"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="id_usuario", type="integer", description="ID do usuário"),
     *                 @OA\Property(property="id_tipo_trabalho", type="integer", description="ID do tipo de trabalho"),
     *                 @OA\Property(property="valor", type="number", description="Valor do trabalho"),
     *                 @OA\Property(property="data_inicio", type="string", format="date", description="Data de início do trabalho"),
     *                 @OA\Property(property="data_fim", type="string", format="date", description="Data de término do trabalho"),
     *                 @OA\Property(property="pagamento", type="string", description="Método de pagamento"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", description="Dados do novo trabalho"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="errors", type="object", description="Erros de validação"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", description="Mensagem de erro"),
     *         )
     *     ),
     * )
     */
    public function postNewWork(Request $request)
    {
        try {
            $this->validate($request, [
                'id_usuario' => 'required|integer',
                'id_tipo_trabalho' => 'required|integer',
                'valor' => 'required|numeric',
                'data_inicio' => 'date',
                'data_fim' => 'date',
            ]);

            DB::beginTransaction();
            $newWorks = new Trabalho;
            $newWorks->id_usuario = $request->id_usuario;
            $newWorks->id_tipo_trabalho = $request->id_tipo_trabalho;
            $newWorks->valor = $request->valor;
            $newWorks->pagamento = Pagamentos::fromValue($request->pagamento);
            $newWorks->ativo = 1;

            $newWorks->save();

            DB::commit();
            return response()->json(['success' => true, 'data' => $newWorks], 200);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e], 422);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
