# Mensasystem

Das Mensasystem ist ein Schulprojekt, dass ich in einem Team während meiner Ausbildung machen musste.
Es basiert auf dem klassischen LAMP Stack - Linux, Apache, MySQL und PHP.

## Features

* Benutzer System mit Benutzergruppen (Schüler und Caterer)
* Angebot wird vom Caterer angelegt und bereitgestellt
* Schüler können Essen über ihren Account buchen
* Caterer sehen eine Bestellübersicht und können so besser Planen
* Message of the day vom Caterer festlegbar

## Installation

Für die Installation ist ein Webserver mit PHP und MySQL Zugriff notwendig.
Der Webserver Fokus (DocumentRoot bei Apache) muss auf den Ordner frontend gelegt werden.

1. Importieren der Datenbank `mensasystem.sql`
2. Apache2 DocumentRoot auf `frontend` legen
3. `backend/Config.php` Datenbank Login Daten festlegen

## License

Licensed under the MIT License.
