# Stromkosten Logger (IP-Symcon Modul)

Dieses Modul für IP-Symcon berechnet stündlich den Stromverbrauch auf Basis zweier Float-Variablen:
- Zählerstand (z. B. in kWh)
- Strompreis pro kWh

## Funktionen

- Automatische stündliche Berechnung von Verbrauch und Kosten
- CSV-Protokollierung inklusive Zeitstempel
- Bereitstellung der CSV-Datei als Download im WebFront

## Einrichtung

1. Modul in IP-Symcon hinzufügen
2. Instanz anlegen und folgende Parameter setzen:
   - Zähler-Variable (Float)
   - Preis-Variable (Float)
   - Optional: Pfad zur CSV-Datei

## CSV-Format

```csv
Zeitstempel;Verbrauch (kWh);kWh-Preis (€);Kosten (€)
2025-05-08 13:00:00;1.23;0.35;0.4305
```