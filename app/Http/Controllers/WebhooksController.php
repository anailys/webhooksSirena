<?php

namespace App\Http\Controllers;

//use App\Sirena;
use Illuminate\Http\Request;

use Sirena\Sirena;

class WebhooksController extends Controller
{
    public function __invoke(Request $request)
    {

        $this->sendInternalNote($request);

        return "Ok";
    }

    /**
     * functión para consumo del servicio de vmc-services para buscar si un usuario
     * se encuentra en la base de datos como usuario según un nuemero de telefono
     *
     * @param string $phone
     * @return json
     */
    public function findDataUserPhone(string $phone){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, config('webhooks.vmc'). "api/v2/users/get-data-phone/{$phone}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = json_decode(curl_exec($ch));
        curl_close($ch);
        if($res->success){
            return $res->result;
        }
        return null;


    }

    /**
     * functión para el envío de notas internas según usuario registrado o visitante
     *
     * @param Request $request
     * @return json
     */
    public function sendInternalNote($request)
    {
        $phones = json_encode($request->prospect["phones"]);
        $phone =  rtrim($phones, '"]');

        $phone = substr($phone,5);

        $data = $this->findDataUserPhone($phone);


        $sirena = new Sirena;
        $apiKey = config('webhooks.key_sirena');
        $idProspect = $request->prospect["id"];

        if ($data != null) {
            $contentNota = " *Datos del Usuario*
            CUU: ".$data->nickname."
            TELF: ".$data->phone."
            CORREO: ".$data->mail."
            NOMBRE Y APELLIDOS: ".$data->name."
            FONDOS DISPONIBLES:
            CASH: ".$data->cash."
            SUBASCOIN: ".$data->subascoin."
            CONSIGNACIONES ACTIVAS: ".$data->consignments."
            PROCESOS DE COMPRA ACTIVOS: ".$data->on_process;
        }
        else{
            $contentNota = " *Datos del Visitante*
            TELF: ".$phone;
        }

        $urlsendNote = config('webhooks.sirena')."prospect/{$idProspect}/interactions/?api-key={$apiKey}";

        if ($request->type =="updated") {
            $result = $sirena->createInteractionByProspectId($urlsendNote, $idProspect, $contentNota);
            if ($result) {
                return [
                        "note_send" => true
                    ];
            }
            return [
                "note_send" => false
            ];
        }


    }

}
