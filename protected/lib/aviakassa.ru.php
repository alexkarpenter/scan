<?php

class ServiceFlightsParamDateTime{
    var $_;//ServiceFlightsParamDateTimeValueStringPattern
}
class ServiceFlightsParamAirport{
    var $_;//ServiceFlightsParamAirportValueStringPattern
    var $CodeType;//string
}
class ServiceFlightsGeneralRequestSearchFlightsODPair{
    var $DepDate;//ServiceFlightsParamDateTime
    var $DepAirp;//ServiceFlightsParamAirport
    var $ArrAirp;//ServiceFlightsParamAirport
}
class ServiceFlightsGeneralRequestSearchFlightsODPairs{
    var $ODPair;//ServiceFlightsGeneralRequestSearchFlightsODPair
    var $Type;//TypeStringPattern
    var $Direct;//boolean
    var $AroundDates;//int
}
class ServiceFlightsParamTravellerSearch{
    var $Type;//TypeStringPattern
    var $Count;//int
}
class ArrayOfTravellersServiceFlightsParamTravellerSearch{
    var $Traveller;//ServiceFlightsParamTravellerSearch
}
class ServiceFlightsGeneralRequestSearchFlightsRestrictionsAirVPref{
    var $Code;//string
    var $Include;//boolean
    var $Type;//string
}
class ArrayOfAirVPrefsServiceFlightsGeneralRequestSearchFlightsRestrictionsAirVPrefIterKeyAirVPref{
    var $AirVPref;//ServiceFlightsGeneralRequestSearchFlightsRestrictionsAirVPref
}
class ServiceFlightsGeneralRequestSearchFlightsRestrictions{
    var $ClassPref;//ClassPrefStringPattern
    var $OnlyAvail;//boolean
    var $AirVPrefs;//ArrayOfAirVPrefsServiceFlightsGeneralRequestSearchFlightsRestrictionsAirVPrefIterKeyAirVPref
    var $IncludePrivateFare;//boolean
    var $CurrencyCode;//CurrencyCodeStringPattern
}
class ServiceFlightsGeneralRequestSearchFlights{
    var $ODPairs;//ServiceFlightsGeneralRequestSearchFlightsODPairs
    var $Travellers;//ArrayOfTravellersServiceFlightsParamTravellerSearch
    var $Restrictions;//ServiceFlightsGeneralRequestSearchFlightsRestrictions
    var $LinkOnly;//boolean
}
class ServiceFlightsGeneralRequestSearch{
    var $SearchFlights;//ServiceFlightsGeneralRequestSearchFlights
}
class ServiceFlightsSource{
    var $ClientId;//ClientIdStringMaxLength
    var $APIKey;//APIKeyStringPattern
    var $Language;//LanguageStringPattern
    var $Currency;//CurrencyStringPattern
}
class ServiceFlightsGeneralRequestSearchBin{
    var $Request;//ServiceFlightsGeneralRequestSearch
    var $Source;//ServiceFlightsSource
}
class ServiceFlightsGeneralResponseSearchFlightsFlightSegmentBookingCode{
    var $_;//string
}
class ArrayOfBookingCodesServiceFlightsGeneralResponseSearchFlightsFlightSegmentBookingCode{
    var $BookingCode;//ServiceFlightsGeneralResponseSearchFlightsFlightSegmentBookingCode
}
class ServiceFlightsGeneralResponseSearchFlightsFlightSegmentTimeZone{
    var $Departure;//int
    var $Arrival;//int
}
class ServiceFlightsGeneralResponseSearchFlightsFlightSegment{
    var $Direction;//string
    var $DepAirp;//ServiceFlightsParamAirport
    var $DepTerminal;//string
    var $ArrAirp;//ServiceFlightsParamAirport
    var $ArrTerminal;//string
    var $OpAirline;//string
    var $MarkAirline;//string
    var $FlightNumber;//string
    var $AircraftType;//string
    var $DepDateTime;//ServiceFlightsParamDateTime
    var $ArrDateTime;//ServiceFlightsParamDateTime
    var $StopNum;//int
    var $BookingCodes;//ArrayOfBookingCodesServiceFlightsGeneralResponseSearchFlightsFlightSegmentBookingCode
    var $FlightTime;//int
    var $TimeZone;//ServiceFlightsGeneralResponseSearchFlightsFlightSegmentTimeZone
    var $ETicket;//boolean
    var $SegNum;//int
}
class ArrayOfSegmentsServiceFlightsGeneralResponseSearchFlightsFlightSegment{
    var $Segment;//ServiceFlightsGeneralResponseSearchFlightsFlightSegment
}
class ServiceFlightsParamFare{
    var $Currency;//CurrencyStringPattern
    var $Amount;//float
}
class ServiceFlightsParamTax{
    var $CurCode;//CurCodeStringPattern
    var $TaxCode;//string
    var $Amount;//float
}
class ArrayOfTaxesServiceFlightsParamTaxIterKeyTax{
    var $Tax;//ServiceFlightsParamTax
}
class ServiceFlightsParamTariff{
    var $Code;//string
    var $SegNum;//int
}
class ArrayOfTariffsServiceFlightsParamTariff{
    var $Tariff;//ServiceFlightsParamTariff
}
class ServiceFlightsGeneralResponseSearchFlightsFlightPricingInfoPassengerFare{
    var $BaseFare;//ServiceFlightsParamFare
    var $EquiveFare;//ServiceFlightsParamFare
    var $TotalFare;//ServiceFlightsParamFare
    var $Taxes;//ArrayOfTaxesServiceFlightsParamTaxIterKeyTax
    var $Tariffs;//ArrayOfTariffsServiceFlightsParamTariff
    var $FareCalc;//string
    var $LastTicketDateTime;//ServiceFlightsParamDateTime
    var $Type;//TypeStringPattern
    var $Quantity;//int
}
class ServiceFlightsGeneralResponseSearchFlightsFlightPricingInfo{
    var $PassengerFare;//ServiceFlightsGeneralResponseSearchFlightsFlightPricingInfoPassengerFare
    var $Refundable;//boolean
}
class ServiceFlightsParamPrice{
    var $_;//float
    var $Currency;//CurrencyStringPattern
}
class ServiceFlightsGeneralResponseSearchFlightsFlight{
    var $WebService;//string
    var $ValCompany;//string
    var $URL;//string
    var $Segments;//ArrayOfSegmentsServiceFlightsGeneralResponseSearchFlightsFlightSegment
    var $PricingInfo;//ServiceFlightsGeneralResponseSearchFlightsFlightPricingInfo
    var $Commission;//ServiceFlightsParamPrice
    var $Charges;//ServiceFlightsParamPrice
    var $TotalPrice;//ServiceFlightsParamPrice
    var $FlightId;//int
    var $SearchId;//int
}
class ServiceFlightsGeneralResponseSearchFlightsResult{
    var $Flight;//ServiceFlightsGeneralResponseSearchFlightsFlight
    var $SearchId;//int
    var $ResultURL;//string
}
class ServiceFlightsGeneralResponseSearchErrorsError{
    var $Code;//string
    var $ServiceErrorMessage;//string
    var $Message;//string
}
class ArrayOfErrorsServiceFlightsGeneralResponseSearchErrorsError{
    var $Error;//ServiceFlightsGeneralResponseSearchErrorsError
}
class ServiceFlightsGeneralResponseSearchFlights{
    var $Flights;//ServiceFlightsGeneralResponseSearchFlightsResult
    var $Errors;//ArrayOfErrorsServiceFlightsGeneralResponseSearchErrorsError
}
class ServiceFlightsGeneralResponseSearch{
    var $SearchFlights;//ServiceFlightsGeneralResponseSearchFlights
}
class ServiceFlightsError{
    var $_;//string
    var $Code;//string
}
class ServiceFlightsGeneralResponseSearchBin{
    var $Response;//ServiceFlightsGeneralResponseSearch
    var $Error;//ServiceFlightsError
}
class av
{
    var $soapClient;

    private static $classmap = array('ServiceFlightsParamDateTime'=>'ServiceFlightsParamDateTime'
    ,'ServiceFlightsParamAirport'=>'ServiceFlightsParamAirport'
    ,'ServiceFlightsGeneralRequestSearchFlightsODPair'=>'ServiceFlightsGeneralRequestSearchFlightsODPair'
    ,'ServiceFlightsGeneralRequestSearchFlightsODPairs'=>'ServiceFlightsGeneralRequestSearchFlightsODPairs'
    ,'ServiceFlightsParamTravellerSearch'=>'ServiceFlightsParamTravellerSearch'
    ,'ArrayOfTravellersServiceFlightsParamTravellerSearch'=>'ArrayOfTravellersServiceFlightsParamTravellerSearch'
    ,'ServiceFlightsGeneralRequestSearchFlightsRestrictionsAirVPref'=>'ServiceFlightsGeneralRequestSearchFlightsRestrictionsAirVPref'
    ,'ArrayOfAirVPrefsServiceFlightsGeneralRequestSearchFlightsRestrictionsAirVPrefIterKeyAirVPref'=>'ArrayOfAirVPrefsServiceFlightsGeneralRequestSearchFlightsRestrictionsAirVPrefIterKeyAirVPref'
    ,'ServiceFlightsGeneralRequestSearchFlightsRestrictions'=>'ServiceFlightsGeneralRequestSearchFlightsRestrictions'
    ,'ServiceFlightsGeneralRequestSearchFlights'=>'ServiceFlightsGeneralRequestSearchFlights'
    ,'ServiceFlightsGeneralRequestSearch'=>'ServiceFlightsGeneralRequestSearch'
    ,'ServiceFlightsSource'=>'ServiceFlightsSource'
    ,'ServiceFlightsGeneralRequestSearchBin'=>'ServiceFlightsGeneralRequestSearchBin'
    ,'ServiceFlightsGeneralResponseSearchFlightsFlightSegmentBookingCode'=>'ServiceFlightsGeneralResponseSearchFlightsFlightSegmentBookingCode'
    ,'ArrayOfBookingCodesServiceFlightsGeneralResponseSearchFlightsFlightSegmentBookingCode'=>'ArrayOfBookingCodesServiceFlightsGeneralResponseSearchFlightsFlightSegmentBookingCode'
    ,'ServiceFlightsGeneralResponseSearchFlightsFlightSegmentTimeZone'=>'ServiceFlightsGeneralResponseSearchFlightsFlightSegmentTimeZone'
    ,'ServiceFlightsGeneralResponseSearchFlightsFlightSegment'=>'ServiceFlightsGeneralResponseSearchFlightsFlightSegment'
    ,'ArrayOfSegmentsServiceFlightsGeneralResponseSearchFlightsFlightSegment'=>'ArrayOfSegmentsServiceFlightsGeneralResponseSearchFlightsFlightSegment'
    ,'ServiceFlightsParamFare'=>'ServiceFlightsParamFare'
    ,'ServiceFlightsParamTax'=>'ServiceFlightsParamTax'
    ,'ArrayOfTaxesServiceFlightsParamTaxIterKeyTax'=>'ArrayOfTaxesServiceFlightsParamTaxIterKeyTax'
    ,'ServiceFlightsParamTariff'=>'ServiceFlightsParamTariff'
    ,'ArrayOfTariffsServiceFlightsParamTariff'=>'ArrayOfTariffsServiceFlightsParamTariff'
    ,'ServiceFlightsGeneralResponseSearchFlightsFlightPricingInfoPassengerFare'=>'ServiceFlightsGeneralResponseSearchFlightsFlightPricingInfoPassengerFare'
    ,'ServiceFlightsGeneralResponseSearchFlightsFlightPricingInfo'=>'ServiceFlightsGeneralResponseSearchFlightsFlightPricingInfo'
    ,'ServiceFlightsParamPrice'=>'ServiceFlightsParamPrice'
    ,'ServiceFlightsGeneralResponseSearchFlightsFlight'=>'ServiceFlightsGeneralResponseSearchFlightsFlight'
    ,'ServiceFlightsGeneralResponseSearchFlightsResult'=>'ServiceFlightsGeneralResponseSearchFlightsResult'
    ,'ServiceFlightsGeneralResponseSearchErrorsError'=>'ServiceFlightsGeneralResponseSearchErrorsError'
    ,'ArrayOfErrorsServiceFlightsGeneralResponseSearchErrorsError'=>'ArrayOfErrorsServiceFlightsGeneralResponseSearchErrorsError'
    ,'ServiceFlightsGeneralResponseSearchFlights'=>'ServiceFlightsGeneralResponseSearchFlights'
    ,'ServiceFlightsGeneralResponseSearch'=>'ServiceFlightsGeneralResponseSearch'
    ,'ServiceFlightsError'=>'ServiceFlightsError'
    ,'ServiceFlightsGeneralResponseSearchBin'=>'ServiceFlightsGeneralResponseSearchBin'

    );

    function __construct($url='http://new.aviakassa.ru/aviakassa.wsdl')
    {
        $this->soapClient = new SoapClient($url,array("classmap"=>self::$classmap,"trace" => true,"exceptions" => true));
    }

    function search($ServiceFlightsGeneralRequestSearchBin)
    {

        $ServiceFlightsGeneralResponseSearchBin = $this->soapClient->search($ServiceFlightsGeneralRequestSearchBin);
        return $ServiceFlightsGeneralResponseSearchBin;

    }
    function dlog($anyType)
    {

        $this->soapClient->dlog($anyType);
    }}



function doreq()
{
    $url = "http://www.aviakassa.ru/serviceflights/?version=1.0&for=SearchFlights";

    $soap_request = '<?xml version="1.0" encoding="UTF-8"?>
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" xmlns:ns1="http://www.aviakassa.ru/serviceflights/?version=1.0&for=SearchFlights">
  <env:Body>
    <ns1:search>
      <RequestBin>
        <Request>
          <SearchFlights>
            <ODPairs Type="OW" Direct="false" AroundDates="0">
              <ODPair>
                <DepDate>2014-08-20T00:00:00</DepDate>
                <DepAirp CodeType="IATA">TAS</DepAirp>
                <ArrAirp CodeType="IATA">MOW</ArrAirp>
              </ODPair>
            </ODPairs>
            <Travellers>
              <Traveller Type="ADT" Count="1"/>
              <Traveller Type="CNN" Count="0"/>
            </Travellers>
            <Restrictions>
              <ClassPref>all</ClassPref>
              <OnlyAvail>false</OnlyAvail>
              <AirVPrefs/>
              <IncludePrivateFare>false</IncludePrivateFare>
              <CurrencyCode>RUB</CurrencyCode>
            </Restrictions>
          </SearchFlights>
        </Request>
        <Source>
          <ClientId>3</ClientId>
          <APIKey>334992BD098BD7B74268D57DFB4E6383</APIKey>
          <Language>RU</Language>
          <Currency>RUB</Currency>
        </Source>
      </RequestBin>
    </ns1:search>
  </env:Body>
</env:Envelope>';

    $soap_request = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:ver="http://www.aviakassa.ru/serviceflights/?version%3D1.0%26for%3DSearchFlights">
   <soapenv:Header/>
   <soapenv:Body>
      <ver:search>
         <RequestBin>
            <Request>
               <SearchFlights LinkOnly="false">
                  <ODPairs Type="OW" Direct="false" AroundDates="0">
                     <ODPair>
                        <DepDate>2014-08-20T00:00:00</DepDate>
                        <DepAirp CodeType="IATA">MOW</DepAirp>
                        <ArrAirp CodeType="IATA">PAR</ArrAirp>
                     </ODPair>
                  </ODPairs>
                  <Travellers>
                     <Traveller Type="ADT" Count="1" />
                  </Travellers>
                  <Restrictions>
                     <ClassPref>economy</ClassPref>
                     <OnlyAvail>true</OnlyAvail>
                     <AirVPrefs></AirVPrefs>
                     <IncludePrivateFare>true</IncludePrivateFare>
                     <CurrencyCode>RUB</CurrencyCode>
                  </Restrictions>
               </SearchFlights>
            </Request>
            <Source>
               <ClientId>3</ClientId>
               <APIKey>334992BD098BD7B74268D57DFB4E6383</APIKey>
               <Language>RU</Language>
               <Currency>RUB</Currency>
            </Source>
         </RequestBin>
      </ver:search>
   </soapenv:Body>
</soapenv:Envelope>';

    $header = array(
        "Content-type: text/xml;charset=\"utf-8\"",
        "Accept: text/xml",
        "Cache-Control: no-cache",
        "Pragma: no-cache",
        "SOAPAction: '".$url."'",
        "Content-length: ".strlen($soap_request),
    );


    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt( $curl, CURLOPT_FAILONERROR, 0 );
    @curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 1 );

    //curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY ) ;
    //curl_setopt($curl, CURLOPT_USERPWD, 'aviabilet:9unuhawR');
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

    curl_setopt($curl, CURLOPT_HEADER, 0 );
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS,  $soap_request );
    curl_setopt($curl, CURLOPT_HTTPHEADER,     $header);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

// perform the request

    $xml_result = curl_exec($curl);
    dump($xml_result);
    die();
// check for errors
    if ($xml_result === false) {
        $error_occurred = true;
    }
    else {

        //$xml = simplexml_load_string($xml_result);

        dump($xml);
    }

}

?>