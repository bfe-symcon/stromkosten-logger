<?php

class StromkostenLogger extends IPSModule
{
    public function Create()
    {
        parent::Create();

        $this->RegisterPropertyInteger("ZaehlerVariable", 0);
        $this->RegisterPropertyInteger("PreisVariable", 0);
        $this->RegisterPropertyString("CSVPath", IPS_GetKernelDir() . "logs/stromkosten_" . $this->InstanceID . ".csv");

        $this->RegisterTimer("BerechneStromkosten", 0, 'SCL_LogKosten($_IPS["TARGET"]);');

        $this->RegisterAttributeFloat("LetzterZaehlerstand", 0.0);
        $this->RegisterAttributeInteger("CSVMediaID", 0);
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        // Timer jede Stunde
        $this->SetTimerInterval("BerechneStromkosten", 3600000);

        // Medienobjekt erstellen (falls nicht vorhanden)
        $mediaID = $this->ReadAttributeInteger("CSVMediaID");
        if (!IPS_ObjectExists($mediaID)) {
            $mediaID = IPS_CreateMedia(1); // 1 = Datei
            IPS_SetParent($mediaID, $this->InstanceID);
            IPS_SetName($mediaID, "CSV Download");
            IPS_SetMediaFile($mediaID, "stromkosten_" . $this->InstanceID . ".csv", false);
            $this->WriteAttributeInteger("CSVMediaID", $mediaID);
        }

        // CSV-Datei initialisieren
        $this->InitCSV();
    }

    public function LogKosten()
    {
        $zaehlerID = $this->ReadPropertyInteger("ZaehlerVariable");
        $preisID = $this->ReadPropertyInteger("PreisVariable");
        $csvPath = $this->ReadPropertyString("CSVPath");

        if (!@IPS_VariableExists($zaehlerID) || !@IPS_VariableExists($preisID)) {
            IPS_LogMessage("StromkostenLogger", "Ungültige Variablen-ID");
            return;
        }

        $aktuellerZaehlerstand = GetValueFloat($zaehlerID);
        $kWhPreis = GetValueFloat($preisID);
        $letzterZaehlerstand = $this->ReadAttributeFloat("LetzterZaehlerstand");

        $verbrauch = $aktuellerZaehlerstand - $letzterZaehlerstand;
        if ($verbrauch < 0) {
            $verbrauch = 0;
        }

        $kosten = $verbrauch * $kWhPreis;
        $zeitstempel = date("Y-m-d H:i:s");

        $zeile = "$zeitstempel;$verbrauch;$kWhPreis;$kosten\n";

        file_put_contents($csvPath, $zeile, FILE_APPEND);

        $this->WriteAttributeFloat("LetzterZaehlerstand", $aktuellerZaehlerstand);

        // CSV in Medienobjekt laden
        $mediaID = $this->ReadAttributeInteger("CSVMediaID");
        if (IPS_MediaExists($mediaID)) {
            IPS_SetMediaContent($mediaID, base64_encode(file_get_contents($csvPath)));
        }
    }

    private function InitCSV()
    {
        $csvPath = $this->ReadPropertyString("CSVPath");

        if (!file_exists($csvPath)) {
            $header = "Zeitstempel;Verbrauch (kWh);kWh-Preis (€);Kosten (€)\n";
            file_put_contents($csvPath, $header);
        }

        // Inhalt ins Medienobjekt laden
        $mediaID = $this->ReadAttributeInteger("CSVMediaID");
        if (IPS_MediaExists($mediaID)) {
            IPS_SetMediaContent($mediaID, base64_encode(file_get_contents($csvPath)));
        }
    }
}
