<?php

namespace App\Http\Controllers;

//use App\Sirena;
use Illuminate\Http\Request;

use Sirena\Sirena;

class WebhooksController extends Controller
{

    /**
     * Email de la cuenta zendesk
     *
     * @var [type]
     */
    private $email = 'anailys.rodriguez@gmail.com';
    /**
     * Password del acceso en zendesk
     *
     * @var [type]
     */
    private  $password = 'BS88tFw$Ci3Lbb6';

     /**
     * base64 del {$email}:{$password}
     *
     * @var [type]
     */
    private $basic;

    public function __invoke(Request $request)
    {
        $this->basic = base64_encode("{$this->email}:{$this->password}");  
        return $this->createTicketComment();
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
            dd($res->result);
            return $res->result;
        }
        return null;
    }

    public function createTicketComment(){
        $result = $this->findDataUserPhone('3174723818');
        $result = $this->curl(false, 'https://vmcsubastashelp.zendesk.com/api/v2/tickets.json?include=comment_count');
        $result = json_decode($result);
        return (array) $result;
    }

    public function curl(bool $post, string $service, $fields = null)
    {
        $curl = curl_init();       

        curl_setopt_array($curl, array(
            CURLOPT_URL => $service,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $post ? 'POST' : 'GET',
            CURLOPT_POSTFIELDS => $fields,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Basic '.$this->basic
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }


    /**
     * functión para el envío de notas internas según usuario registrado o visitante
     *
     * @param Request $request
     * @return json
     */
    public function sendInternalNote($request)
    {
        $phones = "3174723818";//json_encode($request->prospect["phones"]);
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
