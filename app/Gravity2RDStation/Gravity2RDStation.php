<?php

namespace VictorHugoBatista\Gravity2RDStation;

use VictorHugoBatista\Integrations\RDStationAPI;

/**
 * Responsável pelo envio dos dados
 * de um formulário ao RD Station.
 * @package VictorHugoBatista\Gravity2RDStation\Integrations
 */
class Gravity2RDStation
{
    /**
     * Conexão com a API do RD Station.
     * @var \VictorHugoBatista\Integrations\RDStationAPI
     */
    private $rd_station_api;

    /**
     * Variações da palavra 'email', para fins de comparação.
     * @var array
     */
    private static $email_variations = ['e-mail', 'email'];

    /**
     * Valor padrão do email do lead (o envio de leads sem email não é permitido).
     * @var string
     */
    private static $email_default = 'mail@mail.com';

    /**
     * Gravity2RDStation constructor.
     * @param $token_private_rdstation Token privado da API do RD Station.
     * @param $token_rdstation Token público da API do RD Station.
     */
    public function __construct($token_private_rdstation, $token_rdstation)
    {
        $this->rd_station_api =
            new RDStationAPI($token_private_rdstation, $token_rdstation);
    }

    /**
     * Envia lead gerado pelo Gravity Forms ao RD Station.
     * Preferencialmente chamado no hook 'gform-confirmation'.
     * @see https://www.gravityhelp.com/documentation/article/gform_confirmation/
     * @param Form $form Formulário onde o lead foi gerado.
     * @param Entry $entry Lead gerado pelo Gravity Forms.
     * @return bool Resultado da operação de envio do lead.
     */
    public function send_lead($form, $entry)
    {
        // Adiciona valores padão aos dados à ser enviados.
        $email_lead = self::$email_default;
        $form_lead = [];

        // Inicializa o identificador com o título do formulário.
        $form_lead['identificador'] = (array_key_exists('title', $form)) ? $form['title'] : '';

        $form_lead = array_merge(
            $form_lead,
            $this->generate_array_lead($form, $entry)
        );
        $email_variations = array_flip(self::$email_variations);

        // Adiciona a possível ocorrência de email à variável
        // $email_lead, removendo do array $form_lead.
        foreach (array_keys($form_lead) as $form_lead_key) {
            $form_lead_key_lower = strtolower($form_lead_key);
            array_flip(self::$email_variations);
            if (array_key_exists($form_lead_key_lower, $email_variations)) {
                $email_lead = $form_lead[$form_lead_key];
                unset($form_lead[$form_lead_key]);
                break;
            }
        }

        return $this->rd_station_api->sendNewLead($email_lead, $form_lead);
    }

    /**
     * Gera um array associativo com as informações do lead.
     * @param Form $form Formulário onde o lead foi gerado.
     * @param Entry $entry Lead gerado pelo Gravity Forms.
     * @return array Lead estruturado em um array associativo.
     */
    private function generate_array_lead($form, $entry)
    {
        $lead = [];
        foreach ( $form['fields'] as $form_field ) {
            $lead[ $form_field->label ] = $entry[ $form_field->id ];
        }
        return $lead;
    }
}
