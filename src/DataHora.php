<?php


namespace TairoLima\MongodbODM;


use MongoDB\BSON\UTCDateTime;

class DataHora
{
    public static function formataDataBrasil(string $date): string
    {
        return date('d/m/Y', strtotime($date));
    }

    public static function converteData(string $dataFormatoBrasil): string
    {
        return date("Y-m-d", strtotime(str_replace("/", "-", $dataFormatoBrasil)));
    }

    public static function converteDataHora(string $dataFormatoBrasil): string
    {
        return date("Y-m-d H:i:s", strtotime(str_replace("/", "-", $dataFormatoBrasil)));
    }

    public static function gerarDataFuturo(int $quantidaDeDias, string $dataInicio = "Y-m-d"): ?string
    {
        $timestampDia        = 86400; // 1 dia tem 86400 segundos
        $timestampDataInicio = strtotime(date($dataInicio));
        $dataFimTimestamp    = ($timestampDataInicio + ($timestampDia * $quantidaDeDias));
        return date('d/m/Y', $dataFimTimestamp);
    }


    /*** [MongoDB] ***/

    public static function getDataHoraAtualMongoDB(): UTCDateTime
    {
        $timeZone = new \DateTimeZone("America/Sao_Paulo");
        $date     = new \DateTime("now", $timeZone);

        $milisegundos = ($date->getTimestamp() - 7200) * 1000;

        return new UTCDateTime($milisegundos);
    }

    public static function converteDataParaMongoDB(?string $data, bool $usarHoraInformada = false, bool $ajustarHoraPesquisa = false): ?UTCDateTime
    {
        //pega a data [Y-m-d] ou [d/m/Y] e coloca no formato do MongoDB [ISODate]
        if ($data != null)
        {
            if ($usarHoraInformada == true)
            {
                $data = self::converteDataHora($data);
            }else{
                $data = self::converteData($data);
            }

            $timeZone = new \DateTimeZone("America/Sao_Paulo");
            $novaData = new \DateTime($data, $timeZone);

            if ($usarHoraInformada == false)
            {
                $novaData->setTime(5,0,1);

                if ($ajustarHoraPesquisa == true)
                {
                    //Existe um diferença na data do PHP com MongoDB, quando vai pesquisar um periodo por data fixa
                    //Problema é esse valor 10800: Converte Timestamp para milisegundos * 7200 ou 10800 somente para ajustar dataHora
                    $novaData->setTime(7,0,1);
                }
            }

            //Converte Timestamp para milisegundos * 7200 ou 10800 somente para ajustar dataHora
            $milisegundos = (($novaData->getTimestamp() - 10800) * 1000);

            return new UTCDateTime( $milisegundos );
        }

        return null;
    }

    public static function reverteDataHoraMongoDB(?UTCDateTime $data): ?string
    {
        if ($data != null)
        {
            return $data->toDateTime()->format("d/m/Y H:i:s");
        }

        return null;
    }

    public static function reverteDataMongoDB(?UTCDateTime $data): ?string
    {
        if ($data != null)
        {
            return $data->toDateTime()->format("d/m/Y");
        }

        return null;
    }

}