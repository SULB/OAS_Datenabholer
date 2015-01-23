# Installation

Wenn Datenbank genutzt wereden soll: gbv_stat.sql in die DB einspielen und Zugangsdaten zur Datenbank+Tabelle in die passende ini-Datei in _config/_ eintragen.

# Voraussetzungen

* PHP >= 5.3
* php_curl Modul
* php_mysqli Modul für MySQL-Operationen

# Betrieb

## API

Es wird die Version 1 der OAS-API genutzt. URL, Benutzer und Passwort zur Nutzung der API ist bei GBV zu erfragen.

## Konfiguration

Konfigurationsdateien liegen im Ordner config. Der Dateiname der Konfigurationsdatei (ohne die Erweiterung '.ini') ist das erste Argument für den aufruf auf der Konsole.

In der Datei sind Konfigurationsparameter in mehrere Abschnitte unterteilt: *common* wird immer eingelesen. *Wunschabschnitt* mit Ergänzungen (überschreibt die Parameter aus *common*) wird zusätzlich gelesen. Es ist das zweite Argument des Aufrufs und darf '*commom*' heißen.

Die Parameter in der ini-Datei entsprechen den privaten Variablen in der ConfigParser-Klasse (**ohne einleitende Unterstriche**). 

* *dbXxxx* betrifft die Datenbank, 
* *apiXxx* die HTTP-Schnittstelle der API, 
* *target* kann *db* (schreiben in mysql), *stdout* (dierekte Ausgabe) oder *store* (speichern in Datei, Dateiname ist das 7. Argument = *destination*). Ein anderer Target wie Mail etc soll DataHandlerInterface implementieren.
* *callMethod* gibt die Methode des Abrufs an (aktuell nur *curl* in CallMethodCurl.php umgesetzt, denkbar ist aber auch fopen und curl-bin, wget-bin - soll CallMethodInterface implementieren)
* weitere Parameter sind Parameter der API (*format* = json|csv nur json wird verarbeitet; *granularity = day|week|month|year; *content* = counter,counter_abstract,robots,robots_abstract - Mehrfachnennung möglich, Nicht-Buchstabe ist Trenner, *prefix* = OAI-Prefix oder ganze OAI-ID - *%* ist Joker)

## Aufruf

Parameter:

1. Name der ini-Datei
2. Abschnitt in der ini-Datei
3. ID- oder OAI-Prefix, der abgeholt werden soll
4. from-Argument der API (2014-10-13, oder '-5 days')
5. until-Argument der API (2014-10-30)
6. target = wohin mit den Daten
7. destination = aktuell nur in 'save' für den Datei-Pfad verwendet (Tabellenname ist in *dbTable* in der ini-Datei)

### Kurzaufruf:

**`php app.php REPO1`**

Holt die Daten nach den Vorgaben des *commom*-Abschnitts der Datei *config/REPO1.ini* für die letzten drei Tage.

# TODO: Nicht umgesetzt oder Überbleibsel vorheriger Entwicklungen

1. Spalte *country* in der Tabelle, dieses Datum wird nicht geliefert
2. Andere Aufruf-Methoden nach CallMethodInterface
3. Andere Targets (wem _db,stdout und store_ nicht reichen)